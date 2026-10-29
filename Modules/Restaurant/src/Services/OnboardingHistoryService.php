<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\Auth;
use Modules\KYC\Models\KycSubmission;
use Modules\Restaurant\Models\OnboardingStatusHistory;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Models\RestaurantOnboardingStep;

class OnboardingHistoryService
{
    public function __construct(private OnboardingStatusHistory $histories) {}

    public function record(int $restaurantId, ?string $step, string $action, ?string $oldStatus, string $newStatus, ?int $actor = null, ?string $reason = null): void
    {
        $this->histories->newQuery()->create([
            'restaurant_id' => $restaurantId,
            'step_key' => $step,
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $this->actorId() ?? $actor,
            'reason' => $reason,
        ]);

        // History records what happened. The step observer derives the current
        // onboarding status from review results, including resubmissions.
    }

    public function stepSaved(RestaurantOnboardingStep $step, bool $created): void
    {
        if (!$created && !$step->wasChanged(['status', 'submitted_at', 'reviewed_by', 'reviewed_at', 'rejection_reason'])) {
            return;
        }

        $oldStatus = $created ? null : $step->getRawOriginal('status');
        $action = match (true) {
            $created && $step->status === 'draft' => 'initialized',
            $step->status === 'submitted' && in_array($oldStatus, ['rejected', 'action_required'], true) => 'resubmitted',
            $step->status === 'submitted'
                && in_array($oldStatus, ['submitted', 'pending', 'under_review', 'approved'], true) => 'resubmitted',
            $step->status === 'submitted' => 'submitted',
            $step->status === 'under_review' && $oldStatus !== 'under_review' => 'review_started',
            $step->status === 'approved' => 'approved',
            $step->status === 'rejected' => 'rejected',
            $step->status === 'action_required' => 'changes_requested',
            default => $created ? 'initialized' : 'updated',
        };
        $actor = $step->wasChanged('reviewed_by') && $step->reviewed_by
            ? $step->reviewed_by : $step->restaurant?->admin_id;

        $this->record($step->restaurant_id, $step->step_key, $action, $oldStatus, $step->status, $actor, $step->rejection_reason);
    }

    public function detailsUpdated(Restaurant $restaurant, string $step, string $action = 'details_updated'): void
    {
        $status = $restaurant->onboardingSteps()->where('step_key', $step)->value('status') ?? 'draft';
        $this->record($restaurant->id, $step, $action, $status, $status, $restaurant->admin_id);
    }

    public function kycSaved(KycSubmission $submission, bool $created): void
    {
        if (!$created && !$submission->wasChanged(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason'])) {
            return;
        }
        $oldStatus = $created ? null : $submission->getRawOriginal('status');
        $action = $created ? 'submitted' : match ($submission->status) {
            'pending' => $oldStatus === 'rejected' ? 'resubmitted' : 'updated',
            'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'updated',
        };
        $documentName = trim((string) $submission->kycType?->name);
        $reason = trim('KYC document: ' . ($documentName !== '' ? $documentName : 'Unnamed document') . '. '
            . ($submission->rejection_reason ?? ''));
        foreach (Restaurant::where('admin_id', $submission->user_id)->get() as $restaurant) {
            $this->record($restaurant->id, 'kyc', $action, $oldStatus, $submission->status,
                $submission->reviewed_by ?? $submission->user_id,
                $reason);
        }
    }

    private function actorId(): ?int
    {
        foreach ([null, 'admin'] as $guard) {
            try {
                if ($id = Auth::guard($guard)->id()) {
                    return (int) $id;
                }
            } catch (\Throwable) {
                // A console action may not have an authenticated guard.
            }
        }

        return null;
    }
}
