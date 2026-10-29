<?php

namespace Modules\Checkout\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;

/**
 * CheckoutRepository
 * 
 * Handles checkout data operations
 */
class CheckoutRepository
{
    /**
     * Get cart session data
     */
    public function getCart(string $sessionId): array
    {
        try {
            return session()->get("cart.{$sessionId}", []);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Save cart session data
     */
    public function saveCart(string $sessionId, array $cartData): bool
    {
        try {
            session()->put("cart.{$sessionId}", $cartData);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get checkout session data
     */
    public function getCheckoutSession(string $sessionId): array
    {
        try {
            return session()->get("checkout.{$sessionId}", []);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Save checkout session data
     */
    public function saveCheckoutSession(string $sessionId, array $checkoutData): bool
    {
        try {
            session()->put("checkout.{$sessionId}", $checkoutData);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Clear checkout session
     */
    public function clearCheckoutSession(string $sessionId): bool
    {
        try {
            session()->forget("checkout.{$sessionId}");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Validate coupon code
     */
    public function validateCoupon(string $couponCode): array
    {
        try {
            if (!class_exists('Modules\Discounts\Models\Discount')) {
                return ['valid' => false, 'message' => 'Discounts module not available'];
            }

            $discount = \Modules\Discounts\Models\Discount::where('code', $couponCode)
                ->where('is_active', true)
                ->first();

            if (!$discount) {
                return ['valid' => false, 'message' => 'Invalid or expired coupon code'];
            }

            // Check if coupon is expired
            if ($discount->expiry_date && $discount->expiry_date < now()) {
                return ['valid' => false, 'message' => 'Coupon has expired'];
            }

            return [
                'valid' => true,
                'discount' => [
                    'code' => $discount->code,
                    'type' => $discount->discount_type,
                    'value' => $discount->discount_value,
                    'description' => $discount->description,
                ],
            ];
        } catch (Exception $e) {
            return ['valid' => false, 'message' => 'Error validating coupon'];
        }
    }
}
