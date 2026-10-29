<?php

namespace Modules\Checkout\Services;

use Exception;
use Illuminate\Support\Facades\DB;
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
    public function placeOrder(Customer $customer, array $checkoutData): array
    {
        return DB::transaction(function () use ($customer, $checkoutData) {
            // Find cart
            $cart = $this->findCustomerCart($customer, $checkoutData['restaurant_id']);

            if (!$cart) {
                throw new Exception('No cart found for customer ID: ' . $customer->id . ' and restaurant ID: ' . $checkoutData['restaurant_id']);
            }

            if ($cart->items->isEmpty()) {
                throw new Exception('Cart is empty');
            }

            // Get addresses
            $billingAddress = $customer->addresses()
                ->where('address_type', 'billing')
                ->latest()
                ->first();

            $deliveryAddress = null;
            if ($checkoutData['shipping_method'] === 'delivery') {
                $deliveryAddress = $customer->addresses()
                    ->where('address_type', 'delivery')
                    ->latest()
                    ->first();

                if (!$deliveryAddress) {
                    throw new Exception('Delivery address is required for delivery');
                }
            }

            // Check if payment method is COD - FIXED QUERY
            $paymentConfig = \Modules\Payments\Models\PaymentConfiguration::where('payment_method_id', $checkoutData['payment_method'])
                ->where('is_active', true)
                ->first();
            \Log::info($paymentConfig);
            $isCod = false;
            if ($paymentConfig && $paymentConfig->method) {
                \Log::info('true');
                $methodName = strtolower($paymentConfig->method->name ?? '');
                $isCod = str_contains($methodName, 'cash') || str_contains($methodName, 'cod') || str_contains($methodName, 'delivery');
            \Log::info($isCod);
                }

            // Prepare order data
            $orderData = [
                'customer_id'          => $customer->id,
                'restaurant_id'        => $checkoutData['restaurant_id'],
                'order_type'           => $checkoutData['shipping_method'],
                'payment_method'       => $checkoutData['payment_method'],
                'billing_address'      => $billingAddress ? $billingAddress->toArray() : null,
                'delivery_address'     => $deliveryAddress ? $deliveryAddress->toArray() : null,
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
            $result = $this->orderService->createOrder($orderData);
            $order  = $result['order'];

            // Cart delete karo
            $cart->items()->delete();
            $cart->delete();

            // Get payment method name
            $paymentMethodName = 'Unknown';
            if ($order->payment_method && $paymentConfig) {
                $paymentMethodName = $paymentConfig->merchant_name ?? 'Unknown';
            }

            return [
                'order_id'                => $order->id,
                'order_number'            => $order->order_number,
                'status'                  => $order->status,
                'payment_status'          => $order->payment_status,
                'total'                   => $order->total,
                'currency'                => fn_format_default_currency(),
                'estimated_delivery_time' => $order->estimated_delivery_time,
                'order_type'              => $order->order_type,
                'payment_method'          => $paymentMethodName,
                'payment_method_id'       => $order->payment_method,
                'client_secret'           => $result['client_secret'] ?? null, // Can be null for COD
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

            // Check if payment method is COD - FIXED QUERY
            $paymentConfig = \Modules\Payments\Models\PaymentConfiguration::where('payment_method_id', $checkoutData['payment_method'])
                ->where('is_active', true)
                ->first();
                
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
            if ($order->payment_method && $paymentConfig) {
                $paymentMethodName = $paymentConfig->merchant_name ?? 'Unknown';
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
