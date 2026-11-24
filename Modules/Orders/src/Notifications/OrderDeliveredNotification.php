<?php

namespace Modules\Orders\Notifications;

class OrderDeliveredNotification extends OrderLifecycleNotification
{
    public function getEventCode(): string
    {
        return 'order_delivered';
    }
}
