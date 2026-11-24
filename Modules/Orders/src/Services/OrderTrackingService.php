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
            'items' => $order->items()->get()->map(fn ($item) => app(OrderPresentationService::class)->item($item))->all(),
            ...app(OrderPresentationService::class)->metadata($order),
            'pricing' => app(OrderPresentationService::class)->pricing($order),
            'delivery_address' => $order->delivery_address,
            'order_type' => $order->order_type,
            'payment_status' => $order->payment_status,
            'current_status' => [
                'code' => $currentStatus?->status_code ?? $order->status,
                'name' => $currentStatus?->name ?? $order->formatted_status,
                'color' => $currentStatus?->color ?? 'gray',
            ],
            'statuses' => $allStatuses->map(fn($status) => [
                'code' => $status->status_code,
                'name' => $status->name,
                'color' => $status->color,
            ])->toArray(),
        ];
    }
}
