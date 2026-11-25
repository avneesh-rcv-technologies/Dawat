<?php

namespace Modules\Cart\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Restaurant\Models\Restaurant;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $itemDiscount = $this->item_discount_amount;
        $couponDiscount = $this->coupon_discount_amount;

        return [
            'id' => $this->id,
            'session_id' => $this->session_id,
            'restaurant_id' => $this->cartable_id,
            'restaurant' => self::restaurantSummary((int) $this->cartable_id),
            'event_guest_count' => $this->event_guest_count,
            'delivery_address_id' => $this->delivery_address_id,
            'delivery_fee' => $this->delivery_fee,
            'is_free_delivery' => (bool) $this->delivery_available
                && $this->delivery_fee !== null && (float) $this->delivery_fee === 0.0,
            'delivery_distance_miles' => $this->delivery_distance_miles,
            'delivery_available' => $this->delivery_available,
            'delivery_unavailable_reason' => $this->delivery_unavailable_reason,
            'estimated_delivery_minutes' => $this->estimated_delivery_minutes,
            'estimated_delivery_at' => $this->estimated_delivery_at,
            'items' => CartItemResource::collection($this->items),
            'line_count' => $this->items->count(),
            'counts' => [
                'fixed_option_quantity' => $this->items->filter(fn ($item) => ($item->selected_option_snapshot['pricing_basis'] ?? null) === 'per_option')->sum('quantity'),
                'per_person_selection_count' => $this->items->filter(fn ($item) => ($item->selected_option_snapshot['pricing_basis'] ?? null) === 'per_person')->count(),
                'legacy_line_count' => $this->items->filter(fn ($item) => $item->selected_option_snapshot === null)->count(),
            ],
            'subtotal_before_discount' => number_format((float) $this->subtotal + $itemDiscount, 2, '.', ''),
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'item_discount_amount' => number_format($itemDiscount, 2, '.', ''),
            'coupon_discount_amount' => number_format($couponDiscount, 2, '.', ''),
            'discount_amount' => $this->discount_amount,
            'discount_percentage' => $this->discount_percentage,
            'discount_breakdown' => $this->discount_breakdown,
            'total' => $this->total,
            'tax_breakdown' => $this->tax_breakdown,
        ];
    }

    public static function emptyCart(?int $restaurantId, ?string $sessionId = null): array
    {
        return [
            'id' => null, 'session_id' => $sessionId, 'restaurant_id' => $restaurantId,
            'restaurant' => $restaurantId ? self::restaurantSummary($restaurantId) : null,
            'event_guest_count' => null, 'items' => [],
            'line_count' => 0, 'counts' => ['fixed_option_quantity' => 0, 'per_person_selection_count' => 0, 'legacy_line_count' => 0],
            'subtotal_before_discount' => '0.00', 'subtotal' => '0.00', 'tax_amount' => '0.00',
            'item_discount_amount' => '0.00', 'coupon_discount_amount' => '0.00',
            'discount_amount' => '0.00', 'discount_percentage' => '0.00', 'discount_breakdown' => null,
            'total' => '0.00', 'tax_breakdown' => [],
            'delivery_address_id' => null, 'delivery_fee' => null, 'delivery_distance_miles' => null,
            'delivery_available' => false, 'delivery_unavailable_reason' => 'empty_cart',
            'is_free_delivery' => false,
            'estimated_delivery_minutes' => null, 'estimated_delivery_at' => null,
        ];
    }

    private static function restaurantSummary(int $restaurantId): ?array
    {
        $restaurant = Restaurant::with(['addresses.city', 'addresses.state', 'addresses.country'])->find($restaurantId);
        if (!$restaurant) {
            return null;
        }

        $address = $restaurant->addresses->firstWhere('address_type', 'primary')
            ?? $restaurant->addresses->firstWhere('is_default', true)
            ?? $restaurant->addresses->first();
        $line1 = $address?->address_line_1 ?: $restaurant->address_line1;
        $line2 = $address?->address_line_2 ?: $restaurant->address_line2;
        $city = $address?->city?->name ?: ($address?->city_name ?: $restaurant->city);
        $state = $address?->state?->name ?: $restaurant->state;
        $country = $address?->country?->name ?: $restaurant->country;
        $zipCode = $address?->zip_code ?: $restaurant->postal_code;

        return [
            'id' => $restaurant->id,
            'name' => $restaurant->name,
            'address' => [
                'address_line_1' => $line1,
                'address_line_2' => $line2,
                'city' => $city,
                'state' => $state,
                'country' => $country,
                'zip_code' => $zipCode,
                'latitude' => $address?->latitude ?? $restaurant->latitude,
                'longitude' => $address?->longitude ?? $restaurant->longitude,
                'full_address' => implode(', ', array_filter([$line1, $line2, $city, $state, $country, $zipCode])),
            ],
        ];
    }
}
