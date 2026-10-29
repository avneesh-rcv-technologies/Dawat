<?php

namespace Modules\Discounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Discounts\Models\Discount;
use Modules\Discounts\Models\Coupon;
use Modules\Discounts\Models\CouponUsage;
use Modules\Discounts\Models\DiscountRule;
use Modules\Acl\Models\Admin;
use App\Models\User;
use Modules\Discounts\Enums\DiscountType;
use Modules\Discounts\Enums\DiscountApplyTo;

class DiscountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating Discounts with morph relationships...');

        // Get some admins and users for morph relationships
        $admins = Admin::take(2)->get();
        $users = Admin::take(3)->get();

        if ($admins->isEmpty()) {
            $this->command->warn('No admins found. Creating a default admin...');
            // $admin = Admin::create([
            //     'name' => 'Admin User',
            //     'email' => 'admin@example.com',
            //     'password' => bcrypt('password'),
            //     'status' => true,
            // ]);
            // $admins = collect([$admin]);
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Creating default users...');
            // for ($i = 1; $i <= 3; $i++) {
            //     $users->push(User::create([
            //         'name' => "User $i",
            //         'email' => "user$i@example.com",
            //         'password' => bcrypt('password'),
            //     ]));
            // }
        }

        // Create discounts
        $discounts = [
            [
                'name' => 'Summer Sale 2026',
                'description' => 'Get 20% off on all orders during summer',
                'type' => DiscountType::PERCENTAGE,
                'amount' => 20,
                'apply_to' => DiscountApplyTo::SUBTOTAL,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(3),
                'coupons' => [
                    ['code' => 'SUMMER20', 'usage_limit' => 100, 'usage_limit_per_user' => 1],
                    ['code' => 'SUMMER20VIP', 'usage_limit' => 50, 'usage_limit_per_user' => 2, 'min_order_amount' => 100],
                ],
                'rules' => [
                    ['rule_type' => 'subtotal', 'condition_type' => 'greater_than', 'rule_value' => 50],
                ],
            ],
            [
                'name' => 'New Customer Welcome',
                'description' => '$10 off for first-time customers',
                'type' => DiscountType::FIXED,
                'amount' => 10,
                'apply_to' => DiscountApplyTo::TOTAL,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
                'coupons' => [
                    ['code' => 'WELCOME10', 'usage_limit' => 500, 'usage_limit_per_user' => 1, 'min_order_amount' => 25],
                ],
            ],
            [
                'name' => 'Free Shipping Campaign',
                'description' => 'Free shipping on orders over $75',
                'type' => DiscountType::FIXED,
                'amount' => 10,
                'apply_to' => DiscountApplyTo::TOTAL,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'coupons' => [
                    ['code' => 'FREESHIP', 'usage_limit' => null, 'min_order_amount' => 75],
                    ['code' => 'FREESHIP50', 'usage_limit' => null, 'min_order_amount' => 50],
                ],
                'rules' => [
                    ['rule_type' => 'subtotal', 'condition_type' => 'greater_than', 'rule_value' => 75],
                ],
            ],
            [
                'name' => 'Flash Sale 50% OFF',
                'description' => 'Limited time 50% discount',
                'type' => DiscountType::PERCENTAGE,
                'amount' => 50,
                'apply_to' => DiscountApplyTo::SUBTOTAL,
                'is_active' => true,
                'starts_at' => now()->subDays(5),
                'expires_at' => now()->addDays(2),
                'coupons' => [
                    ['code' => 'FLASH50', 'usage_limit' => 50, 'usage_limit_per_user' => 1, 'min_order_amount' => 100],
                ],
            ],
            [
                'name' => 'Loyalty Reward Program',
                'description' => '15% off for returning customers',
                'type' => DiscountType::PERCENTAGE,
                'amount' => 15,
                'apply_to' => DiscountApplyTo::SUBTOTAL,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => null,
                'coupons' => [
                    ['code' => 'LOYAL15', 'usage_limit' => null, 'usage_limit_per_user' => 5],
                ],
            ],
            [
                'name' => 'Holiday Special $25 OFF',
                'description' => '$25 discount on orders over $150',
                'type' => DiscountType::FIXED,
                'amount' => 25,
                'apply_to' => DiscountApplyTo::TOTAL,
                'is_active' => false,
                'starts_at' => now()->addMonths(2),
                'expires_at' => now()->addMonths(3),
                'coupons' => [
                    ['code' => 'HOLIDAY25', 'usage_limit' => 200, 'min_order_amount' => 150],
                ],
            ],
        ];

        foreach ($discounts as $index => $discountData) {
            // Alternate between admin and user as creator
            $creator = $index % 2 === 0 ? $admins->random() : $users->random();

            $discount = Discount::create([
                'name' => $discountData['name'],
                'description' => $discountData['description'],
                'created_by_type' => get_class($creator),
                'created_by_id' => $creator->id,
                'type' => $discountData['type'],
                'amount' => $discountData['amount'],
                'apply_to' => $discountData['apply_to'],
                'is_active' => $discountData['is_active'],
                'starts_at' => $discountData['starts_at'],
                'expires_at' => $discountData['expires_at'],
            ]);

            $this->command->info("Created discount: {$discount->name}");

            // Create coupons for this discount
            if (isset($discountData['coupons'])) {
                foreach ($discountData['coupons'] as $couponData) {
                    $coupon = Coupon::create([
                        'discount_id' => $discount->id,
                        'code' => $couponData['code'],
                        'description' => "Coupon for {$discount->name}",
                        'starts_at' => $discount->starts_at,
                        'expires_at' => $discount->expires_at,
                        'usage_limit' => $couponData['usage_limit'] ?? null,
                        'usage_limit_per_user' => $couponData['usage_limit_per_user'] ?? null,
                        'min_order_amount' => $couponData['min_order_amount'] ?? null,
                        'times_used' => 0,
                        'is_active' => $discount->is_active,
                    ]);

                    $this->command->info("  - Created coupon: {$coupon->code}");

                    // Create some usage records for active coupons
                    if ($discount->is_active && $discount->starts_at <= now()) {
                        $usageCount = rand(0, min(5, $coupon->usage_limit ?? 5));
                        
                        for ($i = 0; $i < $usageCount; $i++) {
                            // Randomly assign to user or admin
                            $usable = rand(0, 1) === 0 ? $users->random() : $admins->random();
                            
                            CouponUsage::create([
                                'coupon_id' => $coupon->id,
                                'usable_type' => get_class($usable),
                                'usable_id' => $usable->id,
                                'order_id' => rand(1000, 9999), // Random order ID
                                'discount_amount' => $this->calculateDiscountAmount($discount),
                                'used_at' => now()->subDays(rand(1, 30)),
                            ]);

                            // Update times_used
                            $coupon->increment('times_used');
                        }

                        if ($usageCount > 0) {
                            $this->command->info("    - Created {$usageCount} usage record(s)");
                        }
                    }
                }
            }

            // Create rules for this discount
            if (isset($discountData['rules'])) {
                foreach ($discountData['rules'] as $ruleData) {
                    DiscountRule::create([
                        'discount_id' => $discount->id,
                        'rule_type' => $ruleData['rule_type'],
                        'rule_id' => $ruleData['rule_id'] ?? null,
                        'condition_type' => $ruleData['condition_type'] ?? null,
                        'rule_value' => $ruleData['rule_value'] ?? null,
                    ]);
                }
            }
        }

        $this->command->info('✅ Discounts seeding completed!');
        $this->command->info('Created: ' . Discount::count() . ' discounts');
        $this->command->info('Created: ' . Coupon::count() . ' coupons');
        $this->command->info('Created: ' . CouponUsage::count() . ' usage records');
        $this->command->info('Created: ' . DiscountRule::count() . ' rules');
    }

    /**
     * Calculate a realistic discount amount
     */
    private function calculateDiscountAmount(Discount $discount): float
    {
        $orderTotal = rand(50, 300);

        switch ($discount->type) {
            case DiscountType::PERCENTAGE:
                return round($orderTotal * ($discount->amount / 100), 2);
            
            case DiscountType::FIXED:
                return min($discount->amount, $orderTotal);
            
            default:
                return 0;
        }
    }
}
