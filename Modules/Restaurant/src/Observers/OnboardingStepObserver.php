<?php

namespace Modules\Restaurant\Observers;

use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\RestaurantOnboardingStep;

class OnboardingStepObserver
{
    public function saved(RestaurantOnboardingStep $step): void
    {
        $restaurant = $step->restaurant;
        if (!$restaurant) {
            return;
        }

        $requiredSteps = OnboardingStep::required();
        $requiredKeys = array_values(array_map(fn (OnboardingStep $key) => $key->value, $requiredSteps));
        $statuses = $restaurant->onboardingSteps()->pluck('status', 'step_key');
        $allRequiredApproved = collect($requiredKeys)->every(
            fn (string $key) => $statuses->get($key) === OnboardingStatus::APPROVED->value
        );

        $status = match (true) {
            $statuses->contains(OnboardingStatus::REJECTED->value),
            $statuses->contains(OnboardingStatus::ACTION_REQUIRED->value) => OnboardingStatus::ACTION_REQUIRED,
            $statuses->contains(OnboardingStatus::SUBMITTED->value),
            $statuses->contains('pending'),
            $statuses->contains(OnboardingStatus::UNDER_REVIEW->value) => OnboardingStatus::UNDER_REVIEW,
            $allRequiredApproved => OnboardingStatus::APPROVED,
            default => OnboardingStatus::DRAFT,
        };

        if ($status === OnboardingStatus::APPROVED) {
            $firstApproval = $restaurant->onboarding_status !== OnboardingStatus::APPROVED;
            if ($firstApproval || $restaurant->status !== 'active') {
                $restaurant->update([
                    'onboarding_status' => $status->value,
                    'status' => 'active',
                ]);
            }
            if ($firstApproval) {
                $approvedAt = now()->format('Y-m-d H:i:s');
                DB::afterCommit(function () use ($restaurant, $approvedAt) {
                    app(\Modules\Restaurant\Services\FinalOnboardingApprovedService::class)->send($restaurant, $approvedAt);
                });
            }
        } elseif ($restaurant->onboarding_status !== $status) {
            $restaurant->update(['onboarding_status' => $status->value]);
        }
    }
}
