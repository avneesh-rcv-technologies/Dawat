<?php

use Modules\Restaurant\Models\Restaurant;

if (!function_exists('fn_get_restaurant_address')) {
    function fn_get_restaurant_address(?int $restaurantId): ?string
    {
        if (!$restaurantId || $restaurantId < 1) return null;

        $restaurant = Restaurant::find($restaurantId);
        if (!$restaurant) return null;

        $address = $restaurant->addresses()->with(['city', 'state', 'country'])
            ->orderByDesc('is_default')->orderBy('id')->first();

        $parts = $address ? [
            $address->address_line_1,
            $address->address_line_2,
            $address->city?->name ?: $address->city_name,
            $address->state?->name,
            $address->country?->name,
            $address->zip_code,
        ] : [
            $restaurant->address_line1,
            $restaurant->address_line2,
            $restaurant->city,
            $restaurant->state,
            $restaurant->country,
            $restaurant->postal_code,
        ];

        $parts = array_filter(array_map(fn ($part) => trim((string) $part), $parts), fn ($part) => $part !== '');

        return $parts ? implode(', ', $parts) : null;
    }
}
