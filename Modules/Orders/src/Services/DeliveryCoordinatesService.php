<?php

namespace Modules\Orders\Services;

use Illuminate\Support\Facades\Log;
use Modules\Countries\Models\Address;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\GeocodingService;
use Throwable;

class DeliveryCoordinatesService
{
    public function address(Address $address, bool $force = false): bool
    {
        return $this->resolve($address, $force, fn () => [
            'address_line1' => $address->address_line_1,
            'address_line2' => $address->address_line_2,
            'city_name' => $address->city?->name ?? $address->city_name,
            'state_name' => $address->state?->name,
            'country_name' => $address->country?->name,
            'postal_code' => $address->zip_code,
        ]);
    }

    public function restaurant(Restaurant $restaurant): bool
    {
        return $this->resolve($restaurant, false, fn () => [
            'address_line1' => $restaurant->address_line1, 'address_line2' => $restaurant->address_line2,
            'city_name' => $restaurant->city, 'state_name' => $restaurant->state,
            'country_name' => $restaurant->country, 'postal_code' => $restaurant->postal_code,
        ]);
    }

    private function resolve(Address|Restaurant $model, bool $force, callable $components): bool
    {
        if (!$force && app(DeliveryChargeService::class)->validCoordinates($model->latitude, $model->longitude)) {
            return true;
        }
        // Never retain coordinates from the previous street address after an edit.
        $model->latitude = null;
        $model->longitude = null;
        try {
            $coordinates = app(GeocodingService::class)->geocodeAddress($components());
            if ($coordinates && app(DeliveryChargeService::class)->validCoordinates($coordinates['latitude'], $coordinates['longitude'])) {
                $model->fill($coordinates);
            }
        } catch (Throwable $e) {
            Log::warning('Delivery address geocoding unavailable.', ['exception_class' => get_class($e)]);
        }

        return app(DeliveryChargeService::class)->validCoordinates($model->latitude, $model->longitude);
    }
}
