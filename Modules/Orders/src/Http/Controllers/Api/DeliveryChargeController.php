<?php

namespace Modules\Orders\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Orders\Http\Requests\DeliveryChargeRequest;
use Modules\Orders\Services\DeliveryChargeService;
use Modules\Orders\Services\DeliveryCoordinatesService;
use Modules\Restaurant\Models\Restaurant;

class DeliveryChargeController extends Controller
{
    public function __construct(private Restaurant $restaurants)
    {
    }

    public function __invoke(DeliveryChargeRequest $request, DeliveryChargeService $service): JsonResponse
    {
        $data = $request->validated();
        $restaurant = $this->restaurants->newQuery()->findOrFail($data['restaurant_id']);
        $coordinates = app(DeliveryCoordinatesService::class);
        $coordinates->restaurant($restaurant);
        if ($restaurant->exists && $restaurant->isDirty(['latitude', 'longitude'])) $restaurant->saveQuietly();

        if (isset($data['saved_address_id'])) {
            // Resolve through the authenticated customer, never through an unscoped address lookup.
            $address = $request->user('customer')->addresses()->findOrFail($data['saved_address_id']);
            $coordinates->address($address);
            if ($address->exists && $address->isDirty(['latitude', 'longitude'])) $address->saveQuietly();
            $latitude = $address->latitude;
            $longitude = $address->longitude;
            if (!$service->validCoordinates($latitude, $longitude)) {
                throw ValidationException::withMessages([
                    'saved_address_id' => 'This address does not have valid coordinates. Please update it or send latitude and longitude.',
                ]);
            }
        } else {
            $latitude = $data['latitude'];
            $longitude = $data['longitude'];
        }

        $quote = $service->calculate($restaurant, (float) $latitude, (float) $longitude, (float) $data['order_subtotal']);

        return response()->json([
            'success' => true,
            'message' => $quote['delivery_available'] ? 'Delivery charge calculated successfully.' : 'Delivery is unavailable for this address.',
            'data' => $quote,
        ]);
    }
}
