<?php

namespace Modules\Orders\Events;

use Modules\Orders\Models\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class OrderStatusUpdated
{
    use Dispatchable, SerializesModels;

    public Order $order;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct($order, string $oldStatus, string $newStatus)
    {
        $this->order        = Order::find($order);
        $this->oldStatus    = $oldStatus;
        $this->newStatus    = $newStatus;
    }
}
