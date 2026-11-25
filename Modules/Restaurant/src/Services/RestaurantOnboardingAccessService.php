<?php

namespace Modules\Restaurant\Services;

use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;

class RestaurantOnboardingAccessService
{
    public static function isStepEditable(?string $status): bool
    {
        return in_array($status, [null, 'draft', 'submitted', 'pending', 'rejected', 'action_required'], true);
    }

    public static function assertStepEditable(Restaurant $restaurant, OnboardingStep $step): void
    {
        $status = $restaurant->onboardingSteps()->where('step_key', $step->value)
            ->lockForUpdate()->value('status');
        if (!self::isStepEditable($status)) {
            $message = $status === OnboardingStatus::UNDER_REVIEW->value
                ? 'This step is under review and cannot be updated until the review is complete.'
                : 'This step cannot be updated in its current status. Ask an administrator to Request Replacement first.';
            throw ValidationException::withMessages([$step->value => $message]);
        }
    }

    public static function assertStepNotApproved(Restaurant $restaurant, OnboardingStep $step): void
    {
        self::assertStepEditable($restaurant, $step);
    }

    public function forAdmin(Admin $admin): ?Restaurant
    {
        return Restaurant::where('admin_id', $admin->id)
            ->whereIn('approval_status', ['pending', 'approved', 'rejected'])
            ->where('onboarding_status', '!=', OnboardingStatus::APPROVED->value)
            ->whereIn('status', [OnboardingStatus::DRAFT->value, OnboardingStatus::ACTIVE->value])
            ->where(fn ($query) => $query->whereIn('onboarding_status', [
                OnboardingStatus::DRAFT->value,
                OnboardingStatus::ACTION_REQUIRED->value,
                OnboardingStatus::UNDER_REVIEW->value,
            ])->orWhereHas('certifications', fn ($query) => $query->whereNotNull('file_path')
                ->where('file_path', '!=', '')->where('status', '!=', 'approved')))
            ->orderBy('id')
            ->first();
    }
}
