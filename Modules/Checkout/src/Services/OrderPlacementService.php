<?php

namespace Modules\Checkout\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Cart\Models\Cart;
use Modules\Orders\Services\OrderService;
use Modules\Acl\Models\Customer;

class OrderPlacementService
{
    protected $orderService;
    protected $checkoutService;

    public function __construct(OrderService $orderService, CheckoutService $checkoutService)
    {
        $this->orderService = $orderService;
        $this->checkoutService = $checkoutService;
    }

    /**
     * Place order from checkout data
     *
     * @param Customer $customer
     * @param array $checkoutData
     * @return array
     * @throws Exception
     */
    // public function placeOrder(Customer $customer, array $checkoutData): array
    // {
    //     return DB::transaction(function () use ($customer, $checkoutData) {
    //         // Try multiple approaches to find the cart
    //         $cart = $this->findCustomerCart($customer, $checkoutData['restaurant_id']);



    //         if (!$cart) {
    //             throw new Exception('No cart found for customer ID: ' . $customer->id . ' and restaurant ID: ' . $checkoutData['restaurant_id']);
    //         }

    //         if ($cart->items->isEmpty()) {
    //             throw new Exception('Cart is empty');
    //         }

    //         // Get addresses
    //         $billingAddress = $customer->addresses()
    //             ->where('address_type', 'billing')
    //             ->latest()
    //             ->first();

    //         // dd($billingAddress);

    //         $deliveryAddress = null;
    //         if ($checkoutData['shipping_method'] === 'delivery') {
    //             $deliveryAddress = $customer->addresses()
    //                 ->where('address_type', 'delivery')
    //                 ->latest()
    //                 ->first();

    //             // dd($deliveryAddress);                 
    //             if (!$deliveryAddress) {
    //                 throw new Exception('Delivery address is required for delivery');
    //             }
    //         }

    //         // Prepare order data
    //         $orderData = [
    //             'customer_id' => $customer->id,
    //             'restaurant_id' => $checkoutData['restaurant_id'],
    //             'order_type' => $checkoutData['shipping_method'], // 'takeaway' or 'delivery'
    //             'payment_method' => $checkoutData['payment_method'],
    //             'billing_address' => $billingAddress ? $billingAddress->toArray() : null,
    //             'delivery_address' => $deliveryAddress ? $deliveryAddress->toArray() : null,
    //             'order_items' => $cart->items->map(function ($item) {
    //                 return [
    //                     'item_id' => $item->item_id, // Use polymorphic item_id
    //                     'item_type' => $item->item_type, // Use polymorphic item_type
    //                     'quantity' => $item->quantity,
    //                     'price' => $item->unit_price,
    //                     'total' => $item->total_price,
    //                     'selected_options' => $item->selected_options,
    //                     'special_instructions' => $item->special_instructions,
    //                 ];
    //             })->toArray(),
    //             'coupon_code' => $cart->coupon_code, // Transfer coupon from cart
    //             'special_instructions' => $checkoutData['special_instructions'] ?? null,
    //             // 'requires_payment' => in_array($checkoutData['payment_method'], ['card', 'digital_wallet']),
    //         ];

    //         // Create order
    //         $order = $this->orderService->createOrder($orderData);

    //         // Clear cart after successful order
    //         $cart->items()->delete();
    //         $cart->delete();

    //         // Get payment method name from PaymentConfiguration
    //         $paymentMethodName = 'Unknown';
    //         if ($order->payment_method) {
    //             // dd($order->payment_method);
    //             // $paymentConfig = \Modules\Payments\Models\PaymentConfiguration::find($order->payment_method);
    //             $paymentConfig = \Modules\Payments\Models\PaymentConfiguration::where('payment_method_id', $order->payment_method)
    //                 ->where('is_active', true)
    //                 ->first();
    //             // dd($paymentConfig);
    //             $paymentMethodName = $paymentConfig ? $paymentConfig->merchant_name : 'Unknown';
    //         }

    //         return [
    //             'order_id' => $order->id,
    //             'order_number' => $order->order_number,
    //             'status' => $order->status,
    //             'payment_status' => $order->payment_status,
    //             'total' => $order->total,
    //             'currency' => fn_format_default_currency(),
    //             'estimated_delivery_time' => $order->estimated_delivery_time,
    //             'order_type' => $order->order_type,
    //             'payment_method' => $paymentMethodName,
    //             'payment_method_id' => $order->payment_method,
    //             'client_secret'   => $order['client_secret'],
    //         ];
    //     });
    // }


    public function placeOrder(Customer $customer, array $checkoutData): array
    {
     
        return DB::transaction(function () use ($customer, $checkoutData) {
            $customer = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            // Find cart
            $cart = $this->findCustomerCart($customer, $checkoutData['restaurant_id']);

            if (!$cart) {
                throw \Illuminate\Validation\ValidationException::withMessages(['cart' => 'Your cart is empty. Please add items before placing an order.']);
            }

            if ($cart->items->isEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['cart' => 'Your cart is empty. Please add items before placing an order.']);
            }

            // Price from the current address and trusted cart subtotal immediately before ordering.
            $cart->updateTotals();
            if ($checkoutData['shipping_method'] === 'delivery' && !$cart->delivery_available) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'delivery_address' => 'Delivery is unavailable: '.($cart->delivery_unavailable_reason ?? 'address required'),
                ]);
            }

            // Get addresses
            $billingAddress = $customer->addresses()
                ->where('address_type', 'billing')
                ->latest()
                ->first();

            $deliveryAddress = null;
            if ($checkoutData['shipping_method'] === 'delivery') {
                $deliveryAddress = $customer->addresses()
                    ->whereKey($cart->delivery_address_id)
                    ->first();

                if (!$deliveryAddress) {
                    throw new Exception('Delivery address is required for delivery');
                }
            }

            // Check if payment method is COD
            $paymentConfig = \Modules\Payments\Models\PaymentConfiguration::whereKey($checkoutData['payment_method'])
                ->where('is_active', true)
                ->first();
            if (!$paymentConfig || !$paymentConfig->method) {
                throw \Illuminate\Validation\ValidationException::withMessages(['payment_method' => 'Please select an active payment method.']);
            }
            $isCod = false;
            if ($paymentConfig && $paymentConfig->method) {
                $methodCode = strtolower($paymentConfig->method->code ?? '');
                $methodName = strtolower($paymentConfig->method->name ?? '');
                $isCod = in_array($methodCode, ['cod', 'cash', 'cash_on_delivery'], true) || str_contains($methodName, 'cash on delivery');
            }


            // Prepare order data
            $orderData = [
                'customer_id'          => $customer->id,
                'restaurant_id'        => $checkoutData['restaurant_id'],
                'order_type'           => $checkoutData['shipping_method'],
                'delivery_fee'         => $checkoutData['shipping_method'] === 'delivery' ? (float) $cart->delivery_fee : 0,
                'estimated_delivery_time' => $cart->estimated_delivery_at,
                'preferred_delivery_date' => $customer->preferred_delivery_date,
                'payment_method'       => $checkoutData['payment_method'],
                'billing_address'      => $billingAddress ? $billingAddress->toArray() : null,
                'delivery_address'     => $deliveryAddress ? array_merge($deliveryAddress->toArray(), [
                    'city' => $deliveryAddress->city?->name ?? $deliveryAddress->city_name,
                    'state' => $deliveryAddress->state?->name, 'country' => $deliveryAddress->country?->name,
                ]) : null,
                'order_items'          => $cart->items->map(function ($item) {
                    return [
                        'item_id'              => $item->item_id,
                        'item_type'            => $item->item_type,
                        'quantity'             => $item->quantity,
                        'price'                => $item->unit_price,
                        'total'                => $item->total_price,
                        'selected_options'     => $item->selected_options,
                        'special_instructions' => $item->special_instructions,
                    ];
                })->toArray(),
                'coupon_code'          => $cart->coupon_code,
                'special_instructions' => $checkoutData['special_instructions'] ?? null,
                'requires_payment'     => !$isCod, // COD doesn't require payment processing
            ];
           

            // Create order — ab array return hoga
            $result = $this->orderService->createOrder($orderData, $cart);
            $order  = $result['order'];

            // Cart delete karo
            $cart->items()->delete();
            $cart->delete();

            // Get payment method name
            $paymentMethodName = 'Unknown';
            if ($order->payment_method) {
            $paymentConfig = \Modules\Payments\Models\PaymentConfiguration::whereKey($order->payment_method)
                    ->where('is_active', true)
                    ->first();
                $paymentMethodName = $paymentConfig?->merchant_name ?? 'Unknown';
            }
               Log::info('Placing order for customer ID: ' . $customer->id, ['checkoutData' => $checkoutData]);

            return [
                'order_id'               => $order->id,
                'order_number'           => $order->order_number,
                'status'                 => $order->status,
                'payment_status'         => $order->payment_status,
                'total'                  => $order->total,
                'currency'               => fn_format_default_currency(),
                'estimated_delivery_time' => $order->estimated_delivery_time,
                'preferred_delivery_date' => $order->preferred_delivery_date,
                'order_type'             => $order->order_type,
                'payment_method'         => $paymentMethodName,
                'payment_method_id'      => $order->payment_method,
                'client_secret'          => $result['client_secret'],
                'payment_url'            => $result['payment_url'] ?? null,
                'event_guest_count' => $order->event_guest_count,
                'delivery_fee' => $order->delivery_fee,
                'delivery_distance_miles' => $order->delivery_distance_miles,
                'estimated_delivery_minutes' => $order->estimated_delivery_minutes,
                'is_free_delivery' => $order->order_type === 'delivery' && (float) $order->delivery_fee === 0.0,
                'delivery_policy_snapshot' => $order->delivery_policy_snapshot,
                'pricing' => app(\Modules\Orders\Services\OrderPresentationService::class)->pricing($order),
                'items' => $order->items->map(fn ($item) => app(\Modules\Orders\Services\OrderPresentationService::class)->item($item))->all(),
            ];
        });
    }


    /**
     * Place guest order from cart
     *
     * @param Cart $cart
     * @param array $checkoutData
     * @return array
     * @throws Exception
     */
    public function placeGuestOrder(Cart $cart, array $checkoutData): array
    {
        return DB::transaction(function () use ($cart, $checkoutData) {
            if ($cart->items->isEmpty()) {
                throw new Exception('Cart is empty');
            }

            // Build delivery address from guest details
            $deliveryAddress = null;
            if ($checkoutData['shipping_method'] === 'delivery') {
                $deliveryAddress = $checkoutData['guest_details'] ?? [];
            }

            // Check if payment method is COD
            $paymentConfig = \Modules\Payments\Models\PaymentConfiguration::find($checkoutData['payment_method']);
            $isCod = false;
            if ($paymentConfig && $paymentConfig->method) {
                $methodName = strtolower($paymentConfig->method->name ?? '');
                $isCod = str_contains($methodName, 'cash') || str_contains($methodName, 'cod') || str_contains($methodName, 'delivery');
            }

            // Prepare order data for guest
            $orderData = [
                'customer_id' => null,  // No customer for guest
                'guest_phone' => $checkoutData['guest_phone'],
                'restaurant_id' => $checkoutData['restaurant_id'],
                'order_type' => $checkoutData['shipping_method'], // 'takeaway' or 'delivery'
                'payment_method' => $checkoutData['payment_method'],
                'delivery_address' => $deliveryAddress,
                'order_items' => $cart->items->map(function ($item) {
                    return [
                        'item_id' => $item->item_id,
                        'item_type' => $item->item_type,
                        'quantity' => $item->quantity,
                        'price' => $item->unit_price,
                        'total' => $item->total_price,
                        'selected_options' => $item->selected_options,
                        'special_instructions' => $item->special_instructions,
                    ];
                })->toArray(),
                'coupon_code' => $cart->coupon_code,
                'requires_payment' => !$isCod, // COD doesn't require payment processing
            ];

            // Create guest order
            $order = $this->orderService->createGuestOrder($orderData);

            // Clear cart after successful order
            $cart->items()->delete();
            $cart->delete();

            // Get payment method name from PaymentConfiguration
            $paymentMethodName = 'Unknown';
            if ($order->payment_method) {
                $paymentConfig = \Modules\Payments\Models\PaymentConfiguration::find($order->payment_method);
                $paymentMethodName = $paymentConfig ? $paymentConfig->merchant_name : 'Unknown';
            }

            return [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'total' => $order->total,
                'currency' => $order->currency ?? 'USD',
                'estimated_delivery_time' => $order->estimated_delivery_time,
                'order_type' => $order->order_type,
                'payment_method' => $paymentMethodName,
                'payment_method_id' => $order->payment_method,
            ];
        });
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

        return null;
    }
}
