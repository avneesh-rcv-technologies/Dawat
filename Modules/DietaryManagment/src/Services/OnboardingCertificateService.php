<?php

namespace Modules\DietaryManagment\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\DietaryManagment\Models\DietaryLabel;
use Modules\DietaryManagment\Models\RestaurantCertification;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;

class OnboardingCertificateService
{
    public const STATUS_LABELS = [
        'not_submitted' => 'Not submitted', 'submitted' => 'Submitted', 'under_review' => 'Under review',
        'approved' => 'Approved', 'rejected' => 'Rejected', 'changes_requested' => 'Changes requested',
    ];

    public function screen(Restaurant $restaurant): array
    {
        return [
            'hasUploadedCertificates' => $restaurant->dietaryCertificateStatus() !== null,
            'awaitingCertificateApproval' => $restaurant->hasPendingDietaryCertificates(),
            'certificateLabels' => DietaryLabel::active()->where('is_certification_required', true)
                ->with('certificationType')->orderBy('display_order')->orderBy('name')->get(),
            'certificates' => $restaurant->certifications()->with('reviewer')->get()->keyBy('certification_type_id'),
        ];
    }

    public static function canReview(Admin $admin): bool
    {
        return (bool) $admin->status && $admin->hasPermission('admin.dietarymanagment.certifications.review')
            && $admin->hasPermission('admin.dietarymanagment.certifications.show')
            && !app(CertificationAccess::class)->isOwner($admin);
    }

    public function upload(Admin $owner, int $restaurantId, string $labelId, UploadedFile $document, ?bool $creating = null): RestaurantCertification
    {
        abort_unless($owner->status && $owner->email_verified_at, 403);
        $path = null;
        try {
            return DB::transaction(function () use ($owner, $restaurantId, $labelId, $document, $creating, &$path) {
                $restaurant = Restaurant::where('admin_id', $owner->id)->whereIn('status', ['draft', 'active'])
                    ->lockForUpdate()->findOrFail($restaurantId);
                $label = DietaryLabel::active()->where('is_certification_required', true)->findOrFail($labelId);
                $type = app(CertificationTypeSynchronizer::class)->sync($label);
                $certificate = $restaurant->certifications()->where('certification_type_id', $type->id)->lockForUpdate()->first();
                if ($creating === true && $certificate) {
                    throw ValidationException::withMessages(['certification_type_id' => 'A certificate has already been submitted for this type.']);
                }
                if ($creating === false && !$certificate) {
                    throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())->setModel(RestaurantCertification::class);
                }
                if ($certificate && in_array($certificate->status, ['approved', 'under_review'], true)) {
                    throw ValidationException::withMessages(['document' => 'This certificate is read only while under review or approved.']);
                }
                $oldPath = $certificate?->file_path;
                $resubmitted = !empty($oldPath);
                $path = $document->store('restaurant-certifications/'.$restaurant->id, 'local');
                if (!$path) throw new \RuntimeException('Unable to store the certificate. Please try again.');
                $certificate ??= $restaurant->certifications()->make(['certification_type_id' => $type->id]);
                $certificate->fill([
                    'file_path' => $path, 'file_name' => mb_substr(basename($document->getClientOriginalName()), 0, 255),
                    'status' => 'submitted', 'submitted_at' => now(), 'reviewed_at' => null, 'reviewed_by' => null, 'admin_notes' => null,
                ])->save();
                $this->syncStep($restaurant);
                if ($restaurant->onboarding_status !== \Modules\Restaurant\Enums\OnboardingStatus::APPROVED) {
                    $restaurant->update(['onboarding_step' => OnboardingStep::DIETARY_CERTIFICATES->order()]);
                }
                if ($oldPath) DB::afterCommit(function () use ($oldPath, $certificate) {
                    try { Storage::disk('local')->delete($oldPath); }
                    catch (\Throwable $e) { Log::warning('Could not remove replaced certificate.', ['certificate_id' => $certificate->id]); }
                });
                app(DietaryCertificateNotificationService::class)->schedule($certificate, $resubmitted ? 'resubmitted' : 'submitted');
                return $certificate;
            });
        } catch (\Throwable $e) {
            if ($path) Storage::disk('local')->delete($path);
            throw $e;
        }
    }

    public function skip(Admin $owner, int $restaurantId): void
    {
        abort_unless($owner->status && $owner->email_verified_at, 403);
        DB::transaction(function () use ($owner, $restaurantId) {
            $restaurant = Restaurant::where('admin_id', $owner->id)->whereIn('status', ['draft', 'active'])
                ->lockForUpdate()->findOrFail($restaurantId);
            if ($restaurant->hasPendingDietaryCertificates()) {
                throw ValidationException::withMessages(['certificates' => 'You have uploaded dietary certificates. Correct any rejected or refill-requested documents and wait until all uploaded certificates are approved before continuing.']);
            }
            if ($restaurant->dietaryCertificateStatus() === null) {
                $restaurant->onboardingSteps()->updateOrCreate(['step_key' => OnboardingStep::DIETARY_CERTIFICATES->value], ['status' => 'skipped']);
            }
            $restaurant->update(['onboarding_step' => OnboardingStep::KYC->order()]);
        });
    }

    public function review(Admin $reviewer, int $id, string $status, ?string $notes, string $version): RestaurantCertification
    {
        abort_unless(self::canReview($reviewer), 403);
        return DB::transaction(function () use ($reviewer, $id, $status, $notes, $version) {
            $restaurantId = RestaurantCertification::findOrFail($id)->restaurant_id;
            $restaurant = Restaurant::lockForUpdate()->findOrFail($restaurantId);
            $certificate = RestaurantCertification::lockForUpdate()->findOrFail($id);
            if (!hash_equals($certificate->documentVersion(), $version)) {
                throw ValidationException::withMessages(['status' => 'The certificate changed. Refresh the page before reviewing it.']);
            }
            if (!in_array($status, ['under_review', 'approved', 'rejected', 'changes_requested'], true)
                || !in_array($certificate->status, ['submitted', 'under_review'], true) || $status === $certificate->status) {
                throw ValidationException::withMessages(['status' => 'This review action is not available for the current status.']);
            }
            if (in_array($status, ['rejected', 'changes_requested'], true) && trim($notes ?? '') === '') {
                throw ValidationException::withMessages(['admin_notes' => 'Explain what the owner needs to correct.']);
            }
            if ($status === 'approved' && (!$certificate->file_path || !Storage::disk('local')->exists($certificate->file_path))) {
                throw ValidationException::withMessages(['status' => 'The document is missing. Request a new upload before approving.']);
            }
            $certificate->update(['status' => $status, 'admin_notes' => $notes, 'reviewed_by' => $reviewer->id, 'reviewed_at' => now()]);
            $this->syncStep($restaurant);
            app(DietaryCertificateNotificationService::class)->schedule($certificate, $status);
            return $certificate;
        });
    }

    private function syncStep(Restaurant $restaurant): void
    {
        if ($restaurant->onboarding_status === \Modules\Restaurant\Enums\OnboardingStatus::APPROVED) return;
        $documents = $restaurant->certifications()->with('certificationType')->whereNotNull('file_path')->where('file_path', '!=', '')->get();
        $status = $restaurant->dietaryCertificateStatus() ?? 'draft';
        $latest = $documents->whereNotNull('reviewed_at')->sortByDesc('reviewed_at')->first();
        $reviewed = in_array($status, ['approved', 'rejected', 'action_required'], true);
        $notes = $documents->whereIn('status', ['rejected', 'changes_requested'])->map(
            fn ($document) => $document->certificationType->name.': '.$document->admin_notes
        )->implode("\n");
        $restaurant->onboardingSteps()->updateOrCreate(['step_key' => OnboardingStep::DIETARY_CERTIFICATES->value], [
            'status' => $status, 'submitted_at' => $documents->max('submitted_at'),
            'reviewed_by' => $reviewed ? $latest?->reviewed_by : null, 'reviewed_at' => $reviewed ? $latest?->reviewed_at : null,
            'rejection_reason' => $notes ?: null,
        ]);
    }
}
