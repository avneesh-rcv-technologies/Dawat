<?php

namespace Modules\Checkout\Services;

use Illuminate\Http\Request;
use Modules\Acl\Models\Customer;
use Modules\Cart\Http\Resources\V2\CartItemResource;
use Modules\Cart\Models\Cart;

class ReviewOrderSummary
{
    /** Build from recalculated cart totals, matching the snapshot used at placement. */
    public function build(Cart $cart, Customer $customer, Request $request, string $currency, float $serviceFee = 0): array
    {
        $groups = [];
        $items = [];
        foreach ($cart->items as $line) {
            $item = (new CartItemResource($line))->resolve($request);
            // Keep the original review fields alongside the detailed cart representation.
            $item = array_merge($item, [
                'cart_item_id' => $line->id,
                'id' => $line->item_id,
                'name' => $item['item_name'],
                'unit_price' => $item['pricing']['unit_price'],
                'total_price' => $line->total_price,
                'currency' => $currency,
                'selected_options' => $line->selected_options ?? [],
                'special_instructions' => $line->note ?? $line->special_instructions,
            ]);
            $items[] = $item;

            // One review line per cart line, even for items with multiple categories.
            $category = $line->item?->categories
                ->where('restaurant_id', $cart->cartable_id)->sortBy('id')->first();
            $key = $category?->id ?? 'uncategorized';
            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'category_id' => $category?->id,
                    'category_name' => $category?->name ?? 'Other items',
                    'item_count' => 0,
                    'items' => [],
                ];
            }
            $groups[$key]['items'][] = $item;
            $groups[$key]['item_count']++;
        }

        return [
            'cart_id' => $cart->id,
            // A preview reference; the actual order number is created at placement.
            'order_ref' => 'ORD-'.str_pad((string) $cart->id, 6, '0', STR_PAD_LEFT),
            'delivery_method' => $customer->guest_method,
            'guest_count' => $customer->number_of_guests,
            'event_guest_count' => $cart->event_guest_count,
            'preferred_delivery_date' => $customer->preferred_delivery_date,
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ],
            'currency' => $currency,
            'items' => $items,
            'line_count' => count($items),
            'items_by_category' => array_values($groups),
            'coupon_code' => $cart->coupon_code,
            'pricing' => [
                'subtotal_before_discount' => round((float) $cart->subtotal + $cart->item_discount_amount, 2),
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax_amount,
                'tax_breakdown' => $cart->tax_breakdown ?? [],
                'is_tax_inclusive' => $cart->is_tax_inclusive,
                'delivery_fee' => $cart->delivery_fee,
                'delivery_available' => $cart->delivery_available,
                'delivery_unavailable_reason' => $cart->delivery_unavailable_reason,
                'delivery_distance_miles' => $cart->delivery_distance_miles,
                'is_free_delivery' => $customer->guest_method === 'Delivery' && $cart->delivery_available
                    && $cart->delivery_fee !== null && (float) $cart->delivery_fee === 0.0,
                'estimated_delivery_minutes' => $cart->estimated_delivery_minutes,
                'estimated_delivery_at' => $cart->estimated_delivery_at,
                'service_fee' => $serviceFee,
                'service_charge' => $serviceFee,
                'item_discount' => $cart->item_discount_amount,
                'coupon_discount' => $cart->coupon_discount_amount,
                'discount' => $cart->discount_amount,
                'discount_breakdown' => $cart->discount_breakdown,
                'total' => round((float) $cart->total + $serviceFee, 2),
                'currency' => $currency,
            ],
        ];
    }
}
