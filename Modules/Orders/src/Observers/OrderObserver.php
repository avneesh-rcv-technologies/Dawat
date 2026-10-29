<?php

namespace Modules\Orders\Observers;

use Modules\Orders\Models\Order;
use Modules\Discounts\Services\DiscountService;
use Illuminate\Support\Facades\Log;
use Modules\Orders\Services\OrderInventoryService;

class OrderObserver
{
    protected DiscountService $discountService;

    public function __construct(DiscountService $discountService, protected OrderInventoryService $inventoryService)
    {
        $this->discountService = $discountService;
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Record coupon usage if coupon was applied
        if ($order->coupon_code && $order->discount > 0) {
            try {
                $this->discountService->recordCouponUsage(
                    $order->coupon_code,
                    $order->customer, // Use polymorphic customer relationship
                    $order->id,
                    $order->discount // Use discount field instead of discount_amount
                );
                
                Log::info("Coupon usage recorded", [
                    'order_id' => $order->id,
                    'coupon_code' => $order->coupon_code,
                    'discount_amount' => $order->discount,
                    'customer_id' => $order->customer?->id,
                    'customer_type' => $order->customer_type
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to record coupon usage", [
                    'order_id' => $order->id,
                    'coupon_code' => $order->coupon_code,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        $restockStatuses = [Order::STATUS_CANCELLED, Order::STATUS_FAILED, Order::STATUS_REFUNDED];
        if ($order->wasChanged('status') && in_array($order->status, $restockStatuses, true)
            && !in_array($order->getRawOriginal('status'), $restockStatuses, true)) {
            $this->inventoryService->restore($order);
        }

        // If order status changed to cancelled and coupon was used, we might want to reverse the usage
        // This is optional based on business logic
        if ($order->wasChanged('status') && $order->status === Order::STATUS_CANCELLED && $order->coupon_code) {
            // Optionally implement coupon usage reversal logic here
            Log::info("Order cancelled with coupon", [
                'order_id' => $order->id,
                'coupon_code' => $order->coupon_code,
                'status' => $order->status
            ]);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // Optionally handle coupon usage cleanup when order is deleted
        if ($order->coupon_code) {
            Log::info("Order deleted with coupon", [
                'order_id' => $order->id,
                'coupon_code' => $order->coupon_code
            ]);
        }
    }
}
