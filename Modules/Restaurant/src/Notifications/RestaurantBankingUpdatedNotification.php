<?php

namespace Modules\Restaurant\Notifications;

class RestaurantBankingUpdatedNotification extends RestaurantBankingSubmittedNotification
{
    protected const EVENT_CODE = 'restaurant_banking_updated';
}
