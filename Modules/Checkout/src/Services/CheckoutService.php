<?php

namespace Modules\Checkout\Services;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * CheckoutService
 * 
 * Orchestrates the checkout process including:
 * - Cart aggregation
 * - Tax calculation (optional)
 * - Discount application (optional)
 * - Shipping calculation
 * - Order creation
 * - Payment processing
 */
class CheckoutService
{
    /**
     * Calculate order totals with tax and discounts
     * 
     * @param array $items Order items
     * @param array $shippingAddress Shipping address
     * @param array $billingAddress Billing address
     * @param $customer Customer information
     * @param array $couponCodes Applied coupon codes
     * @return array Totals breakdown
     */
    public function calculateTotals(
        array $items,
        array $shippingAddress,
        array $billingAddress,
        $customer,
        array $couponCodes = []
    ): array {
        try {
            // Calculate subtotal (sum of all items)
            $subtotal = $this->calculateSubtotal($items);

            // Calculate tax (optional - graceful fallback)
            $taxAmount = $this->calculateTax($items, $shippingAddress, $billingAddress, $customer);

            // Calculate shipping (optional)
            $shippingAmount = $this->calculateShipping($items, $shippingAddress);

            // Calculate discounts (optional - graceful fallback)
            $discounts = $this->calculateDiscounts($items, $subtotal, $customer, $couponCodes);
            $discountAmount = $discounts['total'] ?? 0;

            // Calculate totals
            $total = $subtotal + $taxAmount + $shippingAmount - $discountAmount;
            $total = max(0, $total); // Ensure total is never negative

            return [
                'subtotal' => round($subtotal, 2),
                'tax' => [
                    'amount' => round($taxAmount, 2),
                    'details' => $discounts['tax_details'] ?? [],
                ],
                'shipping' => [
                    'amount' => round($shippingAmount, 2),
                    'method' => $shippingAddress['shipping_method'] ?? 'standard',
                ],
                'discounts' => [
                    'amount' => round($discountAmount, 2),
                    'items' => $discounts['items'] ?? [],
                    'coupons' => $discounts['coupons'] ?? [],
                ],
                'total' => round($total, 2),
                'currency' => fn_get_setting('general.currency') ?? 'USD',
            ];
        } catch (Exception $e) {
            Log::error('Checkout calculation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate subtotal (sum of items)
     */
    private function calculateSubtotal(array $items): float
    {
        return array_reduce($items, function ($sum, $item) {
            return $sum + (($item['price'] ?? 0) * ($item['quantity'] ?? 1));
        }, 0);
    }

    /**
     * Calculate tax (optional - checks if Tax module exists)
     */
    private function calculateTax(
        array $items,
        array $shippingAddress,
        array $billingAddress,
        $customer
    ): float {
        try {
            if (!class_exists('Modules\Tax\Services\TaxService')) {
                Log::info('Tax module not available - skipping tax calculation');
                return 0;
            }

            $taxService = app('Modules\Tax\Services\TaxService');
            return $taxService->calculate($items, $shippingAddress, $billingAddress, $customer);
        } catch (Exception $e) {
            Log::warning('Tax calculation failed', ['error' => $e->getMessage()]);
            return 0; // Graceful fallback
        }
    }

    /**
     * Calculate shipping amount
     */
    private function calculateShipping(array $items, array $shippingAddress): float
    {
        try {
            // Check if shipping is free
            if (fn_get_setting('shipping.free_threshold')) {
                $subtotal = $this->calculateSubtotal($items);
                if ($subtotal >= fn_get_setting('shipping.free_threshold')) {
                    return 0;
                }
            }

            // Standard shipping cost
            return (float) (fn_get_setting('shipping.standard_cost') ?? 0);
        } catch (Exception $e) {
            Log::warning('Shipping calculation error', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Calculate discounts (optional - checks if Discounts module exists)
     */
    private function calculateDiscounts(
        array $items,
        float $subtotal,
        $customer,
        array $couponCodes = []
    ): array {
        try {
            if (!class_exists('Modules\Discounts\Services\DiscountService')) {
                Log::info('Discounts module not available - skipping discount calculation');
                return ['total' => 0, 'items' => [], 'coupons' => []];
            }

            $discountService = app('Modules\Discounts\Services\DiscountService');
            
            // Calculate coupon discounts
            $totalDiscount = 0;
            $appliedCoupons = [];
            
            foreach ($couponCodes as $couponCode) {
                try {
                    $couponResult = $discountService->applyCoupon($couponCode, $subtotal, $customer, $items);
                    $totalDiscount += $couponResult['discount_amount'];
                    $appliedCoupons[] = [
                        'code' => $couponCode,
                        'amount' => $couponResult['discount_amount'],
                        'type' => $couponResult['discount_type'],
                        'name' => $couponResult['discount']->name ?? $couponCode,
                    ];
                } catch (Exception $e) {
                    Log::warning('Coupon application failed', [
                        'coupon' => $couponCode,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other coupons
                }
            }

            return [
                'total' => $totalDiscount,
                'items' => [], // No item-specific discounts for now
                'coupons' => $appliedCoupons,
            ];
        } catch (Exception $e) {
            Log::warning('Discount calculation failed', ['error' => $e->getMessage()]);
            return ['total' => 0, 'items' => [], 'coupons' => []]; // Graceful fallback
        }
    }

    /**
     * Validate checkout data
     */
    public function validateCheckout(array $data): array
    {
        $errors = [];

        // Validate items
        if (empty($data['items'])) {
            $errors['items'] = 'Checkout cart is empty';
        }

        // Validate shipping address
        if (empty($data['shipping_address'])) {
            $errors['shipping_address'] = 'Shipping address is required';
        } else {
            $shippingErrors = $this->validateAddress($data['shipping_address']);
            if (!empty($shippingErrors)) {
                $errors['shipping_address'] = $shippingErrors;
            }
        }

        // Validate billing address
        if (empty($data['billing_address'])) {
            $errors['billing_address'] = 'Billing address is required';
        } else {
            $billingErrors = $this->validateAddress($data['billing_address']);
            if (!empty($billingErrors)) {
                $errors['billing_address'] = $billingErrors;
            }
        }

        // Validate payment method
        if (empty($data['payment_method'])) {
            $errors['payment_method'] = 'Payment method is required';
        }

        return $errors;
    }

    /**
     * Validate address format
     */
    private function validateAddress(array $address): array
    {
        $errors = [];

        $required = ['first_name', 'last_name', 'address_1', 'city', 'state', 'postcode', 'country'];
        
        foreach ($required as $field) {
            if (empty($address[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }

        return $errors;
    }

    /**
     * Get available payment methods
     */
    public function getPaymentMethods(): array
    {
        try {
            if (!class_exists('Modules\Payments\Models\PaymentConfiguration')) {
                return [];
            }

            return \Modules\Payments\Models\PaymentConfiguration::where('is_enabled', true)
                ->with('method')
                ->get()
                ->map(function ($config) {
                    return [
                        'code' => $config->payment_method_id,
                        'name' => $config->method->name,
                        'description' => $config->method->description,
                        'icon' => $config->method->icon,
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::warning('Payment methods retrieval failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get available shipping methods
     */
    public function getShippingMethods(): array
    {
        return [
            [
                'code' => 'standard',
                'name' => 'Standard Shipping',
                'description' => 'Standard delivery (5-7 business days)',
                'cost' => fn_get_setting('shipping.standard_cost') ?? 0,
            ],
            [
                'code' => 'express',
                'name' => 'Express Shipping',
                'description' => 'Fast delivery (2-3 business days)',
                'cost' => fn_get_setting('shipping.express_cost') ?? 0,
            ],
            [
                'code' => 'overnight',
                'name' => 'Overnight Shipping',
                'description' => 'Next day delivery',
                'cost' => fn_get_setting('shipping.overnight_cost') ?? 0,
            ],
        ];
    }
}
