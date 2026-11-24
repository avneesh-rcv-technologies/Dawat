<?php

namespace Modules\Orders\Notifications;

use Modules\Orders\Models\Order;

/** Shared customer recipient definitions for the explicitly seeded lifecycle events. */
abstract class OrderLifecycleNotification extends OrderStatusUpdateNotification
{
    public function __construct($order = null, $data = [])
    {
        parent::__construct($order instanceof Order ? array_merge($data, ['order' => $order]) : $order, $data);
    }

    public function getVariables(): array
    {
        $variables = parent::getVariables();
        $order = $this->data['order'] ?? null;
        if (!$order instanceof Order) return $variables;

        return array_merge($variables, [
            'user_name' => $variables['customer_name'] ?? 'Customer',
            'estimated_delivery' => $variables['estimated_delivery_time'] ?? 'Not available',
            'estimated_preparation_time' => $variables['estimated_delivery_time'] ?? 'Not available',
            'delivery_date' => $order->actual_delivery_time?->format('M d, Y h:i A') ?? 'Not recorded',
            'cancelled_date' => $order->cancelled_at?->format('M d, Y h:i A') ?? 'Not recorded',
            'cancellation_reason' => $order->cancellation_reason ?: 'Not provided',
            'tracking_number' => 'Not provided', 'delivery_partner' => $variables['vendor_name'] ?? 'Restaurant',
            'tracking_link' => $variables['order_link'] ?? '', 'review_link' => $variables['order_link'] ?? '',
            'browse_menu_link' => url('/'), 'refund_processing_days' => '5–7',
        ]);
    }
}
