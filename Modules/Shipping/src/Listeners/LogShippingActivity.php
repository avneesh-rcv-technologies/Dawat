<?php

namespace Modules\Shipping\Listeners;

use Modules\Shipping\Events\ShippingStatusUpdated;
use Illuminate\Support\Facades\Log;

class LogShippingActivity
{
    /**
     * Handle the event.
     */
    public function handle(ShippingStatusUpdated $event): void
    {
        $shipping = $event->shipping;
        
        // Log the shipping status change
        Log::info('Shipping status updated', [
            'shipping_id' => $shipping->id,
            'order_id' => $shipping->order_id,
            'tracking_number' => $shipping->tracking_number,
            'previous_status' => $event->previousStatus,
            'new_status' => $shipping->status->value,
            'shipping_method' => $shipping->shipping_method_code,
            'updated_at' => $shipping->updated_at->toISOString(),
        ]);

        // Log specific events for analytics
        if ($shipping->status->value === 'delivered') {
            Log::info('Order delivered successfully', [
                'shipping_id' => $shipping->id,
                'order_id' => $shipping->order_id,
                'delivery_time' => $shipping->actual_delivery_at?->toISOString(),
                'estimated_time' => $shipping->estimated_delivery_at?->toISOString(),
                'on_time' => $this->wasDeliveredOnTime($shipping),
            ]);
        }

        if ($shipping->status->value === 'cancelled') {
            Log::warning('Shipping cancelled', [
                'shipping_id' => $shipping->id,
                'order_id' => $shipping->order_id,
                'previous_status' => $event->previousStatus,
                'cancelled_at' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Check if delivery was on time.
     */
    protected function wasDeliveredOnTime(object $shipping): bool
    {
        if (!$shipping->estimated_delivery_at || !$shipping->actual_delivery_at) {
            return false;
        }

        return $shipping->actual_delivery_at <= $shipping->estimated_delivery_at;
    }
}