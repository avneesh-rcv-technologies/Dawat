<?php

namespace Modules\Shipping\Listeners;

use Modules\Shipping\Events\ShippingStatusUpdated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UpdateShippingMetrics
{
    /**
     * Handle the event.
     */
    public function handle(ShippingStatusUpdated $event): void
    {
        $shipping = $event->shipping;
        
        // Update delivery metrics when order is delivered
        if ($shipping->status->value === 'delivered') {
            $this->updateDeliveryMetrics($shipping);
        }

        // Update cancellation metrics
        if ($shipping->status->value === 'cancelled') {
            $this->updateCancellationMetrics($shipping);
        }

        // Update pickup metrics for takeaway orders
        if ($shipping->status->value === 'picked_up' && $shipping->shipping_method_code === 'takeaway') {
            $this->updatePickupMetrics($shipping);
        }

        // Clear relevant caches
        $this->clearMetricsCaches();
    }

    /**
     * Update delivery performance metrics.
     */
    protected function updateDeliveryMetrics(object $shipping): void
    {
        $today = now()->toDateString();
        $cacheKey = "shipping_metrics_delivery_{$today}";
        
        // Get current metrics or initialize
        $metrics = Cache::get($cacheKey, [
            'total_deliveries' => 0,
            'on_time_deliveries' => 0,
            'average_delivery_time' => 0,
            'total_delivery_time' => 0,
        ]);

        $metrics['total_deliveries']++;
        
        // Check if delivered on time
        if ($this->wasDeliveredOnTime($shipping)) {
            $metrics['on_time_deliveries']++;
        }

        // Calculate delivery time if both timestamps exist
        if ($shipping->created_at && $shipping->actual_delivery_at) {
            $deliveryTimeMinutes = $shipping->created_at->diffInMinutes($shipping->actual_delivery_at);
            $metrics['total_delivery_time'] += $deliveryTimeMinutes;
            $metrics['average_delivery_time'] = $metrics['total_delivery_time'] / $metrics['total_deliveries'];
        }

        // Cache for 24 hours
        Cache::put($cacheKey, $metrics, now()->addDay());
    }

    /**
     * Update cancellation metrics.
     */
    protected function updateCancellationMetrics(object $shipping): void
    {
        $today = now()->toDateString();
        $cacheKey = "shipping_metrics_cancellation_{$today}";
        
        $metrics = Cache::get($cacheKey, [
            'total_cancellations' => 0,
            'cancellations_by_method' => [],
            'cancellations_by_status' => [],
        ]);

        $metrics['total_cancellations']++;
        
        // Track by shipping method
        $method = $shipping->shipping_method_code;
        $metrics['cancellations_by_method'][$method] = ($metrics['cancellations_by_method'][$method] ?? 0) + 1;
        
        // Track by previous status
        $previousStatus = $shipping->getOriginal('status');
        $metrics['cancellations_by_status'][$previousStatus] = ($metrics['cancellations_by_status'][$previousStatus] ?? 0) + 1;

        Cache::put($cacheKey, $metrics, now()->addDay());
    }

    /**
     * Update pickup metrics for takeaway orders.
     */
    protected function updatePickupMetrics(object $shipping): void
    {
        $today = now()->toDateString();
        $cacheKey = "shipping_metrics_pickup_{$today}";
        
        $metrics = Cache::get($cacheKey, [
            'total_pickups' => 0,
            'on_time_pickups' => 0,
            'average_preparation_time' => 0,
            'total_preparation_time' => 0,
        ]);

        $metrics['total_pickups']++;
        
        // Check if picked up on time (within estimated time)
        if ($this->wasPickedUpOnTime($shipping)) {
            $metrics['on_time_pickups']++;
        }

        // Calculate preparation time
        if ($shipping->created_at && $shipping->updated_at) {
            $preparationTimeMinutes = $shipping->created_at->diffInMinutes($shipping->updated_at);
            $metrics['total_preparation_time'] += $preparationTimeMinutes;
            $metrics['average_preparation_time'] = $metrics['total_preparation_time'] / $metrics['total_pickups'];
        }

        Cache::put($cacheKey, $metrics, now()->addDay());
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

    /**
     * Check if pickup was on time.
     */
    protected function wasPickedUpOnTime(object $shipping): bool
    {
        if (!$shipping->estimated_delivery_at || !$shipping->updated_at) {
            return false;
        }

        return $shipping->updated_at <= $shipping->estimated_delivery_at;
    }

    /**
     * Clear metrics caches.
     */
    protected function clearMetricsCaches(): void
    {
        $patterns = [
            'shipping_metrics_*',
            'shipping_stats_*',
            'delivery_performance_*',
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}