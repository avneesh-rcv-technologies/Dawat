<?php

namespace Modules\Subscriptions\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Acl\Models\Admin;

class SubscriptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

    
        $now = Carbon::now();

        // Clean slate - must truncate in correct order due to FK constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('subscription_reminders')->truncate();
        DB::table('subscription_features')->truncate();
        DB::table('subscriptions')->truncate();
        DB::table('subscription_products')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get plan IDs
        $plans = DB::table('plans')->select('id', 'name')->get();
        
        // Get some admin users (assuming admins table exists)
        $adminIds = DB::table('admins')->pluck('id')->take(10)->toArray();

        if (empty($adminIds)) {
            $this->command->warn('No admins found. Skipping subscriptions seeding.');
            return;
        }

        // First seed subscription products (product-plan mappings)
        $this->seedSubscriptionProducts($now);

        // Get some subscription products to link with subscriptions
        $subscriptionProducts = DB::table('subscription_products')
            ->select('id', 'plan_id', 'product_id', 'product_type', 'type')
            ->get();

        if ($subscriptionProducts->isEmpty()) {
            $this->command->warn('No subscription products found. Skipping subscriptions seeding.');
            return;
        }

        $subscriptions = [];

        // Create subscriptions using available subscription_products
        // Use direct purchase entries for simplicity
        $directPurchases = $subscriptionProducts->where('type', 'direct');
        
        foreach ($adminIds as $index => $adminId) {
            // Cycle through available direct purchase subscription_products
            $subscriptionProduct = $directPurchases->skip($index % $directPurchases->count())->first();
            
            if (!$subscriptionProduct) {
                continue;
            }

            $startDate = $now->copy()->subDays(rand(10, 90));
            $trialEndsAt = $startDate->copy()->addDays(30);
            $currentPeriodStart = $startDate->copy();
            $currentPeriodEnd = $currentPeriodStart->copy()->addMonth();

            $subscriptions[] = [
                'subscribable_type' => config('config.subscription_default_class'),
                'subscribable_id' => $adminId,
                'plan_id' => $subscriptionProduct->plan_id,
                'subscription_product_id' => $subscriptionProduct->id,
                'status' => 'active',
                'start_date' => $startDate,
                'trial_ends_at' => null,
                'current_period_start' => $currentPeriodStart,
                'current_period_end' => $currentPeriodEnd,
                'ends_at' => null,
                'canceled_at' => null,
                'cancel_at_period_end' => false,
                'gateway_subscription_id' => 'sub_' . strtoupper(uniqid()),
                'gateway_customer_type' => config('config.subscription_default_class'),
                'gateway_customer_id' => $adminId,
                'payment_gateway' => 'stripe',
                'metadata' => json_encode([
                    'signup_source' => 'web',
                    'referral_code' => null,
                    'subscription_product_id' => $subscriptionProduct->id,
                ]),
                'notes' => 'Active subscription via ' . ($subscriptionProduct->type === 'direct' ? 'direct plan purchase' : 'product mapping'),
                'created_at' => $startDate,
                'updated_at' => $now,
            ];
        }

        // Add a few more subscriptions with different statuses if we have enough subscription_products
        if ($directPurchases->count() >= 2 && isset($adminIds[0])) {
            // Trialing subscription
            $sp = $directPurchases->skip(1)->first();
            if ($sp) {
                $startDate = $now->copy()->subDays(5);
                $trialEndsAt = $startDate->copy()->addDays(14);
                
                $subscriptions[] = [
                    'subscribable_type' => config('config.subscription_default_class'),
                    'subscribable_id' => $adminIds[0],
                    'plan_id' => $sp->plan_id,
                    'subscription_product_id' => $sp->id,
                    'status' => 'trialing',
                    'start_date' => $startDate,
                    'trial_ends_at' => $trialEndsAt,
                    'current_period_start' => $startDate,
                    'current_period_end' => $trialEndsAt,
                    'ends_at' => null,
                    'canceled_at' => null,
                    'cancel_at_period_end' => false,
                    'gateway_subscription_id' => 'sub_' . strtoupper(uniqid()),
                    'gateway_customer_type' => config('config.subscription_default_class'),
                    'gateway_customer_id' => $adminIds[0],
                    'payment_gateway' => 'stripe',
                    'metadata' => json_encode(['trial_started' => $startDate->toDateTimeString()]),
                    'notes' => 'Currently in trial period',
                    'created_at' => $startDate,
                    'updated_at' => $now,
                ];
            }
        }

        if ($directPurchases->count() >= 3 && isset($adminIds[0])) {
            // Canceled subscription
            $sp = $directPurchases->skip(2)->first();
            if ($sp) {
                $startDate = $now->copy()->subDays(60);
                $canceledAt = $now->copy()->subDays(5);
                $currentPeriodStart = $now->copy()->subDays(15);
                $currentPeriodEnd = $currentPeriodStart->copy()->addMonth();
                
                $subscriptions[] = [
                    'subscribable_type' => config('config.subscription_default_class'),
                    'subscribable_id' => $adminIds[0],
                    'plan_id' => $sp->plan_id,
                    'subscription_product_id' => $sp->id,
                'status' => 'active',
                'start_date' => $startDate,
                'trial_ends_at' => null,
                'current_period_start' => $currentPeriodStart,
                'current_period_end' => $currentPeriodEnd,
                'ends_at' => $currentPeriodEnd,
                'canceled_at' => $canceledAt,
                'cancel_at_period_end' => true,
                'gateway_subscription_id' => 'sub_' . strtoupper(uniqid()),
                'gateway_customer_type' => config('config.subscription_default_class'),
                'gateway_customer_id' => $adminIds[0],
                'payment_gateway' => 'stripe',
                'metadata' => json_encode([
                    'cancellation_reason' => 'switching_plans',
                ]),
                'notes' => 'Canceled, will end at period end',
                'created_at' => $startDate,
                'updated_at' => $canceledAt,
            ];
            }
        }

        if (!empty($subscriptions)) {
            DB::table('subscriptions')->insert($subscriptions);
            
            // Get the inserted subscription IDs
            $insertedSubscriptions = DB::table('subscriptions')
                ->whereIn('subscribable_id', $adminIds)
                ->get(['id', 'plan_id']);
            
            // Seed subscription features for each subscription
            $this->seedSubscriptionFeatures($insertedSubscriptions);
        }

        $this->command->info('Subscriptions seeded successfully! Created ' . count($subscriptions) . ' subscriptions.');
        $this->command->info('Mix of product mappings and direct plan purchases demonstrated!');

        // Seed test reminders for the first few subscriptions
        $this->seedTestReminders($now);
    }

    /**
     * Seed subscription features (copy from plan features with usage tracking)
     */
    private function seedSubscriptionFeatures($subscriptions)
    {
        $now = \Carbon\Carbon::now();
        
        if ($subscriptions->isEmpty()) {
            return;
        }

        $subscriptionFeatures = [];

        foreach ($subscriptions as $subscription) {
            // Get all features for this plan
            $planFeatures = DB::table('plan_features')
                ->where('plan_id', $subscription->plan_id)
                ->get();

            foreach ($planFeatures as $planFeature) {
                // Get the feature to check its type
                $feature = DB::table('features')
                    ->where('id', $planFeature->feature_id)
                    ->first();

                if (!$feature) {
                    continue;
                }

                // Calculate initial usage based on feature type
                $initialUsed = 0;
                $usageResetAt = null;

                if ($feature->type === 'limit') {
                    // Start with some usage for demonstration
                    $value = $planFeature->value;
                    if (is_numeric($value)) {
                        $initialUsed = rand(0, (int)($value * 0.3));
                    }
                    // Set usage reset for next month
                    $usageResetAt = $now->copy()->addMonth()->startOfMonth();
                } elseif ($feature->type === 'boolean') {
                    // For boolean, mark as fully used (activated)
                    $initialUsed = $planFeature->value === 'true' ? 1 : 0;
                } elseif ($feature->type === 'package') {
                    // For package, no usage tracking needed
                    $initialUsed = 0;
                }

                $subscriptionFeatures[] = [
                    'subscription_id' => $subscription->id,
                    'feature_id' => $planFeature->feature_id,
                    'value' => $planFeature->value,
                    'used' => $initialUsed,
                    'usage_reset_at' => $usageResetAt,
                    'is_custom' => false,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($subscriptionFeatures)) {
            // Insert in chunks to avoid memory issues
            $chunks = array_chunk($subscriptionFeatures, 100);
            foreach ($chunks as $chunk) {
                DB::table('subscription_features')->insert($chunk);
            }
            
            $this->command->info('Subscription features seeded successfully! Created ' . count($subscriptionFeatures) . ' feature records.');
        }
    }

    /**
     * Helper to get or fallback to direct purchase subscription_product
     */
    private function getSubscriptionProduct($subscriptionProducts, $planId, $productId = null, $productType = null)
    {
        // Try to find product-specific mapping first
        if ($productId && $productType) {
            $sp = $subscriptionProducts->where('plan_id', $planId)
                ->where('product_id', $productId)
                ->where('product_type', $productType)
                ->first();
            
            if ($sp) {
                return $sp;
            }
        }

        // Fallback to direct purchase for this plan
        $sp = $subscriptionProducts->where('plan_id', $planId)
            ->where('type', 'direct')
            ->first();

        return $sp;
    }

    /**
     * Seed subscription products with Admin morph relationships
     * DUAL ENTRY SYSTEM:
     * - Type 'product': Admin-mapped products to plans
     * - Type 'direct': Auto-created for direct plan purchases
     */
    private function seedSubscriptionProducts($now)
    {
        // subscription_products already truncated in run() method

        // Get all admins (these will act as our "products" for demo purposes)
        $admins = DB::table('admins')->select('id', 'name')->get();
        
        if ($admins->isEmpty()) {
            $this->command->warn('No admins found. Skipping subscription products seeding.');
            return;
        }

        // Get all plans with their IDs
        $plans = DB::table('plans')->select('id', 'name', 'price')->get();

        if ($plans->isEmpty()) {
            $this->command->warn('No plans found. Skipping subscription products seeding.');
            return;
        }

        $subscriptionProducts = [];

        // TYPE 2: DIRECT PLAN PURCHASES (auto-created when user buys plan directly)
        // Create a few direct purchase entries for demonstration
        foreach ($plans->take(3) as $plan) {
            $subscriptionProducts[] = [
                'product_type' => \Modules\Acl\Models\Admin::class, // No specific product
                'product_id' => 1,   // No specific product
                'plan_id' => $plan->id,
                'price' => $plan->price,
                'is_active' => true,
                'type' => 'direct', // Direct plan purchase
                'created_at' => $now->copy()->subDays(rand(1, 30)),
                'updated_at' => $now,
            ];
        }

        if (!empty($subscriptionProducts)) {
            // Insert in chunks to avoid memory issues
            $chunks = array_chunk($subscriptionProducts, 100);
            foreach ($chunks as $chunk) {
                DB::table('subscription_products')->insert($chunk);
            }
            
            $this->command->info('Subscription products seeded successfully! Created ' . count($subscriptionProducts) . ' entries.');
        }
    }

    /**
     * Seed test reminders for testing
     */
    private function seedTestReminders($now)
    {
        $subscriptionIds = DB::table('subscriptions')->pluck('id')->take(3)->toArray();
        
        $reminders = [];

        foreach ($subscriptionIds as $subscriptionId) {
            // Add some test reminders
            $reminders[] = [
                'subscription_id' => $subscriptionId,
                'type' => 'expiry',
                'channel' => 'email',
                'status' => 'sent',
                'reminder_number' => 1,
                'subject' => 'Subscription Expiring - Reminder #1',
                'message' => 'Your subscription will expire in 30 days. Please renew to continue enjoying our services.',
                'scheduled_at' => $now->copy()->subDays(20),
                'sent_at' => $now->copy()->subDays(20),
                'created_at' => $now->copy()->subDays(21),
                'updated_at' => $now->copy()->subDays(20),
            ];

            $reminders[] = [
                'subscription_id' => $subscriptionId,
                'type' => 'payment',
                'channel' => 'email',
                'status' => 'sent',
                'reminder_number' => 1,
                'subject' => 'Upcoming Payment Due',
                'message' => 'Your next payment is due in 3 days. Please update your payment method to avoid interruption.',
                'scheduled_at' => $now->copy()->subDays(5),
                'sent_at' => $now->copy()->subDays(5),
                'created_at' => $now->copy()->subDays(6),
                'updated_at' => $now->copy()->subDays(5),
            ];

            $reminders[] = [
                'subscription_id' => $subscriptionId,
                'type' => 'expiry',
                'channel' => 'in-app',
                'status' => 'pending',
                'reminder_number' => 2,
                'subject' => 'Subscription Expiring - Reminder #2',
                'message' => 'Your subscription expires in 7 days. Consider renewing now to avoid service disruption.',
                'scheduled_at' => $now->copy()->addDays(7),
                'sent_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $reminders[] = [
                'subscription_id' => $subscriptionId,
                'type' => 'expiry',
                'channel' => 'in-app',
                'status' => 'pending',
                'reminder_number' => 3,
                'subject' => 'Subscription Expiring - Final Notice',
                'message' => 'Final notice: Your subscription expires tomorrow.',
                'scheduled_at' => $now->copy()->addDays(14),
                'sent_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($reminders)) {
            DB::table('subscription_reminders')->insert($reminders);
            $this->command->info('Test reminders seeded successfully! Created ' . count($reminders) . ' reminders.');
        }
    }
}
