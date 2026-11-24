<?php

namespace Modules\Orders\Services;

use Modules\Orders\Models\Order;

class OrderNotificationDetails
{
    public function variables(Order $order): array
    {
        $currency = $order->currency ?: 'USD';
        $money = fn ($amount) => function_exists('fn_format_currency_amount')
            ? fn_format_currency_amount($amount, $currency) : $currency.' '.number_format((float) $amount, 2);
        $items = $order->relationLoaded('items') ? $order->items : $order->items()->get();
        $lines = $items->map(function ($item) use ($money) {
            $perPerson = ($item->selected_option_snapshot['pricing_basis'] ?? null) === 'per_person';
            $selection = $item->serving_name ? ' — '.e($item->serving_name) : '';
            $units = $perPerson ? (int) $item->guest_count.' guests' : (int) $item->quantity.' × '.$money($item->price);
            return e($item->item_name ?? 'Item').$selection.' | '.$units.' | '.$money($item->final_price ?? $item->total_price)
                .($item->selected_option_snapshot !== null ? ' (tax '.$money($item->tax_amount ?? 0).', discount '.$money($item->discount_amount ?? 0).')' : '');
        });

        return [
            'order_items' => $lines->implode("\n"), 'event_guest_count' => $order->event_guest_count ?? 'Not specified',
            'delivery_distance_miles' => $order->delivery_distance_miles === null ? 'N/A' : number_format((float) $order->delivery_distance_miles, 2).' miles',
            'estimated_delivery_minutes' => $order->estimated_delivery_minutes === null ? 'N/A' : $order->estimated_delivery_minutes.' minutes',
            'delivery_label' => $order->order_type === 'delivery' && (float) $order->delivery_fee === 0.0 ? 'Free delivery' : ucfirst($order->order_type ?? 'delivery'),
            'service_charge' => $money($order->service_charge ?? 0),
        ];
    }

    public static function availableVariables(): array
    {
        return [
            'event_guest_count' => 'Overall event guest count', 'delivery_distance_miles' => 'Saved delivery distance in miles',
            'estimated_delivery_minutes' => 'Saved delivery duration estimate', 'delivery_label' => 'Delivery type or free delivery',
            'service_charge' => 'Formatted service charge', 'order_items' => 'Saved serving selections, guests, tax and discounts',
        ];
    }
}
