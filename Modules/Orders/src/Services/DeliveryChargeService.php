<?php

namespace Modules\Orders\Services;

use Illuminate\Validation\ValidationException;
use Modules\Restaurant\Models\Restaurant;

class DeliveryChargeService
{
    public function calculate(Restaurant $restaurant, float $latitude, float $longitude, float $subtotal): array
    {
        if (!$this->validCoordinates($restaurant->latitude, $restaurant->longitude)) {
            throw ValidationException::withMessages([
                'restaurant_id' => 'The restaurant does not have valid address coordinates. Please update its address.',
            ]);
        }

        $distance = $this->distanceMiles((float) $restaurant->latitude, (float) $restaurant->longitude, $latitude, $longitude);
        $radius = $restaurant->delivery_radius_miles === null ? null : (float) $restaurant->delivery_radius_miles;
        $rate = (float) ($restaurant->delivery_cost_per_mile ?? 0);
        $threshold = $restaurant->free_delivery_threshold === null ? null : (float) $restaurant->free_delivery_threshold;
        $reason = match (true) {
            !$restaurant->is_delivery => 'delivery_disabled',
            $radius !== null && $distance > $radius => 'outside_delivery_radius',
            default => null,
        };
        $available = $reason === null;
        $freeDeliveryApplied = $available && $restaurant->free_delivery_enabled
            && $threshold !== null && $subtotal >= $threshold;
        // Use the full distance for radius checks and pricing; round only the final fee.
        $fee = $available ? ($freeDeliveryApplied ? 0.0 : round($distance * $rate, 2)) : null;
        // An estimate from straight-line miles, not a Google road or traffic ETA.
        $minutes = $available ? max(0, (int) config('orders.delivery.preparation_minutes', 30))
            + (int) ceil($distance / max(1, (float) config('orders.delivery.average_speed_mph', 25)) * 60) : null;

        return [
            'restaurant_id' => $restaurant->getKey(),
            'distance_miles' => round($distance, 4),
            'distance_method' => 'straight_line',
            'order_subtotal' => round($subtotal, 2),
            'delivery_available' => $available,
            'reason' => $reason,
            'delivery_fee' => $fee,
            'is_free_delivery' => $available && $fee === 0.0,
            'free_delivery_applied' => $freeDeliveryApplied,
            'estimated_delivery_minutes' => $minutes,
            'estimated_delivery_at' => $minutes === null ? null : now()->addMinutes($minutes)->toIso8601String(),
            'estimate_method' => 'preparation_plus_straight_line_travel',
            'policy' => [
                'delivery_radius_miles' => $radius,
                'delivery_cost_per_mile' => $rate,
                'free_delivery_enabled' => (bool) $restaurant->free_delivery_enabled,
                'free_delivery_threshold' => $threshold,
            ],
        ];
    }

    public function validCoordinates(mixed $latitude, mixed $longitude): bool
    {
        return is_numeric($latitude) && is_numeric($longitude)
            && is_finite((float) $latitude) && is_finite((float) $longitude)
            && (float) $latitude >= -90 && (float) $latitude <= 90
            && (float) $longitude >= -180 && (float) $longitude <= 180;
    }

    private function distanceMiles(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): float
    {
        $latitudeDifference = deg2rad($toLatitude - $fromLatitude);
        $longitudeDifference = deg2rad($toLongitude - $fromLongitude);
        $a = sin($latitudeDifference / 2) ** 2
            + cos(deg2rad($fromLatitude)) * cos(deg2rad($toLatitude)) * sin($longitudeDifference / 2) ** 2;

        return 3958.7613 * 2 * asin(sqrt(max(0, min(1, $a))));
    }
}
