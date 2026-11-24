<?php

namespace Modules\Orders\Services;

use Modules\Cart\Models\Cart;
use Modules\Restaurant\Models\Restaurant;

class CartOrderSnapshotService
{
    public function items(Cart $cart): array
    {
        return $cart->items->map(fn ($item) => [
            'item_id' => $item->item_id, 'item_type' => $item->item_type,
            'item_name' => $item->selected_option_snapshot['item_name'] ?? $item->item?->name,
            'quantity' => $item->quantity,
            'price' => ($item->selected_option_snapshot['pricing_basis'] ?? null) === 'per_person'
                ? ($item->selected_option_snapshot['effective_price'] ?? $item->unit_price) : $item->unit_price,
            'total_price' => $item->total_price,
            'selected_options' => $item->selected_options,
            'special_instructions' => $item->note ?? $item->special_instructions,
            'serving_option_id' => $item->serving_option_id, 'serve_type' => $item->serve_type,
            'serving_name' => $item->serving_name, 'guest_count' => $item->guest_count,
            'selected_option_snapshot' => $item->selected_option_snapshot,
            'tax_rate' => $item->selected_option_snapshot['tax_percentage'] ?? $item->item?->tax_percentage ?? 0,
            'tax_amount' => $item->total_tax_amount, 'discount_amount' => $item->selected_option_snapshot !== null ? $item->discount_price : 0,
            'final_price' => $item->total_display_price, 'is_tax_inclusive' => $item->is_tax_inclusive,
            'tax_breakdown' => $item->tax_breakdown, 'delivery_fee' => $item->delivery_fee,
            'estimated_delivery_minutes' => $item->estimated_delivery_minutes,
            'estimated_delivery_at' => $item->estimated_delivery_at,
        ])->all();
    }

    public function metadata(Cart $cart, Restaurant $restaurant): array
    {
        return [
            'event_guest_count' => $cart->event_guest_count,
            'delivery_distance_miles' => $cart->delivery_distance_miles,
            'estimated_delivery_minutes' => $cart->estimated_delivery_minutes,
            'is_tax_inclusive' => $cart->is_tax_inclusive,
            'tax_breakdown' => $cart->tax_breakdown, 'discount_breakdown' => $cart->discount_breakdown,
            'delivery_policy_snapshot' => [
                'delivery_radius_miles' => $restaurant->delivery_radius_miles,
                'delivery_cost_per_mile' => $restaurant->delivery_cost_per_mile,
                'free_delivery_enabled' => (bool) $restaurant->free_delivery_enabled,
                'free_delivery_threshold' => $restaurant->free_delivery_threshold,
                'distance_method' => 'straight_line', 'delivery_fee' => $cart->delivery_fee,
                'is_free_delivery' => (bool) $cart->delivery_available && $cart->delivery_fee !== null && (float) $cart->delivery_fee === 0.0,
                'eligible_subtotal' => max(0, round((float) $cart->subtotal - $cart->coupon_discount_amount, 2)),
                'calculated_at' => $cart->delivery_calculated_at?->toIso8601String(),
            ],
        ];
    }
}
