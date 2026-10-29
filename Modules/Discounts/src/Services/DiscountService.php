<?php

namespace Modules\Discounts\Services;

use Exception;
use Illuminate\Support\Str;
use Modules\Orders\Models\Order;
use Illuminate\Support\Facades\DB;
use Modules\Customers\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\Discounts\Models\Coupon;
use Modules\Discounts\Models\Discount;
use Modules\Discounts\Models\DiscountRule;

class DiscountService
{
    /**
     * Create a new discount with its coupons and rules
     *
     * @param array $data
     * @return Discount
     * @throws Exception
     */
    public function createDiscount(array $data): Discount
    {
        return DB::transaction(function () use ($data) {
            // Get the authenticated user with guard detection
            $createdBy = $this->getAuthenticatedUser();
            
            // Create the discount with morph relationship
            $discount = Discount::create([
                'name' => $data['name'],
                'created_by_type' => $createdBy ? get_class($createdBy) : null,
                'created_by_id' => $createdBy?->id,
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'apply_to' => $data['apply_to'] ?? 'subtotal',
                'user_groups' => $data['user_groups'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'starts_at' => $data['starts_at'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
            ]);

            // Create default coupon if not provided
            if (!isset($data['coupons'])) {
                $this->createDefaultCoupon($discount);
            }




            return $discount;
        });
    }

    /**
     * Create a default coupon for a discount
     *
     * @param Discount $discount
     * @return Coupon
     */
    public function createDefaultCoupon(Discount $discount): Coupon
    {
        return Coupon::create([
            'discount_id' => $discount->id,
            'code' => Str::upper(Str::random(8)),
            'description' => 'Auto-generated coupon for discount: ' . $discount->name,
            'is_active' => $discount->is_active,
            'starts_at' => $discount->starts_at,
            'expires_at' => $discount->expires_at,
        ]);
    }

    /**
     * Create a coupon for a discount
     *
     * @param Discount $discount
     * @param array $data
     * @return Coupon
     */
    public function createCouponForDiscount(Discount $discount, array $data): Coupon
    {
        return Coupon::create([
            'discount_id' => $discount->id,
            'code' => $data['code'],
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'] ?? $discount->starts_at,
            'expires_at' => $data['expires_at'] ?? $discount->expires_at,
            'usage_limit' => $data['usage_limit'] ?? null,
            'usage_limit_per_user' => $data['usage_limit_per_user'] ?? null,
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'is_active' => $data['is_active'] ?? $discount->is_active,
        ]);
    }

    /**
     * Save discount rules
     *
     * @param Discount $discount
     * @param array $rules
     * @return void
     */
    public function saveDiscountRules(Discount $discount, array $rules): void
    {
        // First delete existing rules
        $discount->rules()->delete();

        // Add new rules
        foreach ($rules as $rule) {
            DiscountRule::create([
                'discount_id' => $discount->id,
                'rule_type' => $rule['rule_type'],
                'rule_id' => $rule['rule_id'],
                'rule_value' => $rule['rule_value'] ?? null,
                'condition_type' => $rule['condition_type'] ?? null,
            ]);
        }
    }

    /**
     * Create a coupon
     *
     * @param array $data
     * @return Coupon
     */
    public function createCoupon(array $data): Coupon
    {
        return Coupon::create([
            'discount_id' => $data['discount_id'],
            'code' => $data['code'],
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'usage_limit_per_user' => $data['usage_limit_per_user'] ?? null,
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Validate a coupon code
     *
     * @param string $code
     * @param User|null $user
     * @param float|null $orderTotal
     * @return Coupon|null
     */
    public function validateCoupon(string $code, $user = null, ?float $orderTotal = null): ?Coupon
    {
        $coupon = Coupon::where('code', $code)
            ->with('discount')
            ->first();

        if (!$coupon) {
            return null;
        }

        // Check if coupon is active
        if (!$coupon->is_active) {
            return null;
        }

        // Check date validity
        $now = now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return null;
        }
        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            return null;
        }

        // Check usage limits
        if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) {
            return null;
        }

        // Check per-user usage limits
        if ($user && $coupon->usage_limit_per_user) {
            $userUsage = \Modules\Discounts\Models\CouponUsage::where('coupon_id', $coupon->id)
                ->where('usable_type', get_class($user))
                ->where('usable_id', $user->id)
                ->count();

            if ($userUsage >= $coupon->usage_limit_per_user) {
                return null;
            }
        }

        // Check minimum order amount
        if ($orderTotal && $coupon->min_order_amount && $orderTotal < $coupon->min_order_amount) {
            return null;
        }

        return $coupon;
    }

    /**
     * Apply coupon to an order and return discount details
     *
     * @param string $couponCode
     * @param float $subtotal
     * @param mixed $user
     * @param array $cartItems
     * @return array
     * @throws Exception
     */
    public function applyCoupon(string $couponCode, float $subtotal, $user = null, array $cartItems = []): array
    {
        // Validate the coupon first
        $coupon = $this->validateCoupon($couponCode, $user, $subtotal);

        if (!$coupon) {
            throw new Exception('Invalid or expired coupon code');
        }

        $discount = $coupon->discount;

        // Validate discount rules if any
        if (!empty($cartItems)) {
            $this->validateDiscountRules($discount, $cartItems, $subtotal);
        }

        // Calculate discount amount based on discount type
        $discountAmount = 0;

        switch ($discount->type->value) {
            case 'F': // Fixed amount
                $discountAmount = min($discount->amount, $subtotal);
                break;

            case 'P': // Percentage
                $discountAmount = $subtotal * ($discount->amount / 100);
                break;
        }

        // Round to 2 decimal places
        $discountAmount = round($discountAmount, 2);
        
        return [
            'coupon' => $coupon,
            'discount' => $discount,
            'discount_amount' => $discountAmount,
            'discount_type' => $discount->type,
            'apply_to' => $discount->apply_to,
        ];
    }

    /**
     * Record coupon usage when order is placed
     *
     * @param string $couponCode
     * @param mixed $user
     * @param int|null $orderId
     * @param float $discountAmount
     * @return void
     * @throws Exception
     */
    public function recordCouponUsage(string $couponCode, $user, ?int $orderId = null, float $discountAmount = 0): void
    {
        $coupon = Coupon::where('code', $couponCode)->first();
        
        if (!$coupon) {
            throw new Exception('Coupon not found');
        }

        DB::transaction(function () use ($coupon, $user, $orderId, $discountAmount) {
            // Create usage record
            \Modules\Discounts\Models\CouponUsage::create([
                'coupon_id' => $coupon->id,
                'usable_type' => $user ? get_class($user) : null,
                'usable_id' => $user?->id,
                'order_id' => $orderId,
                'discount_amount' => $discountAmount,
                'used_at' => now(),
            ]);

            // Increment times_used counter
            $coupon->increment('times_used');
        });
    }

    /**
     * Validate discount rules against cart items
     *
     * @param Discount $discount
     * @param array $cartItems
     * @param float $subtotal
     * @throws Exception
     */
    private function validateDiscountRules(Discount $discount, array $cartItems, float $subtotal): void
    {
        if ($discount->rules->isEmpty()) {
            return; // No rules to validate
        }

        foreach ($discount->rules as $rule) {
            switch ($rule->rule_type) {
                case 'product':
                    $this->validateProductRule($rule, $cartItems);
                    break;
                case 'category':
                    $this->validateCategoryRule($rule, $cartItems);
                    break;
                case 'subtotal':
                    $this->validateSubtotalRule($rule, $subtotal);
                    break;
                case 'quantity':
                    $this->validateQuantityRule($rule, $cartItems);
                    break;
            }
        }
    }

    /**
     * Validate product rule
     */
    private function validateProductRule($rule, array $cartItems): void
    {
        $hasProduct = collect($cartItems)->contains('item_id', $rule->rule_id);
        
        if (!$hasProduct) {
            throw new Exception('This coupon is only valid for specific products');
        }
    }

    /**
     * Validate category rule
     */
    private function validateCategoryRule($rule, array $cartItems): void
    {
        // This would need to be implemented based on your cart item structure
        // For now, we'll skip this validation
        return;
    }

    /**
     * Validate subtotal rule
     */
    private function validateSubtotalRule($rule, float $subtotal): void
    {
        $conditionType = $rule->condition_type ?? 'greater_than';
        $ruleValue = (float) $rule->rule_value;
        
        switch ($conditionType) {
            case 'greater_than':
                if ($subtotal <= $ruleValue) {
                    throw new Exception("Minimum subtotal of " . number_format($ruleValue, 2) . " required");
                }
                break;
            case 'less_than':
                if ($subtotal >= $ruleValue) {
                    throw new Exception("Maximum subtotal of " . number_format($ruleValue, 2) . " exceeded");
                }
                break;
            case 'equals':
                if ($subtotal != $ruleValue) {
                    throw new Exception("Subtotal must be exactly " . number_format($ruleValue, 2));
                }
                break;
        }
    }

    /**
     * Validate quantity rule
     */
    private function validateQuantityRule($rule, array $cartItems): void
    {
        $totalQuantity = collect($cartItems)->sum('quantity');
        $conditionType = $rule->condition_type ?? 'greater_than';
        $ruleValue = (int) $rule->rule_value;
        
        switch ($conditionType) {
            case 'greater_than':
                if ($totalQuantity <= $ruleValue) {
                    throw new Exception("Minimum quantity of {$ruleValue} items required");
                }
                break;
            case 'less_than':
                if ($totalQuantity >= $ruleValue) {
                    throw new Exception("Maximum quantity of {$ruleValue} items exceeded");
                }
                break;
            case 'equals':
                if ($totalQuantity != $ruleValue) {
                    throw new Exception("Quantity must be exactly {$ruleValue} items");
                }
                break;
        }
    }


    /**
     * Calculate discount amount based on discount type
     *
     * @param Discount $discount
     * @param Order $order
     * @return float
     */
    protected function calculateDiscountAmount(Discount $discount, $order): float
    {
        $amount = 0;

        switch ($discount->type) {
            case 'F':
                $amount = $discount->amount;
                break;
            case 'P':
                $baseAmount = $discount->apply_to === 'shipping'
                    ? $order->shipping_amount
                    : $order->subtotal;
                $amount = $baseAmount * ($discount->amount / 100);
                break;
            case 'free_shipping':
                $amount = $order->shipping_amount;
                break;
        }

        return round($amount, 2);
    }

    /**
     * Get the authenticated user from any available guard
     *
     * @return mixed
     */
    protected function getAuthenticatedUser()
    {
        return Auth::user();
    }
}
