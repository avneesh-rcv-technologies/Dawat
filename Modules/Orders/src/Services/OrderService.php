<?php

namespace Modules\Orders\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Orders\Models\Order;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Restaurant\Models\Restaurant;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Modules\Orders\Models\OrderItem;
use Modules\Tax\Services\FoodItemTaxService;
use Modules\Orders\Enums\OrderStatus;
use Modules\Payments\Models\Payments;
use Modules\Orders\Enums\TransactionStatus;
use Modules\Payments\Services\PaymentsService;
use Modules\Discounts\Services\DiscountService;
use Modules\Orders\Services\TransactionService;
use Modules\Notifications\Services\NotificationService;
use Modules\Payments\Services\PaymentService;

class OrderService
{
    protected const TERMINAL_STATUS_CODES = ['D', 'C', 'F', 'R'];

    public function __construct(
        protected PaymentsService $paymentService,
        protected TransactionService $transactionService,
        protected FoodItemTaxService $taxService,
        protected DiscountService $discountService,
        protected NotificationService $notificationService,
        protected PaymentService $paymentRecordService
    ) {}

    public function getOrderStatusWorkflow(string $orderType = Order::TYPE_DELIVERY): array
    {
        return array_map(fn (OrderStatus $status) => $status->code(), OrderStatus::workflow($orderType));
    }

    public function getAllowedStatusesForOrder(?string $currentStatus, string $orderType = Order::TYPE_DELIVERY): array
    {
        $currentStatus = $currentStatus ?: Order::STATUS_PENDING;
        $transitions = OrderStatus::fromCode($currentStatus)?->allowedTransitions($orderType) ?? [];

        return [$currentStatus, ...array_map(fn (OrderStatus $status) => $status->code(), $transitions)];
    }

    protected function validateOrderStatusTransition(string $currentStatus, string $newStatus, string $orderType = Order::TYPE_DELIVERY): void
    {
        if ($currentStatus === $newStatus) {
            return;
        }

        $allowedStatuses = $this->getAllowedStatusesForOrder($currentStatus, $orderType);

        if (!in_array($newStatus, $allowedStatuses, true)) {
            $currentStatusName = fn_get_order_status_name($currentStatus, 'O') ?? $currentStatus;
            $newStatusName = fn_get_order_status_name($newStatus, 'O') ?? $newStatus;

            throw \Illuminate\Validation\ValidationException::withMessages([
                'status' => "You cannot change status from {$currentStatusName} to {$newStatusName}.",
            ]);
        }
    }


    public function createOrder(array $orderData): array
    {
        return DB::transaction(function () use ($orderData) {
            // Validate customer and restaurant
            $customer   = $this->validateCustomer($orderData['customer_id']);
            $restaurant = $this->validateRestaurant($orderData['restaurant_id']);

            // Validate and prepare order data
            $preparedData = $this->prepareOrderData($orderData, $customer, $restaurant);

            // Create the order
            $order = Order::create($preparedData['order']);

            // Process order items
            $this->processOrderItems($order, $preparedData['items']);

            // Process payment if required
            $clientSecret = null;
            if ($orderData['requires_payment'] ?? true) {
                $clientSecret = $this->processPayment($order, $orderData);
            }

            // Send notifications

            // $this->notificationService->trigger(
            //     'Orders',
            //     'OrderCreated',
            //     $customer->email,
            //     $order
            // );
            if (!($orderData['requires_payment'] ?? true)) {

                $this->notificationService->trigger(
                    'Orders',
                    'OrderCreated',
                    $customer->email,
                    $order
                );
            }



            return [
                'order'         => $order->load('items', 'customer', 'restaurant'),
                'client_secret' => $clientSecret,
            ];
        });
    }



    /**
     * Update order status with food ordering workflow
     */
    public function updateOrderStatus(Order $order, string $status): Order
    {
        return DB::transaction(function () use ($order, $status) {
            $order = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $previousStatus = $order->status;

            // Validate that the status exists
            $statusExists = fn_get_order_status($status, 'O');
            if (!$statusExists) {
                throw new \Exception("Invalid status: {$status}");
            }

            $this->validateOrderStatusTransition($previousStatus, $status, $order->order_type ?? Order::TYPE_DELIVERY);

            if ($previousStatus === $status) {
                return $order;
            }

            // Update the order status
            $order->update(['status' => $status]);

            // Update delivery times based on status
            if ($status === "D" && !$order->actual_delivery_time) {
                $order->update(['actual_delivery_time' => now()]);
            }

            if ($status == "D" && $order->payment_method == "1" && $order->payment_status == 'pending') {
                $order->update(['payment_status' => 'paid']);
            }


            \Log::info('order status updated this is working');

            // Trigger status change event only if customer exists
            if ($order->customer && $order->customer->email) {
                $this->notificationService->trigger(
                    'Orders',
                    'OrderStatusUpdate',
                    $order->customer->email,
                    [
                        'order' => $order,
                        'old_status' => $previousStatus,
                    ]
                );
            }

            return $order->refresh();
        });
    }

    /**
     * Cancel order with restaurant policy validation
     */
    public function cancelOrder(Order $order, ?string $reason = null, bool $bypassPolicyCheck = false): Order
    {
        return DB::transaction(function () use ($order, $reason, $bypassPolicyCheck) {
            // Serialize cancellations so stock can only be returned once.
            $order = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            // Load restaurant relationship
            $order->load('vendor');

            if (in_array($order->status, self::TERMINAL_STATUS_CODES, true)) {
                throw new \Exception('Order is already in a terminal state and cannot be cancelled again');
            }

            // Check restaurant cancellation policy (unless bypassed by admin)
            if (!$bypassPolicyCheck) {
                $cancellationCheck = $order->isCancellableByCustomer();

                if (!$cancellationCheck['can_cancel']) {
                    throw new \Exception($cancellationCheck['reason']);
                }
            }


         
            // Fallback only when the restaurant does not define a cancellation policy.
            $restaurant = $order->vendor;
            $hasRestaurantPolicy = $restaurant && method_exists($restaurant, 'allowsOrderCancellation');

            if ($hasRestaurantPolicy && method_exists($restaurant, 'getCancellableStatuses')) {
                $allowedStatuses = $restaurant->getCancellableStatuses();
                $canCancelByPolicy = $restaurant->allowsOrderCancellation() && in_array($order->status, $allowedStatuses, true);

                if (!$canCancelByPolicy) {
                    throw new \Exception('Order cannot be cancelled at this stage');
                }
            } elseif (!$order->canBeCancelled()) {
                throw new \Exception('Order cannot be cancelled at this stage');
            }

            // Update order status
            $order = $this->updateOrderStatus($order, fn_get_order_status_by_name('CANCELLED'));

            // Store cancellation details
            $order->update([
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
                'notes' => ($order->notes ? $order->notes . "\n\n" : '') .
                    "Cancelled: " . $reason
            ]);

            // Record transaction
            $this->transactionService->record(
                order: $order,
                type: 'order_cancelled',
                amount: $order->total,
                status: 'cancelled',
                notes: $reason ?? 'Order cancelled',
                metadata: [
                    'cancelled_by' => $bypassPolicyCheck ? 'admin' : 'customer',
                    'cancellation_reason' => $reason
                ]
            );

            return $order;
        });
    }

    protected function validateCustomer(int $customerId): \Modules\Acl\Models\Customer
    {
        return \Modules\Acl\Models\Customer::findOrFail($customerId);
    }

    protected function validateRestaurant(int $restaurantId): Restaurant
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        if (!$restaurant->isActive()) {
            throw new \Exception('Restaurant is not active');
        }

        if (!$restaurant->is_accepting_orders) {
            throw new \Exception('Restaurant is not accepting orders at this time');
        }

        return $restaurant;
    }

    // protected function prepareOrderData(array $orderData, \Modules\Acl\Models\Customer $customer, Restaurant $restaurant): array
    // {
    //     $subtotal = $this->calculateSubtotal($orderData['order_items']);
    //     $discountAmount = $this->calculateDiscounts($orderData, $orderData['coupon_code'] ?? '');

    //     // Calculate delivery fee based on order type and restaurant settings
    //     $deliveryFee = $this->calculateDeliveryFee($orderData, $restaurant);
    //     $serviceCharge = $this->calculateServiceCharge($orderData, $restaurant);

    //     foreach ($orderData['order_items'] as &$item) {
    //         $price = (float) ($item['price'] ?? 0);
    //         $quantity = (int) ($item['quantity'] ?? 0);
    //         $optionsPrice = $this->calculateItemOptionsPrice($item['selected_options'] ?? []);

    //         $lineTotal = ($price + $optionsPrice) * $quantity;
    //         $item['line_total'] = $lineTotal;
    //         $item['total_price'] = $lineTotal;

    //         $proportion = $subtotal > 0 ? ($item['line_total'] / $subtotal) : 0;
    //         $item['discounted_line_total'] = $item['line_total'] - ($discountAmount * $proportion);
    //     }

    //     $taxCalculation = $this->calculateTaxForOrder($orderData, $customer);
    //     $tax = $taxCalculation['tax_amount'];
    //     $isInclusive = $taxCalculation['is_inclusive'];

    //     // For inclusive tax, don't add tax to total since it's already included in item prices
    //     // For exclusive tax, add tax to total
    //     if ($isInclusive) {
    //         $total = $subtotal - $discountAmount + $deliveryFee + $serviceCharge;
    //     } else {
    //         $total = $subtotal - $discountAmount + $tax + $deliveryFee + $serviceCharge;
    //     }

    //     return [
    //         'order' => [
    //             // Use polymorphic relationship for Customer model
    //             'customer_type' => \Modules\Acl\Models\Customer::class,
    //             'customer_id' => $customer->id,
    //             // Use polymorphic relationship for vendor (restaurant)
    //             'vendor_type' => \Modules\Restaurant\Models\Restaurant::class,
    //             'vendor_id' => $restaurant->id,
    //             'admin_id' => auth('admin')->id(),
    //             'order_number' => $this->generateOrderNumber(),
    //             'status' => $orderData['status'] ?? 'W', // Use full status name
    //             'order_type' => $orderData['order_type'] ?? Order::TYPE_DELIVERY,
    //             'subtotal' => $subtotal,
    //             'discount' => $discountAmount,
    //             'tax' => $tax,
    //             'delivery_fee' => $deliveryFee,
    //             'service_charge' => $serviceCharge,
    //             'total' => $orderData['custom_total'] ?? $total,
    //             'delivery_address' => $orderData['delivery_address'] ?? $orderData['shipping_address'] ?? null,
    //             'billing_address' => $orderData['billing_address'] ?? null,
    //             'estimated_delivery_time' => $this->calculateEstimatedDeliveryTime($orderData, $restaurant),
    //             'delivery_instructions' => $orderData['delivery_instructions'] ?? null,
    //             'phone_number' => $orderData['phone_number'] ?? $customer->phone,
    //             'payment_method' => $orderData['payment_method'] ?? 'cash_on_delivery',
    //             'payment_status' => $orderData['payment_status'] ?? Order::PAYMENT_PENDING,
    //             'notes' => $orderData['notes'] ?? null,
    //             'ip_address' => request()->ip(),
    //             'user_agent' => request()->userAgent(),
    //             'currency' => $orderData['currency'] ?? fn_get_setting('general.currency') ?? 'USD',
    //             'coupon_code' => $orderData['coupon_code'] ?? null,
    //         ],
    //         'items' => $orderData['order_items']
    //     ];
    // }


    protected function prepareOrderData(array $orderData, \Modules\Acl\Models\Customer $customer, Restaurant $restaurant): array
    {
        $subtotal = $this->calculateSubtotal($orderData['order_items']);
        $discountAmount = $this->calculateDiscounts($orderData, $orderData['coupon_code'] ?? '');

        // Calculate delivery fee based on order type and restaurant settings
        $deliveryFee = $this->calculateDeliveryFee($orderData, $restaurant);
        $serviceCharge = $this->calculateServiceCharge($orderData, $restaurant);

        foreach ($orderData['order_items'] as &$item) {
            $price = (float) ($item['price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            $optionsPrice = $this->calculateItemOptionsPrice($item['selected_options'] ?? []);

            $lineTotal = ($price + $optionsPrice) * $quantity;
            $item['line_total'] = $lineTotal;
            $item['total_price'] = $lineTotal;

            $proportion = $subtotal > 0 ? ($item['line_total'] / $subtotal) : 0;
            $item['discounted_line_total'] = $item['line_total'] - ($discountAmount * $proportion);
        }

        $taxCalculation = $this->calculateTaxForOrder($orderData, $customer);
        $tax = $taxCalculation['tax_amount'];
        $isInclusive = $taxCalculation['is_inclusive'];

        // For inclusive tax, don't add tax to total since it's already included in item prices
        // For exclusive tax, add tax to total
        if ($isInclusive) {
            $total = $subtotal - $discountAmount + $deliveryFee + $serviceCharge;
        } else {
            $total = $subtotal - $discountAmount + $tax + $deliveryFee + $serviceCharge;
        }

        return [
            'order' => [
                // Use polymorphic relationship for Customer model
                'customer_type' => \Modules\Acl\Models\Customer::class,
                'customer_id' => $customer->id,
                // Use polymorphic relationship for vendor (restaurant)
                'vendor_type' => \Modules\Restaurant\Models\Restaurant::class,
                'vendor_id' => $restaurant->id,
                'admin_id' => auth('admin')->id(),
                'order_number' => $this->generateOrderNumber(),
                'status' => $orderData['status'] ?? 'W', // Use full status name
                'order_type' => $orderData['order_type'] ?? Order::TYPE_DELIVERY,
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'tax' => $tax,
                'delivery_fee' => $deliveryFee,
                'service_charge' => $serviceCharge,
                'total' => $orderData['custom_total'] ?? $total,
                'delivery_address' => $orderData['delivery_address'] ?? $orderData['shipping_address'] ?? null,
                'billing_address' => $orderData['billing_address'] ?? null,
                'estimated_delivery_time' => $this->calculateEstimatedDeliveryTime($orderData, $restaurant),
                'delivery_instructions' => $orderData['delivery_instructions'] ?? null,
                'phone_number' => $orderData['phone_number'] ?? $customer->phone,
                'payment_method' => $orderData['payment_method'] ?? 'cash_on_delivery',
                'payment_status' => $orderData['payment_status'] ?? Order::PAYMENT_PENDING,
                'notes' => $orderData['notes'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'currency' => $orderData['currency'] ?? fn_get_setting('general.currency') ?? 'USD',
                'coupon_code' => $orderData['coupon_code'] ?? null,
            ],
            'items' => $orderData['order_items']
        ];
    }

    // protected function processOrderItems(Order $order, array $items): void
    // {
    //     foreach ($items as $item) {
    //         // Support both old food_item_id and new item_id/item_type format
    //         $itemId = $item['item_id'] ?? $item['food_item_id'] ?? null;
    //         $itemType = $item['item_type'] ?? 'Modules\FoodManagements\Models\FoodItem';

    //         if (!$itemId) {
    //             throw new \Exception("Item ID is required");
    //         }

    //         $foodItem = $itemType::findOrFail($itemId);

    //         // Validate food item availability
    //         if (!$foodItem->is_available || !$foodItem->is_active) {
    //             throw new \Exception("Food item '{$foodItem->name}' is not available");
    //         }

    //         // dd($foodItem->in_stock, $foodItem->quantity, $item['quantity']);
    //         // Check stock if managed
    //         // if ($foodItem->in_stock && $foodItem->quantity < $item['quantity']) {
    //         //     throw new \Exception("Insufficient stock for '{$foodItem->name}'");
    //         // }

    //         // Calculate total price including options
    //         $basePrice = $item['price'];
    //         $optionsPrice = $this->calculateItemOptionsPrice($item['selected_options'] ?? []);
    //         $totalPrice = ($basePrice + $optionsPrice) * $item['quantity'];

    //         // Create order item with polymorphic relationship
    //         $order->items()->create([
    //             'item_type' => $itemType,
    //             'item_id' => $itemId,
    //             'item_name' => $foodItem->name,
    //             'sku' => $foodItem->sku ?? null,
    //             'price' => $basePrice,
    //             'quantity' => $item['quantity'],
    //             'tax_rate' => $foodItem->tax ?? 0,
    //             'selected_options' => $item['selected_options'] ?? [],
    //             'special_instructions' => $item['special_instructions'] ?? null,
    //             'total_price' => $totalPrice,
    //         ]);

    //         // Update stock if managed
    //         if ($foodItem->in_stock) {
    //             $foodItem->decrement('quantity', $item['quantity']);
    //         }
    //     }
    // }
    protected function processOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            // Support both old food_item_id and new item_id/item_type format
            $itemId = $item['item_id'] ?? $item['food_item_id'] ?? null;
            $itemType = $item['item_type'] ?? 'Modules\FoodManagements\Models\FoodItem';

            if (!$itemId) {
                throw new \Exception("Item ID is required");
            }

            // Lock the inventory row for the duration of this order transaction.
            $foodItem = $itemType::whereKey($itemId)->lockForUpdate()->firstOrFail();

            // Validate food item availability
            if (!$foodItem->is_available || !$foodItem->is_active) {
                throw new \Exception("Food item '{$foodItem->name}' is not available");
            }

            $quantity = (int) ($item['quantity'] ?? 0);
            $tracksQuantity = method_exists($foodItem, 'isGroupingQuantityType')
                ? !$foodItem->isGroupingQuantityType()
                : true;
            $reservesStock = $tracksQuantity && $foodItem->in_stock && $foodItem->quantity !== null;

            if ($quantity < 1) {
                throw new \Exception("Invalid quantity for '{$foodItem->name}'");
            }

            if ($tracksQuantity && $foodItem->in_stock && $foodItem->quantity !== null && $foodItem->quantity < $quantity) {
                Log::warning('Order rejected because inventory is insufficient', [
                    'order_id' => $order->id,
                    'item_id' => $foodItem->getKey(),
                    'item_type' => get_class($foodItem),
                    'item_name' => $foodItem->name,
                    'quantity_type' => $foodItem->quantity_type ?? null,
                    'requested_quantity' => $quantity,
                    'available_quantity' => (int) $foodItem->quantity,
                ]);
                throw new \Exception(
                    "Insufficient stock for '{$foodItem->name}': requested {$quantity}, available {$foodItem->quantity}"
                );
            }

            // Calculate total price including options
            $basePrice = $item['price'];
            $optionsPrice = $this->calculateItemOptionsPrice($item['selected_options'] ?? []);
            $totalPrice = ($basePrice + $optionsPrice) * $quantity;

            // Create order item with polymorphic relationship
            $order->items()->create([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'item_name' => $foodItem->name,
                'sku' => $foodItem->sku ?? null,
                'price' => $basePrice,
                'quantity' => $quantity,
                'stock_reserved_quantity' => $reservesStock ? $quantity : 0,
                'tax_rate' => $foodItem->tax ?? 0,
                'selected_options' => $item['selected_options'] ?? [],
                'special_instructions' => $item['special_instructions'] ?? null,
                'total_price' => $totalPrice,
            ]);

            // Update stock if managed
            if ($reservesStock) {
                $quantityBefore = (int) $foodItem->quantity;
                $foodItem->decrement('quantity', $quantity);
                $quantityAfter = (int) $foodItem->fresh()->quantity;

                Log::info('Order inventory quantity decremented', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'item_id' => $foodItem->getKey(),
                    'item_type' => get_class($foodItem),
                    'item_name' => $foodItem->name,
                    'quantity_type' => $foodItem->quantity_type ?? null,
                    'decremented_by' => $quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'decrement_count_for_order_item' => 1,
                ]);
            }
        }
    }


    protected function calculateItemOptionsPrice(array $selectedOptions): float
    {
        $total = 0;
        foreach ($selectedOptions as $option) {
            $total += $option['price'] ?? 0;
        }
        return $total;
    }

    protected function calculateDeliveryFee(array $orderData, Restaurant $restaurant): float
    {
        $orderType = $orderData['order_type'] ?? Order::TYPE_DELIVERY;

        if ($orderType !== Order::TYPE_DELIVERY) {
            return 0;
        }

        // You can implement distance-based delivery fee calculation here
        return $orderData['delivery_fee'] ?? fn_get_setting('general.default_delivery_fee') ?? 0;
    }

    protected function calculateServiceCharge(array $orderData, Restaurant $restaurant): float
    {
        return $orderData['service_charge'] ?? fn_get_setting('general.default_service_charge') ?? 0;
    }

    protected function calculateEstimatedDeliveryTime(array $orderData, Restaurant $restaurant): \Carbon\Carbon
    {
        $orderType = $orderData['order_type'] ?? Order::TYPE_DELIVERY;
        $preparationTime = $restaurant->order_time_limit ?? 30; // minutes

        $additionalTime = match ($orderType) {
            Order::TYPE_DELIVERY => 20, // delivery time
            Order::TYPE_TAKEAWAY => 0,
            Order::TYPE_DINE_IN => 0,
            default => 0
        };

        return now()->addMinutes($preparationTime + $additionalTime);
    }

    // protected function processPayment(Order $order, array $orderData): void
    // {
    //     $this->paymentService->charge(
    //         $orderData['payment_method'],
    //         $order
    //     );
    // }

    // protected function processPayment(Order $order, array $orderData): string
    // {

    //     $response = $this->paymentService->charge(
    //         $orderData['payment_method'],
    //         $order
    //     );

    //     $sessionData = [
    //         'order_id' => $order->id,
    //         'total' => $order->total,
    //         'subtotal' => $order->subtotal,
    //         'billing_address' => $order->billing_address,
    //         'shipping_address' => $order->delivery_address,
    //         'order_note' => $order->special_instructions,
    //         'order_items' => $order->items->toArray(),
    //         'coupon_code' => $order->coupon_code ?? '',
    //         'discount' => $order->discount ?? 0,
    //         'tax' => $order->tax ?? 0,
    //         'customer_details' => [
    //             'id' => $order->customer_id,
    //         ],
    //     ];


    //     $this->paymentRecordService->handlePaymentResponse(
    //         $response,
    //         $orderData['payment_method'],
    //         $sessionData
    //     );


    //     return $response->client_secret;
    // }


    protected function processPayment(Order $order, array $orderData): string
    {

    
        $response = $this->paymentService->charge(
            $orderData['payment_method'],
            $order
        );

        $sessionData = [
            'order_id' => $order->id,
            'total' => $order->total,
            'subtotal' => $order->subtotal,
            'billing_address' => $order->billing_address,
            'shipping_address' => $order->delivery_address,
            'order_note' => $order->special_instructions,
            'order_items' => $order->items->toArray(),
            'coupon_code' => $order->coupon_code ?? '',
            'discount' => $order->discount ?? 0,
            'tax' => $order->tax ?? 0,
            'customer_details' => [
                'id' => $order->customer_id,
            ],
        ];


        $this->paymentRecordService->handlePaymentResponse(
            $response,
            $orderData['payment_method'],
            $sessionData
        );


        return $response->client_secret;
    }


    /**
     * Check if order can be cancelled (for customer)
     */
    public function checkCancellationEligibility(Order $order): array
    {
        return $order->isCancellableByCustomer();
    }

    protected function generateOrderNumber(): string
    {
        $prefix = fn_get_setting('general.order.prefix') ?? 'ORD';
        $suffix = fn_get_setting('general.order.suffix') ?? '';
        $length = fn_get_setting('general.order.length') ?? 6;
        $auto_generate = fn_get_setting('general.order.auto_generate') ?? 'Y';

        $date = '';
        if ($auto_generate === 'Y') {
            $date = date('Ymd');
        }

        do {
            $number = $prefix . $date . '-' . Str::upper(Str::random($length)) . $suffix;
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    protected function calculateSubtotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $basePrice = $item['price'];
            $optionsPrice = $this->calculateItemOptionsPrice($item['selected_options'] ?? []);
            $total += ($basePrice + $optionsPrice) * $item['quantity'];
        }
        return $total;
    }

    protected function calculateDiscounts(array $orderData, $coupon_code = null): float
    {
        if (empty($orderData['coupon_code'])) {
            return 0.0;
        }

        try {
            // Use DiscountService to properly calculate discount
            $subtotal = $this->calculateSubtotal($orderData['order_items'] ?? $orderData['items'] ?? []);

            // Get customer from order data
            $customer = null;
            if (isset($orderData['customer_id'])) {
                $customer = \Modules\Acl\Models\Customer::find($orderData['customer_id']);
            }

            // Prepare cart items for discount validation
            $cartItems = [];
            $items = $orderData['order_items'] ?? $orderData['items'] ?? [];
            foreach ($items as $item) {
                $cartItems[] = [
                    'item_id' => $item['food_item_id'] ?? $item['item_id'] ?? 0,
                    'quantity' => $item['quantity'] ?? 1,
                    'price' => $item['price'] ?? 0,
                    'total_price' => $item['total'] ?? ($item['price'] * $item['quantity']),
                ];
            }

            // Apply coupon using DiscountService
            $discountResult = $this->discountService->applyCoupon(
                $orderData['coupon_code'],
                $subtotal,
                $customer,
                $cartItems
            );

            return $discountResult['discount_amount'];
        } catch (\Exception $e) {
            // Log the error but don't fail the order
            \Illuminate\Support\Facades\Log::warning('Discount calculation failed during order creation', [
                'coupon_code' => $orderData['coupon_code'],
                'error' => $e->getMessage()
            ]);

            return 0.0;
        }
    }

    protected function calculateTax(array $orderData, \Modules\Acl\Models\Customer $customer): float
    {
        $taxCalculation = $this->calculateTaxForOrder($orderData, $customer);
        return $taxCalculation['tax_amount'];
    }

    protected function calculateTaxForOrder(array $orderData, \Modules\Acl\Models\Customer $customer): array
    {
        // Prepare items for tax calculation
        $items = [];
        $orderItems = $orderData['order_items'] ?? $orderData['items'] ?? [];

        foreach ($orderItems as $item) {
            // Support both old food_item_id and new item_id/item_type format
            $itemId = $item['item_id'] ?? $item['food_item_id'] ?? null;
            $itemType = $item['item_type'] ?? 'Modules\FoodManagements\Models\FoodItem';

            if (!$itemId) {
                continue;
            }

            $foodItem = $itemType::find($itemId);
            if ($foodItem) {
                $items[] = [
                    'food_item' => $foodItem,
                    'quantity' => $item['quantity'] ?? 1,
                    'tax_category_ids' => null // Use default tax categories
                ];
            }
        }

        if (empty($items)) {
            return [
                'tax_amount' => 0.0,
                'is_inclusive' => false
            ];
        }

        // Use FoodItemTaxService to calculate cart tax
        $taxCalculation = $this->taxService->calculateCartTax($items);

        // Check if tax is inclusive from settings
        $calculationType = fn_get_setting('tax.calculation_type', 'exclusive');
        $isInclusive = $calculationType === 'inclusive';

        return [
            'tax_amount' => $taxCalculation['total_tax_amount'],
            'is_inclusive' => $isInclusive
        ];
    }

    /**
     * Get order details for food ordering system
     */
    public function getOrder(int $orderId): array
    {
        $order = Order::with([
            'items.item',
            'customer',
            'vendor',
            'payments',
            'transactions',
        ])->findOrFail($orderId);


        $customer = $order->customer;
        $restaurant = $order->vendor;

        $deliveryAddress = $order->delivery_address ?? [];
        $billingAddress = $order->billing_address ?? [];

        $itemsData = [];
        foreach ($order->items as $item) {
            // Calculate line total for compatibility
            $lineTotal = $item->total_price ?? ($item->price * $item->quantity);

            // Calculate tax amount from tax rate
            $taxAmount = ($item->price * $item->quantity) * ($item->tax_rate / 100);

            $itemsData[] = [
                'id' => $item->id,
                'item_id' => $item->item_id,
                'item_type' => $item->item_type,
                'item_name' => $item->item_name,
                'food_item_id' => $item->item_id, // Backward compatibility
                'food_item_name' => $item->item_name, // Backward compatibility
                'sku' => $item->sku,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'selected_options' => $item->selected_options,
                'special_instructions' => $item->special_instructions,
                'total_price' => $item->total_price,
                'line_total' => $lineTotal, // Add for blade template compatibility
                'tax_rate' => $item->tax_rate,
                'tax' => $taxAmount, // Add calculated tax amount for blade template
            ];
        }

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'formatted_status' => $order->formatted_status,
            'order_type' => $order->order_type,
            'formatted_order_type' => $order->formatted_order_type,
            'currency' => $order->currency,
            'coupon_code' => $order->coupon_code,
            'payment_method' => $order->payment_method,
            'payment_method_name' => $this->getPaymentMethodName($order->payment_method),
            'payment_status' => $order->payment_status,
            'estimated_delivery_time' => $order->estimated_delivery_time,
            'actual_delivery_time' => $order->actual_delivery_time,
            'delivery_instructions' => $order->delivery_instructions,
            'phone_number' => $order->phone_number,
            'order_items' => $itemsData,
            'order_summary' => [
                'subtotal' => $order->subtotal,
                'discount' => $order->discount,
                'tax' => $order->tax,
                'delivery_fee' => $order->delivery_fee,
                'service_charge' => $order->service_charge,
                'total' => $order->total,
            ],
            'customer_details' => $customer ? [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? '',
                'type' => $order->customer_type,
            ] : [
                'id' => null,
                'name' => 'N/A',
                'email' => 'N/A',
                'phone' => '',
                'type' => $order->customer_type,
            ],
            'restaurant_details' => $restaurant ? [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'phone' => $restaurant->phone ?? '',
                'address' => ($restaurant->address_line1 ?? '') . ' ' . ($restaurant->address_line2 ?? ''),
                'city' => $restaurant->city ?? '',
            ] : [
                'id' => null,
                'name' => 'N/A',
                'phone' => '',
                'address' => '',
                'city' => '',
            ],
            'delivery_address' => $deliveryAddress,
            'billing_address' => $billingAddress,
            'payment_details' => $order->payments->first(),
            'transactions' => $order->transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'currency' => $transaction->currency,
                    'notes' => $transaction->notes,
                    'gateway_reference' => $transaction->gateway_reference,
                    'metadata' => $transaction->metadata,
                    'processed_at' => $transaction->processed_at,
                    'created_at' => $transaction->created_at,
                ];
            })->toArray(),
            'order_note' => $order->notes,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    }

    /**
     * Recalculate order data stored in session (array-based)
     */
    public function recalculateSessionOrder(array $orderData): array
    {
        $items = $orderData['order_items'] ?? [];
        $subtotal = 0.0;

        foreach ($items as $index => &$item) {
            $price = (float) ($item['price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            $lineTotal = $price * $quantity;
            $item['line_total'] = $lineTotal;
            $subtotal += $lineTotal;
        }

        // Calculate discount
        $discount = $this->calculateDiscounts($orderData, $orderData['coupon_code'] ?? null);

        // Calculate tax using FoodItemTaxService if customer is available
        $tax = 0.0;
        $isInclusive = false;
        if (!empty($orderData['customer_details']['id'])) {
            try {
                $customer = \Modules\Acl\Models\Customer::find($orderData['customer_details']['id']);
                if ($customer) {
                    $taxCalculation = $this->calculateTaxForOrder($orderData, $customer);
                    $tax = $taxCalculation['tax_amount'];
                    $isInclusive = $taxCalculation['is_inclusive'];
                }
            } catch (\Exception $e) {
                // Fallback: no tax if calculation fails
                $tax = 0.0;
                $isInclusive = false;
            }
        }

        // Calculate total based on tax type
        if ($isInclusive) {
            // For inclusive tax, don't add tax to total since it's already included in item prices
            $total = $subtotal - $discount;
        } else {
            // For exclusive tax, add tax to total
            $total = $subtotal - $discount + $tax;
        }

        // Update order data
        $orderData['order_items'] = $items;
        $orderData['subtotal'] = $subtotal;
        $orderData['discount'] = $discount;
        $orderData['tax'] = $tax;
        $orderData['total'] = $total;

        return $orderData;
    }

    /**
     * Update an existing order with new data
     */
    public function markOrderPaymentFailed(Order $order, ?string $reason = null): Order
    {
        if (!$order->exists) {
            return $order;
        }

        return DB::transaction(function () use ($order, $reason) {
            // Use the latest persisted state and serialize with cancellation so only
            // one terminal flow can restore inventory for this order.
            $order = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $status = $order->status ?? null;

            if (in_array($status, [Order::STATUS_FAILED, Order::STATUS_CANCELLED, Order::STATUS_REFUNDED], true)) {
                return $order->fresh()->load('items', 'customer');
            }

            $failureReason = $reason ?? 'Payment failed';
            $order->update([
                'status' => Order::STATUS_FAILED,
                'payment_status' => Order::PAYMENT_FAILED,
                'cancelled_at' => now(),
                'cancellation_reason' => $failureReason,
                'notes' => ($order->notes ? $order->notes . "\n\n" : '') . 'Payment failed: ' . $failureReason,
            ]);

            return $order->fresh()->load('items', 'customer');
        });
    }

    public function updateOrder(Order $order, array $updateData, bool $updateItems = false): Order
    {
        return DB::transaction(function () use ($order, $updateData, $updateItems) {
            // Apply base updates
            $order->update(array_intersect_key($updateData, array_flip([
                'status',
                'billing_address',
                'delivery_address',
                'notes',
                'coupon_code',
                'payment_method',
                'payment_status',
                'customer_id',
                'customer_type',
                'cancelled_at',
                'cancellation_reason'
            ])));

            // Update items if needed
            if ($updateItems && !empty($updateData['items'])) {
                // Simple implementation - you can make this more sophisticated
                $order->items()->delete();
                $this->processOrderItems($order, $updateData['items']);
            }

            return $order->fresh()->load('items', 'customer');
        });
    }

    /**
     * Recalculate order totals after item changes
     */
    public function recalculateOrderTotals(Order $order): Order
    {
        // Calculate subtotal from all items
        $subtotal = $order->items->sum('total_price');

        // Calculate tax
        $taxAmount = 0;
        foreach ($order->items as $item) {
            $itemSubtotal = $item->price * $item->quantity;
            $taxAmount += $itemSubtotal * ($item->tax_rate / 100);
        }

        // Apply discount if coupon exists
        $discountAmount = 0;
        if ($order->coupon_code) {
            // You can implement discount calculation logic here
            $discountAmount = $order->discount ?? 0;
        }

        // Calculate total
        $total = $subtotal + $taxAmount + ($order->delivery_fee ?? 0) + ($order->service_charge ?? 0) - $discountAmount;

        // Update order
        $order->update([
            'subtotal' => $subtotal,
            'tax' => $taxAmount,
            'discount' => $discountAmount,
            'total' => $total
        ]);

        return $order->fresh();
    }

    /**
     * Resolve the payment method name for order and transaction details.
     */
    public function getPaymentMethodName($paymentMethodId, ?\Modules\Payments\Models\Payments $payment = null): string
    {
        $paymentConfig = $payment?->method;
        if ($paymentConfig) {
            return $paymentConfig->merchant_name ?: ($paymentConfig->method?->name ?: 'Unknown');
        }

        if (empty($paymentMethodId)) {
            return 'N/A';
        }

        if (!is_numeric($paymentMethodId)) {
            return in_array(strtolower($paymentMethodId), ['cod', 'cash', 'cash_on_delivery'], true)
                ? 'Cash on Delivery'
                : ucwords(str_replace('_', ' ', $paymentMethodId));
        }

        $paymentConfig = \Modules\Payments\Models\PaymentConfiguration::with('method')->find($paymentMethodId);
        if ($paymentConfig && $paymentConfig->method) {
            return $paymentConfig->merchant_name ?: ($paymentConfig->method->name ?: 'Unknown');
        }

        return 'Unknown';
    }
}
