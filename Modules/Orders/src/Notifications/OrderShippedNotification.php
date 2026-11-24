<?php

namespace Modules\Orders\Notifications;

class OrderShippedNotification extends OrderLifecycleNotification
{
    public function getEventCode(): string
    {
        return 'order_shipped';
    }
}
