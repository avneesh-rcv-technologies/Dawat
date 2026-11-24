<?php

namespace Modules\Restaurant\Observers;

use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\RestaurantOnboardingStep;
use Modules\Restaurant\Services\RestaurantOnboardingCompletionService;

class OnboardingStepObserver
{
    public function saved(RestaurantOnboardingStep $step): void
    {
        // A step may carry a cached parent from before another review updated it.
        $restaurant = $step->restaurant()->first();
        if (!$restaurant) {
            return;
        }

        $requiredSteps = OnboardingStep::required();
        $requiredKeys = array_values(array_map(fn (OnboardingStep $key) => $key->value, $requiredSteps));
        $certificateStatus = $restaurant->dietaryCertificateStatus();
        if ($certificateStatus !== null) $requiredKeys[] = OnboardingStep::DIETARY_CERTIFICATES->value;
        elseif ($step->step_key === OnboardingStep::DIETARY_CERTIFICATES->value) return;
        $statuses = $restaurant->onboardingSteps()->whereIn('step_key', $requiredKeys)->pluck('status', 'step_key');
        if ($certificateStatus !== null) $statuses->put(OnboardingStep::DIETARY_CERTIFICATES->value, $certificateStatus);
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
            app(RestaurantOnboardingCompletionService::class)->complete($restaurant);
        } elseif ($restaurant->onboarding_status !== $status) {
            $restaurant->update(['onboarding_status' => $status->value]);
        }
    }
}
