<?php

namespace Modules\Checkout\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Cart\Models\Cart;
use Modules\Countries\Models\Address;
use Modules\Acl\Models\Customer;

class OrderReviewService
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * Get order review data
     *
     * @param Customer $customer
     * @param int $restaurantId
     * @param string $shippingMethod
     * @param string $paymentMethod
     * @return array
     * @throws Exception
     */
    public function getOrderReview(Customer $customer, int $restaurantId, string $shippingMethod, string $paymentMethod): array
    {
        // Try multiple approaches to find the cart
        $cart = $this->findCustomerCart($customer, $restaurantId);

        // dd($customer->id);
        
        if (!$cart) {
            throw new Exception('No cart found for customer ID: ' . $customer->id . ' and restaurant ID: ' . $restaurantId);
        }

        if ($cart->items->isEmpty()) {
            throw new Exception('Cart is empty');
        }

        // Get addresses
        $billingAddress = $customer->addresses()
            ->where('address_type', 'billing')
            ->with(['city', 'state', 'country'])
            ->latest()
            ->first();
        // dd($billingAddress);

        $deliveryAddress = null;
        if ($shippingMethod === 'delivery') {
            $deliveryAddress = $customer->addresses()
                ->where('address_type', 'delivery')
                ->with(['city', 'state', 'country'])
                ->latest()
                ->first();
        }

        // Prepare cart items for calculation
        $items = $cart->items->map(function ($item) {
            return [
                'item_id' => $item->item_id, // Use item_id instead of food_item_id
                'quantity' => $item->quantity,
                'price' => $item->unit_price, // Use unit_price instead of price
                'total' => $item->total_price, // Use total_price instead of quantity * price
                'item' => $item->item // Use item instead of foodItem
            ];
        })->toArray();

        // Calculate totals
        $totals = $this->checkoutService->calculateTotals(
            $items,
            $deliveryAddress ? $deliveryAddress->toArray() : [],
            $billingAddress ? $billingAddress->toArray() : [],
            $customer,
            $cart->coupon_code ? [$cart->coupon_code] : []
        );

        return [
            'cart' => [
                'id' => $cart->id,
                'restaurant_id' => $cart->cartable_id, // Use cartable_id instead of restaurant_id
                'items' => $cart->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_id' => $item->item_id, // Use item_id instead of food_item_id
                        'quantity' => $item->quantity,
                        'price' => $item->unit_price, // Use unit_price instead of price
                        'total' => $item->total_price, // Use total_price instead of quantity * price
                        'item' => [
                            'id' => $item->item->id,
                            'name' => $item->item->name,
                            'description' => $item->item->description,
                            // 'image' => $item->item->image,
                        ]
                    ];
                }),
                'total_items' => $cart->items->count('id'),
                'coupon_code' => $cart->coupon_code,
            ],
            'addresses' => [
                'billing' => $billingAddress,
                'delivery' => $deliveryAddress,
            ],
            'shipping_method' => $shippingMethod,
            'payment_method' => $paymentMethod,
            'totals' => $totals,
            'estimated_delivery_time' => $this->calculateEstimatedDeliveryTime($shippingMethod),
        ];
    }

    /**
     * Calculate estimated delivery time
     *
     * @param string $shippingMethod
     * @return string
     */
    private function calculateEstimatedDeliveryTime(string $shippingMethod): string
    {
        $minutes = $shippingMethod === 'takeaway' ? 20 : 45;
        return now()->addMinutes($minutes)->toISOString();
    }

    /**
     * Find customer cart using multiple approaches
     *
     * @param Customer $customer
     * @param int $restaurantId
     * @return Cart|null
     */
    private function findCustomerCart(Customer $customer, int $restaurantId): ?Cart
    {
        // Approach 1: Try the getCart method (session-based)
        $cart = $customer->getCart($restaurantId);
        if ($cart) {
            return $cart;
        }

        // Approach 2: Try without session requirement - any cart for this customer and restaurant
        $cart = $customer->ownedCarts()
            ->forRestaurant($restaurantId)
            ->with(['items.item'])
            ->latest()
            ->first();
        if ($cart) {
            return $cart;
        }

        // Approach 3: Direct query with polymorphic relationship
        $cart = Cart::where('owner_type', Customer::class)
            ->where('owner_id', $customer->id)
            ->where('cartable_type', \Modules\Restaurant\Models\Restaurant::class)
            ->where('cartable_id', $restaurantId)
            ->with(['items.item'])
            ->latest()
            ->first();
        if ($cart) {
            return $cart;
        }

        // Approach 4: Check if there's any cart for this customer (for debugging)
        $anyCart = Cart::where('owner_type', Customer::class)
            ->where('owner_id', $customer->id)
            ->with(['items.item'])
            ->latest()
            ->first();
        
        if ($anyCart) {
            Log::info('Found cart for customer but different restaurant', [
                'customer_id' => $customer->id,
                'requested_restaurant_id' => $restaurantId,
                'found_cart_restaurant_id' => $anyCart->cartable_id,
                'found_cart_restaurant_type' => $anyCart->cartable_type
            ]);
        }

        return null;
    }
}