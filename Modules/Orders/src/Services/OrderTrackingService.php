<?php

namespace Modules\Orders\Services;

use Modules\Orders\Models\Order;
use Modules\Restaurant\Models\Restaurant;

class OrderTrackingService
{
    public function getOrderTracking(Order $order): array
    {
        $currentStatus = fn_get_order_status($order->status, 'O');
        $allStatuses = fn_get_order_statuses('O');
        $location = $this->getRestaurantLocation($order);

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'items' => $order->items()->get()->map(fn ($item) => app(OrderPresentationService::class)->item($item))->all(),
            ...app(OrderPresentationService::class)->metadata($order),
            'pricing' => app(OrderPresentationService::class)->pricing($order),
            'delivery_address' => $location['is_pickup_order'] ? null : $order->delivery_address,
            'pickup_address' => $location['pickup_address'],
            'restaurant_details' => $location['restaurant_details'],
            'is_pickup_order' => $location['is_pickup_order'],
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

    /** Shared restaurant and pickup location data for tracking and order details APIs. */
    public function getRestaurantLocation(Order $order): array
    {
        $isPickup = in_array(strtolower((string) $order->order_type), [Order::TYPE_TAKEAWAY, 'pickup'], true);
        $restaurant = $order->vendor_type === Restaurant::class
            ? Restaurant::with(['addresses.city', 'addresses.state', 'addresses.country'])->find($order->vendor_id)
            : null;
        $pickupAddress = $isPickup ? $this->pickupAddress($restaurant) : null;

        return [
            'is_pickup_order' => $isPickup,
            'pickup_address' => $pickupAddress,
            'restaurant_details' => $restaurant ? [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'phone' => $restaurant->phone ?? '',
                // For takeaway, this is also the address customers should use to collect the order.
                'address' => $pickupAddress['full_address'] ?? $this->restaurantAddress($restaurant),
                'pickup_address' => $pickupAddress,
                'city' => $restaurant->city ?? '',
            ] : null,
        ];
    }

    private function pickupAddress(?Restaurant $restaurant): ?array
    {
        if (!$restaurant) {
            return null;
        }

        $address = $restaurant->addresses->firstWhere('address_type', 'primary')
            ?? $restaurant->addresses->firstWhere('is_default', true);
        $city = $address?->city?->name ?? $address?->city_name ?? $restaurant->city;
        $state = $address?->state?->name ?? $restaurant->state;
        $country = $address?->country?->name ?? $restaurant->country;
        $line1 = $address?->address_line_1 ?? $restaurant->address_line1;
        $line2 = $address?->address_line_2 ?? $restaurant->address_line2;
        $zipCode = $address?->zip_code ?? $restaurant->postal_code;

        return [
            'id' => $address?->id,
            'address_line_1' => $line1,
            'address_line_2' => $line2,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'zip_code' => $zipCode,
            'latitude' => $address?->latitude ?? $restaurant->latitude,
            'longitude' => $address?->longitude ?? $restaurant->longitude,
            'full_address' => implode(', ', array_filter([$line1, $line2, $city, $state, $country, $zipCode])),
        ];
    }

    private function restaurantAddress(?Restaurant $restaurant): string
    {
        if (!$restaurant) {
            return '';
        }

        return implode(', ', array_filter([
            $restaurant->address_line1,
            $restaurant->address_line2,
            $restaurant->city,
            $restaurant->state,
            $restaurant->country,
            $restaurant->postal_code,
        ]));
    }
}
