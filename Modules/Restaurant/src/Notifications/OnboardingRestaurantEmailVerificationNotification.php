<?php

namespace Modules\Restaurant\Notifications;

use Modules\Restaurant\Services\RestaurantOnboardingOtpService;

class OnboardingRestaurantEmailVerificationNotification extends RestaurantEmailVerificationNotification
{
    public function getEventCode(): string
    {
        return RestaurantOnboardingOtpService::TYPE;
    }
}
