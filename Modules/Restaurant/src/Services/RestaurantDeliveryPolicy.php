<?php

namespace Modules\Restaurant\Services;

use Illuminate\Validation\ValidationException;
use Modules\Restaurant\Models\Restaurant;

class RestaurantDeliveryPolicy
{
    public static function rules(): array
    {
        return [
            'delivery_radius_miles' => ['sometimes', 'nullable', 'numeric', 'decimal:0,2', 'gt:0', 'max:999999.99'],
            'delivery_cost_per_mile' => ['sometimes', 'required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999.99'],
            'free_delivery_enabled' => ['sometimes', 'required', 'boolean'],
            'free_delivery_threshold' => ['sometimes', 'nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:999999.99'],
        ];
    }

    public static function messages(): array
    {
        $messages = ['free_delivery_enabled.boolean' => 'Please choose whether free delivery is enabled.'];
        foreach ([
            'delivery_radius_miles' => 'Delivery radius',
            'delivery_cost_per_mile' => 'Delivery cost per mile',
            'free_delivery_threshold' => 'Free delivery order threshold',
        ] as $field => $label) {
            $messages["{$field}.required"] = "Please enter the {$label}.";
            $messages["{$field}.numeric"] = "{$label} must be a valid number.";
            $messages["{$field}.decimal"] = "{$label} can have up to two decimal places.";
            $messages["{$field}.min"] = "{$label} cannot be negative.";
            $messages["{$field}.max"] = "{$label} cannot exceed 999,999.99.";
        }
        $messages['delivery_radius_miles.gt'] = 'Delivery radius must be greater than zero miles.';

        return $messages;
    }

    public static function validateThreshold(Restaurant $restaurant, array $data): void
    {
        $enabled = $data['free_delivery_enabled'] ?? $restaurant->free_delivery_enabled;
        $threshold = array_key_exists('free_delivery_threshold', $data)
            ? $data['free_delivery_threshold'] : $restaurant->free_delivery_threshold;
        if ($enabled && $threshold === null) {
            throw ValidationException::withMessages([
                'free_delivery_threshold' => 'Please enter the minimum order value for free delivery.',
            ]);
        }
    }
}
