<?php

namespace Modules\Orders\Services;

use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderItem;

class OrderPresentationService
{
    public function item(OrderItem $item): array
    {
        return [
            'id' => $item->id, 'item_id' => $item->item_id, 'item_type' => $item->item_type,
            'name' => $item->item_name, 'item_name' => $item->item_name,
            'quantity' => (int) $item->quantity, 'price' => $item->price, 'unit_price' => $item->price,
            'total_price' => $item->total_price, 'subtotal' => $item->total_price,
            'serving_option_id' => $item->serving_option_id, 'serve_type' => $item->serve_type,
            'serving_name' => $item->serving_name, 'guest_count' => $item->guest_count,
            'selected_option_snapshot' => $item->selected_option_snapshot,
            'tax_rate' => $item->tax_rate, 'tax_amount' => $item->getTaxAmount(),
            'discount_amount' => $item->discount_amount ?? '0.00',
            'final_price' => $item->final_price ?? number_format((float) $item->total_price + ($item->is_tax_inclusive ? 0 : $item->getTaxAmount()), 2, '.', ''),
            'is_tax_inclusive' => $item->is_tax_inclusive, 'tax_breakdown' => $item->tax_breakdown,
            'delivery_fee' => $item->delivery_fee, 'estimated_delivery_minutes' => $item->estimated_delivery_minutes,
            'estimated_delivery_at' => $item->estimated_delivery_at,
            'selected_options' => $item->selected_options, 'special_instructions' => $item->special_instructions,
        ];
    }

    public function pricing(Order $order): array
    {
        return [
            'subtotal' => (float) $order->subtotal, 'tax' => (float) $order->tax,
            'delivery_fee' => (float) $order->delivery_fee, 'service_charge' => (float) $order->service_charge,
            'discount' => (float) $order->discount, 'total' => (float) $order->total,
            'is_tax_inclusive' => $order->is_tax_inclusive, 'tax_breakdown' => $order->tax_breakdown,
            'discount_breakdown' => $order->discount_breakdown,
        ];
    }

    public function metadata(Order $order): array
    {
        return [
            'currency_code' => $order->currency, 'event_guest_count' => $order->event_guest_count,
            'delivery_distance_miles' => $order->delivery_distance_miles,
            'estimated_delivery_minutes' => $order->estimated_delivery_minutes,
            'preferred_delivery_date' => $order->preferred_delivery_date,
            'estimated_delivery_time' => $order->estimated_delivery_time,
            'is_free_delivery' => $order->order_type === 'delivery' && (float) $order->delivery_fee === 0.0,
            'delivery_policy_snapshot' => $order->delivery_policy_snapshot,
        ];
    }
}
