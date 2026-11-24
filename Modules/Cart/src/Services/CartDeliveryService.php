<?php

namespace Modules\Cart\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Acl\Models\Customer;
use Modules\Cart\Models\Cart;
use Modules\Orders\Services\DeliveryChargeService;
use Modules\Orders\Services\DeliveryCoordinatesService;
use Modules\Restaurant\Models\Restaurant;
use Throwable;

class CartDeliveryService
{
    public function __construct(private DeliveryChargeService $pricing, private DeliveryCoordinatesService $coordinates) {}

    /** Apply once while Cart::updateTotals is computing the food total. */
    public function apply(Cart $cart): void
    {
        $cart->fill([
            'delivery_fee' => null, 'delivery_distance_miles' => null, 'delivery_available' => false,
            'delivery_unavailable_reason' => 'delivery_address_required', 'estimated_delivery_minutes' => null,
            'estimated_delivery_at' => null, 'delivery_calculated_at' => now(),
        ]);
        try {
            $customer = $cart->owner()->first();
            $restaurant = $cart->cartable()->first();
            if (!$customer instanceof Customer || !$restaurant instanceof Restaurant) {
                $cart->delivery_unavailable_reason = 'delivery_address_required';
            } elseif (strtolower((string) $customer->guest_method) === 'pickup') {
                $cart->delivery_fee = 0;
                $cart->delivery_unavailable_reason = 'pickup';
            } else {
                $address = $cart->delivery_address_id
                    ? $customer->addresses()->find($cart->delivery_address_id)
                    : $customer->addresses()->where('is_default', true)->first();
                if ($address) {
                    $cart->delivery_address_id = $address->id;
                    $customerLocated = $this->coordinates->address($address);
                    $restaurantLocated = $this->coordinates->restaurant($restaurant);
                    if ($address->isDirty(['latitude', 'longitude'])) $address->saveQuietly();
                    if ($restaurant->isDirty(['latitude', 'longitude'])) $restaurant->saveQuietly();
                    if ($customerLocated && $restaurantLocated) {
                        $quote = $this->pricing->calculate($restaurant, (float) $address->latitude, (float) $address->longitude,
                            max(0, round((float) $cart->subtotal - $cart->coupon_discount_amount, 2)));
                        if ($quote['delivery_fee'] !== null && (float) $cart->total + $quote['delivery_fee'] > 99999999.99) {
                            throw new \RuntimeException('Delivery amount exceeds the cart storage limit.');
                        }
                        $cart->fill([
                            'delivery_fee' => $quote['delivery_fee'], 'delivery_distance_miles' => $quote['distance_miles'],
                            'delivery_available' => $quote['delivery_available'], 'delivery_unavailable_reason' => $quote['reason'],
                            'estimated_delivery_minutes' => $quote['estimated_delivery_minutes'],
                            'estimated_delivery_at' => $quote['estimated_delivery_at'],
                        ]);
                    } else {
                        $cart->delivery_unavailable_reason = 'geocoding_unavailable';
                    }
                }
            }
        } catch (Throwable $e) {
            // Address/cart mutations can succeed while a delivery quote is unavailable.
            $cart->delivery_unavailable_reason = 'delivery_calculation_unavailable';
            Log::warning('Cart delivery calculation unavailable.', ['cart_id' => $cart->id, 'exception_class' => get_class($e)]);
        }
        $this->allocateToItems($cart);
    }

    /** Allocate in cents so line fees sum exactly to the single cart charge. */
    public function allocateToItems(Cart $cart): void
    {
        $items = $cart->items->sortBy('id')->values();
        $totalWeight = $items->sum(fn ($item) => max(0, (int) round((float) $item->total_price * 100)));
        $feeCents = (int) round((float) $cart->delivery_fee * 100);
        $weight = 0;
        $allocated = 0;
        foreach ($items as $index => $item) {
            $weight += max(0, (int) round((float) $item->total_price * 100));
            $cumulative = (int) round($feeCents * ($totalWeight > 0 ? $weight / $totalWeight : ($index + 1) / $items->count()));
            $item->fill([
                'delivery_fee' => $cart->delivery_fee === null ? null : ($cumulative - $allocated) / 100,
                'estimated_delivery_minutes' => $cart->estimated_delivery_minutes,
                'estimated_delivery_at' => $cart->estimated_delivery_at,
            ]);
            $item->saveQuietly();
            $allocated = $cumulative;
        }
    }

    public function refreshCustomer(Customer $customer, ?int $selectedAddressId = null, ?int $changedAddressId = null): array
    {
        return DB::transaction(function () use ($customer, $selectedAddressId, $changedAddressId) {
            Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $carts = $customer->ownedCarts()->where('cartable_type', (new Restaurant())->getMorphClass())
                ->lockForUpdate()->get();
            foreach ($carts as $cart) {
                if ($selectedAddressId !== null) {
                    $cart->delivery_address_id = $selectedAddressId;
                } elseif ($changedAddressId !== null && (int) $cart->delivery_address_id !== $changedAddressId) {
                    continue;
                }
                $cart->updateTotals();
                DB::afterCommit(function () use ($customer, $cart) {
                    try {
                        app(CartCacheService::class)->forgetCart($customer->id, $cart->cartable_id);
                        app(\Modules\Checkout\Services\CheckoutCacheService::class)->forgetReviewOrder($customer->id, $cart->cartable_id);
                    } catch (Throwable $e) {
                        Log::warning('Delivery cache invalidation failed.', ['cart_id' => $cart->id, 'exception_class' => get_class($e)]);
                    }
                });
            }

            return $carts->map(fn ($cart) => $this->summary($cart))->all();
        });
    }

    public function summary(Cart $cart): array
    {
        return $cart->only([
            'id', 'cartable_id', 'delivery_address_id', 'delivery_fee', 'delivery_distance_miles', 'delivery_available',
            'delivery_unavailable_reason', 'estimated_delivery_minutes', 'estimated_delivery_at', 'delivery_calculated_at', 'total',
        ]);
    }
}
