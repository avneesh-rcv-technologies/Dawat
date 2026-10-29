<?php

namespace Modules\Restaurant\Services;

use Modules\Acl\Models\Admin;
use Modules\KYC\Models\KycSubmission;
use Modules\KYC\Models\KycType;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Models\Restaurant;

class OnboardingKycReportService
{
    public function getReport(Admin $admin): array
    {
        $types = KycType::active()->with('fields','submissions.data')->get();

        $submissions = KycSubmission::where('user_id', $admin->id)
            ->with(['messages', 'files'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('kyc_type_id');

        $documents = $types->map(function ($type) use ($submissions) {
            $typeSubmissions = $submissions->get($type->id, collect());
            $submission = $typeSubmissions->first();

            $messages = $submission?->messages ?? collect();
            $adminMessages = $messages
                ->filter(fn ($message) => $message->sender_type === 'admin')
                ->pluck('message')
                ->all();

            return [
                'kyc_types_fields' => $type->fields->map(function ($field) use ($submission) {
                    $fieldData = $submission?->data->first(fn($d) => $d->field_id == $field->id || $d->field_key == $field->field_key);

                    $files = $submission?->files?->filter(fn($f) => ($f->field_id ?? null) == $field->id)->map(function ($file) {
                        return [
                            'id' => $file->id,
                            'path' => $file->file_path ?? $file->path ?? null,
                            'url' => $file->url ?? ($file->file_path ? asset('storage/' . ltrim($file->file_path, '/')) : null),
                            'uploaded_at' => $file->uploaded_at?->toDateTimeString() ?? $file->created_at?->toDateTimeString() ?? null,
                        ];
                    })->values() ?? [];

                    return [
                        'id' => $field->id,
                        'field_name' => $field->field_name,
                        'field_key' => $field->field_key,
                        'field_type' => $field->field_type,
                        'is_required' => (bool) ($field->is_required ?? false),
                        'options' => $field->options ?? null,
                        'order_index' => $field->order_index ?? null,
                        'validation_rules' => $field->validation_rules ?? null,
                        'submission_data' => $fieldData ? [
                            'id' => $fieldData->id,
                            'field_id' => $fieldData->field_id,
                            'field_key' => $fieldData->field_key,
                            'field_value' => $fieldData->field_value,
                            'verified' => (bool) ($fieldData->verified ?? false),
                            'verified_at' => $fieldData->verified_at?->toDateTimeString(),
                            'notes' => $fieldData->notes ?? null,
                        ] : null,
                        'files' => $files,
                    ];
                })->values(),
                'submission_count' => $typeSubmissions->count(),
                'status' => $this->formatKycStatusLabel($submission?->status),
                'rejected_reason' => $submission?->rejection_reason ?? null,
                'submitted_at' => $submission?->created_at?->toDateTimeString(),
                'reviewed_at' => $submission?->reviewed_at?->toDateTimeString(),
                'admin_message' => $adminMessages,
            ];
        })->values();

        return [
            'documents' => $documents,
            'final_status' => $this->calculateFinalOnboardingStatus($submissions, $admin),
        ];
    }

    protected function formatKycStatusLabel(?string $status): ?string
    {
        if ($status === null || $status === '') {
            return null;
        }

        return match ($status) {
            'draft' => 'Draft',
            'pending' => 'Pending',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'rejected' => 'Rejected',
            'approved' => 'Approved',
            'action_required' => 'Action Required',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    protected function calculateFinalOnboardingStatus($submissions, Admin $admin): string
    {
        $restaurant = Restaurant::where('admin_id', $admin->id)->first();

        if ($restaurant && $restaurant->onboarding_status) {
            $status = is_object($restaurant->onboarding_status)
                ? ($restaurant->onboarding_status->value ?? (string) $restaurant->onboarding_status)
                : (string) $restaurant->onboarding_status;

            return $this->formatKycStatusLabel($status) ?? $status;
        }

    $statuses = collect($submissions)
        ->flatten(1)
        ->pluck('status')
        ->filter()
        ->values();

    if ($statuses->isEmpty()) {
        return OnboardingStatus::DRAFT->label();
    }

    if ($statuses->contains('rejected')) {
        return OnboardingStatus::ACTION_REQUIRED->label();
    }

    if ($statuses->contains('under_review')) {
        return OnboardingStatus::UNDER_REVIEW->label();
    }

    if ($statuses->contains('pending')) {
        return OnboardingStatus::SUBMITTED->label();
    }

    if ($statuses->every(fn ($status) => $status === 'approved')) {
        return OnboardingStatus::APPROVED->label();
    }

    return OnboardingStatus::SUBMITTED->label();
}
}
