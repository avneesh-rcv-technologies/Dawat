<?php

namespace Modules\Orders\Services;

use Modules\Orders\Models\Order;

class OrderTrackingService
{
    public function getOrderTracking(Order $order): array
    {
        $currentStatus = fn_get_order_status($order->status, 'O');
        $allStatuses = fn_get_order_statuses('O');

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'items' => $order->items()->get()->toArray(),
            'current_status' => [
                'code' => $currentStatus->status_code,
                'name' => $currentStatus->name,
                'color' => $currentStatus->color,
            ],
            'statuses' => $allStatuses->map(fn($status) => [
                'code' => $status->status_code,
                'name' => $status->name,
                'color' => $status->color,
            ])->toArray(),
        ];
    }
}
