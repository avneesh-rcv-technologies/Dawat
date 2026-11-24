<?php

namespace Modules\DietaryManagment\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\Acl\Models\Admin;
use Modules\DietaryManagment\Models\CertificationType;
use Modules\DietaryManagment\Models\DietaryLabel;
use Modules\DietaryManagment\Models\RestaurantCertification;
use Modules\DietaryManagment\Services\OnboardingCertificateService;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;

class CertificateController
{
    public function __construct(private OnboardingCertificateService $service) {}

    public function labels(Request $request)
    {
        $this->restaurant($request);
        $filters = $request->validate(['is_certification_required' => ['sometimes', 'boolean']]);
        $labels = DietaryLabel::active()->with('certificationType')->orderBy('display_order')->orderBy('name');
        if (array_key_exists('is_certification_required', $filters)) {
            $labels->where('is_certification_required', $request->boolean('is_certification_required'));
        }
        return $this->response('Dietary labels retrieved successfully.', $labels->get()->map(fn ($label) => [
            'id' => $label->id, 'name' => $label->name, 'code' => $label->code, 'slug' => $label->slug,
            'description' => $label->description, 'icon' => $label->icon,
            'is_certification_required' => $label->is_certification_required,
            'certification_type_id' => $label->certificationType?->id,
            'certification_type' => $label->certificationType ? [
                'id' => $label->certificationType->id, 'name' => $label->certificationType->name,
                'is_active' => $label->certificationType->is_active,
            ] : null,
        ]));
    }

    public function index(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $certificates = $restaurant->certifications()->with('reviewer')->get()->keyBy('certification_type_id');
        $types = CertificationType::with('dietaryLabel')->where(function ($query) use ($restaurant) {
            $query->where(fn ($query) => $query->where('is_active', true)
                ->whereHas('dietaryLabel', fn ($query) => $query->active()->where('is_certification_required', true)))
                ->orWhereHas('restaurantCertifications', fn ($query) => $query->where('restaurant_id', $restaurant->id));
        })->orderBy('name')->get();

        return $this->response('Dietary certificates retrieved successfully.',
            $types->map(fn ($type) => $this->certificateData($type, $certificates->get($type->id))), $restaurant);
    }

    public function uploaded(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $certificates = $restaurant->certifications()
            ->with(['certificationType.dietaryLabel', 'reviewer'])
            ->whereHas('certificationType')
            ->whereNotNull('file_path')->where('file_path', '<>', '')
            ->orderByDesc('submitted_at')->orderByDesc('id')
            ->get();

        return $this->response('Uploaded dietary certificates retrieved successfully.',
            $certificates->map(fn ($certificate) => $this->certificateData($certificate->certificationType, $certificate)),
            $restaurant);
    }

    public function show(Request $request, int $typeId)
    {
        $restaurant = $this->restaurant($request);
        $type = $this->typeForRestaurant($typeId, $restaurant);
        $certificate = $restaurant->certifications()->with('reviewer')->where('certification_type_id', $typeId)->first();
        return $this->response('Dietary certificate retrieved successfully.', $this->certificateData($type, $certificate), $restaurant);
    }

    public function store(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $data = $request->validate($this->uploadRules() + [
            'certification_type_id' => ['required', 'integer', Rule::exists('certification_types', 'id')->where('is_active', true)],
        ], $this->uploadMessages());
        return $this->save($request, $restaurant, (int) $data['certification_type_id'], true);
    }

    public function update(Request $request, int $typeId)
    {
        $restaurant = $this->restaurant($request);
        $request->validate($this->uploadRules() + ['certification_type_id' => ['prohibited']], $this->uploadMessages());
        return $this->save($request, $restaurant, $typeId, false);
    }

    public function document(Request $request, int $typeId)
    {
        $restaurant = $this->restaurant($request);
        $certificate = $restaurant->certifications()->where('certification_type_id', $typeId)->firstOrFail();
        $disk = Storage::disk('local');
        abort_unless($certificate->file_path && $disk->exists($certificate->file_path), 404);
        $headers = ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'];
        if ($request->boolean('preview')) {
            $mime = $disk->mimeType($certificate->file_path);
            abort_unless(in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true), 415);
            return $disk->response($certificate->file_path, $certificate->file_name ?: 'certificate', $headers + [
                'Content-Type' => $mime, 'Content-Security-Policy' => "sandbox; default-src 'none'",
            ], 'inline');
        }
        return $disk->download($certificate->file_path, $certificate->file_name ?: 'certificate', $headers);
    }

    private function save(Request $request, Restaurant $restaurant, int $typeId, bool $creating)
    {
        $type = CertificationType::with('dietaryLabel')->where('is_active', true)
            ->whereHas('dietaryLabel', fn ($query) => $query->active()->where('is_certification_required', true))->findOrFail($typeId);
        $certificate = $this->service->upload($request->user(), $restaurant->id, (string) $type->dietary_label_id, $request->file('document'), $creating);
        return $this->response($creating ? 'Certificate submitted for review.' : 'Certificate updated and resubmitted for review.',
            $this->certificateData($type, $certificate), $restaurant->fresh(), $creating ? 201 : 200);
    }

    private function uploadRules(): array
    {
        return [
            'document' => ['bail', 'required', 'file', 'max:2048', 'mimes:pdf,jpg,jpeg,png,webp', 'extensions:pdf,jpg,jpeg,png,webp'],
            'restaurant_id' => ['prohibited'], 'admin_id' => ['prohibited'], 'status' => ['prohibited'],
            'admin_notes' => ['prohibited'], 'reviewed_by' => ['prohibited'], 'reviewed_at' => ['prohibited'],
            'file_path' => ['prohibited'], 'submitted_at' => ['prohibited'],
        ];
    }

    private function uploadMessages(): array
    {
        return [
            'document.required' => 'Please upload a certificate document.',
            'document.file' => 'The certificate document must be a valid file.',
            'document.uploaded' => 'The certificate document could not be uploaded. Please upload a PDF, PNG, JPG, JPEG, or WebP file no larger than 2 MB.',
            'document.max' => 'The certificate document must not exceed 2 MB.',
            'document.mimes' => 'The certificate document must be a PDF, PNG, JPG, JPEG, or WebP file.',
            'document.extensions' => 'The certificate document must have a .pdf, .png, .jpg, .jpeg, or .webp extension.',
        ];
    }

    private function restaurant(Request $request): Restaurant
    {
        $owner = $request->user();
        abort_unless($owner instanceof Admin && $owner->status && $owner->email_verified_at, 403);
        return Restaurant::where('admin_id', $owner->id)->whereIn('status', ['draft', 'active'])->orderBy('id')->firstOrFail();
    }

    private function typeForRestaurant(int $id, Restaurant $restaurant): CertificationType
    {
        return CertificationType::with('dietaryLabel')->where(function ($query) use ($restaurant) {
            $query->where(fn ($query) => $query->where('is_active', true)
                ->whereHas('dietaryLabel', fn ($query) => $query->active()->where('is_certification_required', true)))
                ->orWhereHas('restaurantCertifications', fn ($query) => $query->where('restaurant_id', $restaurant->id));
        })->findOrFail($id);
    }

    private function certificateData(CertificationType $type, ?RestaurantCertification $certificate): array
    {
        $eligible = $type->is_active && $type->dietaryLabel?->is_active && $type->dietaryLabel?->is_certification_required;
        $status = $certificate?->status ?? 'not_submitted';
        $editable = $eligible && !in_array($status, ['approved', 'under_review'], true);
        return [
            'id' => $certificate?->id, 'certification_type_id' => $type->id, 'certification_name' => $type->name,
            'dietary_label' => $type->dietaryLabel ? [
                'id' => $type->dietaryLabel->id, 'name' => $type->dietaryLabel->name, 'code' => $type->dietaryLabel->code,
                'icon' => $type->dietaryLabel->icon, 'description' => $type->dietaryLabel->description,
            ] : null,
            'status' => $status, 'status_label' => OnboardingCertificateService::STATUS_LABELS[$status] ?? $status,
            'can_submit' => (bool) $editable && !$certificate, 'can_update' => (bool) $editable && $certificate !== null,
            'admin_notes' => $certificate?->admin_notes,
            'submitted_at' => $certificate?->submitted_at?->toIso8601String(),
            'reviewed_at' => $certificate?->reviewed_at?->toIso8601String(),
            'reviewer' => $certificate?->reviewer ? ['id' => $certificate->reviewer->id, 'name' => $certificate->reviewer->name] : null,
            'document' => $certificate?->file_path ? [
                'file_name' => $certificate->file_name,
                'download_url' => route('api.dietarymanagment.certificates.document', $type->id),
                'preview_url' => route('api.dietarymanagment.certificates.document', ['typeId' => $type->id, 'preview' => 1]),
            ] : null,
        ];
    }

    private function response(string $message, $data, ?Restaurant $restaurant = null, int $status = 200)
    {
        $response = ['success' => true, 'message' => $message, 'data' => $data];
        if ($restaurant) {
            $stepStatus = $restaurant->dietaryCertificateStatus();
            $awaitingApproval = $stepStatus !== null && $stepStatus !== 'approved';
            $response['onboarding'] = [
                'restaurant_id' => $restaurant->id, 'step_key' => OnboardingStep::DIETARY_CERTIFICATES->value,
                'step_status' => $stepStatus ?? $restaurant->onboardingSteps()->where('step_key', OnboardingStep::DIETARY_CERTIFICATES->value)->value('status') ?? 'not_started',
                'has_uploaded_documents' => $stepStatus !== null, 'can_skip' => $stepStatus === null,
                'awaiting_approval' => $awaitingApproval,
                'can_continue' => !$awaitingApproval,
            ];
        }
        return response()->json($response, $status)->header('Cache-Control', 'private, no-store');
    }
}
