<?php

namespace Modules\Orders\Notifications;

class OrderCancelledNotification extends OrderLifecycleNotification
{
    public function getEventCode(): string
    {
        return 'order_cancelled';
    }
}
