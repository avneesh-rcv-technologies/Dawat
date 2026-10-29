<?php

namespace Modules\Subscriptions\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $plans = [
            [
                'name' => 'Free',
                'description' => 'Perfect for individuals getting started',
                'price' => 0.00,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'features' => json_encode([
                    '1 User',
                    '5 Projects',
                    '1GB Storage',
                    'Basic Support',
                    'Community Access'
                ]),
                'max_users' => 1,
                'max_projects' => 5,
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Starter',
                'description' => 'Great for small teams and startups',
                'price' => 19.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'features' => json_encode([
                    '5 Users',
                    '25 Projects',
                    '10GB Storage',
                    'Email Support',
                    'Priority Support',
                    'API Access',
                    'Advanced Analytics'
                ]),
                'max_users' => 5,
                'max_projects' => 25,
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Professional',
                'description' => 'Ideal for growing businesses',
                'price' => 49.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 30,
                'features' => json_encode([
                    '20 Users',
                    '100 Projects',
                    '50GB Storage',
                    '24/7 Priority Support',
                    'API Access',
                    'Advanced Analytics',
                    'Custom Integrations',
                    'Team Collaboration Tools',
                    'White Label Options'
                ]),
                'max_users' => 20,
                'max_projects' => 100,
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Business',
                'description' => 'Advanced features for large teams',
                'price' => 99.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 30,
                'features' => json_encode([
                    '50 Users',
                    'Unlimited Projects',
                    '200GB Storage',
                    '24/7 Dedicated Support',
                    'Full API Access',
                    'Advanced Analytics & Reporting',
                    'Custom Integrations',
                    'Team Collaboration Tools',
                    'White Label',
                    'SSO Integration',
                    'Audit Logs',
                    'Custom Workflows'
                ]),
                'max_users' => 50,
                'max_projects' => null,
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Unlimited everything for large organizations',
                'price' => 299.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 30,
                'features' => json_encode([
                    'Unlimited Users',
                    'Unlimited Projects',
                    'Unlimited Storage',
                    '24/7 Dedicated Account Manager',
                    'Full API Access',
                    'Custom Analytics & Reporting',
                    'Custom Integrations',
                    'Advanced Team Collaboration',
                    'Full White Label',
                    'SSO & SAML Integration',
                    'Advanced Audit Logs',
                    'Custom Workflows',
                    'SLA Guarantee',
                    'On-premise Deployment Option',
                    'Custom Training'
                ]),
                'max_users' => null,
                'max_projects' => null,
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Annual Starter',
                'description' => 'Save 20% with annual billing',
                'price' => 191.90,
                'billing_cycle' => 'yearly',
                'trial_days' => 14,
                'features' => json_encode([
                    '5 Users',
                    '25 Projects',
                    '10GB Storage',
                    'Email Support',
                    'Priority Support',
                    'API Access',
                    'Advanced Analytics',
                    '2 Months Free'
                ]),
                'max_users' => 5,
                'max_projects' => 25,
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Lifetime Pro',
                'description' => 'One-time payment, lifetime access',
                'price' => 999.00,
                'billing_cycle' => 'one-time',
                'trial_days' => 30,
                'features' => json_encode([
                    '10 Users',
                    'Unlimited Projects',
                    '100GB Storage',
                    'Lifetime Updates',
                    'Priority Support',
                    'API Access',
                    'Advanced Analytics',
                    'Custom Integrations',
                    'Team Collaboration Tools'
                ]),
                'max_users' => 10,
                'max_projects' => null,
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('plans')->insert($plans);

        $this->command->info('Plans seeded successfully!');
    }
}
