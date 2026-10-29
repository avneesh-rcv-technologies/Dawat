<?php

namespace Modules\Shipping\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Shipping\Models\Shipping;

class ShippingStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Shipping $shipping;
    public string $previousStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Shipping $shipping, string $previousStatus)
    {
        $this->shipping = $shipping;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('shipping.' . $this->shipping->id),
        ];
    }
}