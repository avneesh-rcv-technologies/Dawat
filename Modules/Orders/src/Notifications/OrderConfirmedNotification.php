<?php

namespace Modules\Orders\Notifications;

class OrderConfirmedNotification extends OrderLifecycleNotification
{
    public function getEventCode(): string
    {
        return 'order_confirmed';
    }
}
