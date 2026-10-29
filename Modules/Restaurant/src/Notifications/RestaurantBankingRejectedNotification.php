<?php

namespace Modules\Restaurant\Notifications;

class RestaurantBankingRejectedNotification extends RestaurantBankingApprovedNotification
{
    protected const EVENT_CODE = 'restaurant_banking_rejected';
}
