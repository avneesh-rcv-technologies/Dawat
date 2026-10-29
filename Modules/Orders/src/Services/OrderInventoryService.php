<?php

namespace Modules\Orders\Services;

use Illuminate\Support\Facades\Log;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderItem;

class OrderInventoryService
{
    public function restore(Order $order): void
    {
        $order->getConnection()->transaction(function () use ($order) {
            $lockedOrder = $order->newQuery()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedOrder->stock_restored_at !== null || !in_array($lockedOrder->status, [
                Order::STATUS_CANCELLED, Order::STATUS_FAILED, Order::STATUS_REFUNDED,
            ], true)) {
                return;
            }

            foreach ($lockedOrder->items()->get() as $orderItem) {
                $foodItem = $orderItem->item;
                if (!$foodItem) {
                    continue;
                }

                $foodItem = $foodItem->newQuery()->whereKey($foodItem->getKey())->lockForUpdate()->first();
                if (!$foodItem || $foodItem->quantity === null) {
                    continue;
                }

                $quantity = $this->restorableQuantity($orderItem, $foodItem);
                if ($quantity <= 0) {
                    continue;
                }

                $foodItem->increment('quantity', $quantity);

                Log::info('Order inventory quantity restored', [
                    'order_id' => $lockedOrder->getKey(),
                    'item_id' => $foodItem->getKey(),
                    'item_type' => get_class($foodItem),
                    'restored_by' => $quantity,
                ]);
            }

            // The row lock and marker also protect against repeated observer calls.
            $lockedOrder->forceFill(['stock_restored_at' => now()])->saveQuietly();
        });
    }

    public function restorableQuantity(OrderItem $orderItem, \Illuminate\Database\Eloquent\Model $foodItem): int
    {
        if ($orderItem->stock_reserved_quantity !== null) {
            return max(0, (int) $orderItem->stock_reserved_quantity);
        }

        // Older order items have no reservation record. Match the checkout rules.
        if (!$foodItem->in_stock || $foodItem->quantity === null
            || (method_exists($foodItem, 'isGroupingQuantityType') && $foodItem->isGroupingQuantityType())) {
            return 0;
        }

        return max(0, (int) $orderItem->quantity);
    }
}
