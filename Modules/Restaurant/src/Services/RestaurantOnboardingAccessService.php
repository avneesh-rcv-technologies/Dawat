<?php

namespace Modules\Restaurant\Services;

use Modules\Acl\Models\Admin;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Models\Restaurant;

class RestaurantOnboardingAccessService
{
    public function forAdmin(Admin $admin): ?Restaurant
    {
        return Restaurant::where('admin_id', $admin->id)
            ->whereIn('approval_status', ['pending', 'approved', 'rejected'])
            ->whereIn('status', [OnboardingStatus::DRAFT->value, OnboardingStatus::ACTIVE->value])
            ->whereIn('onboarding_status', [
                OnboardingStatus::DRAFT->value,
                OnboardingStatus::ACTION_REQUIRED->value,
                OnboardingStatus::UNDER_REVIEW->value,
            ])
            ->orderBy('id')
            ->first();
    }
}
