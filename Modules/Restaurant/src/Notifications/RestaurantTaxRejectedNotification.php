<?php

namespace Modules\Restaurant\Notifications;

class RestaurantTaxRejectedNotification extends RestaurantTaxApprovedNotification
{
    protected const EVENT_CODE = 'restaurant_tax_rejected';
}
