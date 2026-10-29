<?php

namespace Modules\Restaurant\Services;

use Modules\KYC\Models\KycSubmission;
use Modules\KYC\Models\KycType;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;

class KycOnboardingStepService
{
    public function syncAfterReview(KycSubmission $submission, int $reviewerId, bool $resubmission = false): void
    {
        if ($submission->kycType?->type !== 'kyc') return;

        $requiredIds = KycType::active()->kyc()->pluck('id');
        if ($requiredIds->isEmpty()) return;

        $submissions = KycSubmission::where('user_id', $submission->user_id)
            ->whereIn('kyc_type_id', $requiredIds)->with(['kycType', 'messages'])
            ->latest('id')->get()->unique('kyc_type_id');
        $rejected = $submissions->where('status', 'rejected');

        $status = match (true) {
            $rejected->contains(fn ($document) => $document->refill_requested ?? false) => OnboardingStatus::ACTION_REQUIRED,
            $rejected->isNotEmpty() => OnboardingStatus::REJECTED,
            $submissions->whereIn('status', ['pending', 'under_review'])->isNotEmpty() => $resubmission ? OnboardingStatus::SUBMITTED : OnboardingStatus::UNDER_REVIEW,
            $submissions->where('status', 'approved')->count() === $requiredIds->count() => OnboardingStatus::APPROVED,
            default => OnboardingStatus::SUBMITTED,
        };
        $reason = $rejected->map(fn ($document) => ($document->kycType?->name ?? 'KYC document')
            . ': ' . ($document->rejection_reason ?: 'Rejected'))->implode("\n");
        $reviewedAt = now();

        foreach (Restaurant::where('admin_id', $submission->user_id)->lockForUpdate()->get() as $restaurant) {
            $step = $restaurant->onboardingSteps()->firstOrNew(['step_key' => OnboardingStep::KYC->value]);
            $step->fill([
                'status' => $status->value,
                'submitted_at' => $resubmission ? $reviewedAt : ($step->submitted_at ?? $submission->created_at ?? $reviewedAt),
                'reviewed_by' => $resubmission ? null : $reviewerId,
                'reviewed_at' => $resubmission ? null : $reviewedAt,
                'rejection_reason' => $reason !== '' ? $reason : null,
            ]);
            $step->save();
        }
    }
}
