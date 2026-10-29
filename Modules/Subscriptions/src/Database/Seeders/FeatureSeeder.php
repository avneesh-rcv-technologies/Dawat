<?php

namespace Modules\Subscriptions\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Subscriptions\Models\Feature;
use Modules\Subscriptions\Models\Plan;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean slate - truncate only feature-related tables
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \Illuminate\Support\Facades\DB::table('plan_features')->truncate();
        \Illuminate\Support\Facades\DB::table('features')->truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Create Features
        $features = [
            // Storage Features
            [
                'name' => 'storage_gb',
                'display_name' => 'Storage Space',
                'description' => 'Total storage space available',
                'type' => 'limit',
                'unit' => 'GB',
                'default_value' => '5',
                'group' => 'Storage',
                'sort_order' => 1,
                'metadata' => json_encode(['min' => 0, 'max' => 10000, 'step' => 1]),
            ],
            [
                'name' => 'file_uploads',
                'display_name' => 'File Uploads',
                'description' => 'Number of files that can be uploaded per month',
                'type' => 'limit',
                'unit' => 'files',
                'default_value' => '100',
                'group' => 'Storage',
                'sort_order' => 2,
                'metadata' => json_encode(['min' => 0, 'max' => 100000]),
            ],
            
            // API Features
            [
                'name' => 'api_calls_monthly',
                'display_name' => 'API Calls',
                'description' => 'Number of API calls allowed per month',
                'type' => 'limit',
                'unit' => 'requests',
                'default_value' => '1000',
                'group' => 'API Access',
                'sort_order' => 1,
                'metadata' => json_encode(['min' => 0, 'step' => 1000]),
            ],
            [
                'name' => 'api_rate_limit',
                'display_name' => 'API Rate Limit',
                'description' => 'Number of API requests per minute',
                'type' => 'limit',
                'unit' => 'req/min',
                'default_value' => '60',
                'group' => 'API Access',
                'sort_order' => 2,
            ],
            
            // User Management
            [
                'name' => 'team_members',
                'display_name' => 'Team Members',
                'description' => 'Maximum number of team members',
                'type' => 'limit',
                'unit' => 'users',
                'default_value' => '5',
                'group' => 'Users',
                'sort_order' => 1,
                'metadata' => json_encode(['min' => 1, 'max' => 1000]),
            ],
            [
                'name' => 'guest_users',
                'display_name' => 'Guest Users',
                'description' => 'Maximum number of guest users',
                'type' => 'limit',
                'unit' => 'users',
                'default_value' => '10',
                'group' => 'Users',
                'sort_order' => 2,
            ],
            
            // Project Features
            [
                'name' => 'projects',
                'display_name' => 'Projects',
                'description' => 'Number of projects allowed',
                'type' => 'limit',
                'unit' => 'projects',
                'default_value' => '3',
                'group' => 'Projects',
                'sort_order' => 1,
            ],
            
            // Boolean Features
            [
                'name' => 'advanced_analytics',
                'display_name' => 'Advanced Analytics',
                'description' => 'Access to advanced analytics dashboard',
                'type' => 'boolean',
                'default_value' => 'false',
                'group' => 'Features',
                'sort_order' => 1,
            ],
            [
                'name' => 'custom_branding',
                'display_name' => 'Custom Branding',
                'description' => 'Ability to customize branding and logos',
                'type' => 'boolean',
                'default_value' => 'false',
                'group' => 'Features',
                'sort_order' => 2,
            ],
            [
                'name' => 'priority_support',
                'display_name' => 'Priority Support',
                'description' => '24/7 priority customer support',
                'type' => 'boolean',
                'default_value' => 'false',
                'group' => 'Support',
                'sort_order' => 1,
            ],
            [
                'name' => 'sso_authentication',
                'display_name' => 'SSO Authentication',
                'description' => 'Single Sign-On authentication',
                'type' => 'boolean',
                'default_value' => 'false',
                'group' => 'Security',
                'sort_order' => 1,
            ],
            [
                'name' => 'api_access',
                'display_name' => 'API Access',
                'description' => 'Access to REST API',
                'type' => 'boolean',
                'default_value' => 'true',
                'group' => 'API Access',
                'sort_order' => 3,
            ],
            
            // Package/Tier Features
            [
                'name' => 'support_tier',
                'display_name' => 'Support Tier',
                'description' => 'Level of customer support',
                'type' => 'package',
                'default_value' => 'basic',
                'group' => 'Support',
                'sort_order' => 2,
                'metadata' => json_encode([
                    'options' => ['basic', 'standard', 'premium', 'enterprise']
                ]),
            ],
        ];

        foreach ($features as $featureData) {
            Feature::create($featureData);
        }

        // Now assign features to plans
        $this->assignFeaturesToPlans();
    }

    /**
     * Assign features to existing plans.
     */
    protected function assignFeaturesToPlans(): void
    {
        // Get all plans and features
        $freePlan = Plan::where('name', 'Free')->first();
        $basicPlan = Plan::where('name', 'Basic')->first();
        $premiumPlan = Plan::where('name', 'Premium')->first();
        $enterprisePlan = Plan::where('name', 'Enterprise')->first();

        // If no plans exist, create sample plans
        if (!$freePlan) {
            $freePlan = Plan::create([
                'name' => 'Free',
                'description' => 'Perfect for getting started',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'trial_days' => 0,
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        if (!$basicPlan) {
            $basicPlan = Plan::create([
                'name' => 'Basic',
                'description' => 'Great for individuals and small teams',
                'price' => 19.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 2,
            ]);
        }

        if (!$premiumPlan) {
            $premiumPlan = Plan::create([
                'name' => 'Premium',
                'description' => 'For growing businesses',
                'price' => 49.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'is_active' => true,
                'sort_order' => 3,
            ]);
        }

        if (!$enterprisePlan) {
            $enterprisePlan = Plan::create([
                'name' => 'Enterprise',
                'description' => 'For large organizations',
                'price' => 199.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 30,
                'is_active' => true,
                'sort_order' => 4,
            ]);
        }

        // Assign features to Free Plan
        $this->assignPlanFeatures($freePlan, [
            'storage_gb' => ['value' => '5', 'is_highlighted' => true],
            'file_uploads' => ['value' => '100'],
            'api_calls_monthly' => ['value' => '1000'],
            'api_rate_limit' => ['value' => '60'],
            'team_members' => ['value' => '3', 'is_highlighted' => true],
            'guest_users' => ['value' => '5'],
            'projects' => ['value' => '3', 'is_highlighted' => true],
            'advanced_analytics' => ['value' => 'false'],
            'custom_branding' => ['value' => 'false'],
            'priority_support' => ['value' => 'false'],
            'sso_authentication' => ['value' => 'false'],
            'api_access' => ['value' => 'true'],
            'support_tier' => ['value' => 'basic'],
        ]);

        // Assign features to Basic Plan
        $this->assignPlanFeatures($basicPlan, [
            'storage_gb' => ['value' => '50', 'is_highlighted' => true],
            'file_uploads' => ['value' => '1000'],
            'api_calls_monthly' => ['value' => '10000', 'is_highlighted' => true],
            'api_rate_limit' => ['value' => '120'],
            'team_members' => ['value' => '10', 'is_highlighted' => true],
            'guest_users' => ['value' => '20'],
            'projects' => ['value' => '10', 'is_highlighted' => true],
            'advanced_analytics' => ['value' => 'true', 'is_highlighted' => true],
            'custom_branding' => ['value' => 'false'],
            'priority_support' => ['value' => 'false'],
            'sso_authentication' => ['value' => 'false'],
            'api_access' => ['value' => 'true'],
            'support_tier' => ['value' => 'standard'],
        ]);

        // Assign features to Premium Plan
        $this->assignPlanFeatures($premiumPlan, [
            'storage_gb' => ['value' => '200', 'is_highlighted' => true],
            'file_uploads' => ['value' => '5000'],
            'api_calls_monthly' => ['value' => '100000', 'is_highlighted' => true],
            'api_rate_limit' => ['value' => '300'],
            'team_members' => ['value' => '50', 'is_highlighted' => true],
            'guest_users' => ['value' => '100'],
            'projects' => ['value' => 'unlimited', 'is_highlighted' => true],
            'advanced_analytics' => ['value' => 'true'],
            'custom_branding' => ['value' => 'true', 'is_highlighted' => true],
            'priority_support' => ['value' => 'true', 'is_highlighted' => true],
            'sso_authentication' => ['value' => 'false'],
            'api_access' => ['value' => 'true'],
            'support_tier' => ['value' => 'premium'],
        ]);

        // Assign features to Enterprise Plan
        $this->assignPlanFeatures($enterprisePlan, [
            'storage_gb' => ['value' => 'unlimited', 'is_highlighted' => true],
            'file_uploads' => ['value' => 'unlimited'],
            'api_calls_monthly' => ['value' => 'unlimited', 'is_highlighted' => true],
            'api_rate_limit' => ['value' => '1000'],
            'team_members' => ['value' => 'unlimited', 'is_highlighted' => true],
            'guest_users' => ['value' => 'unlimited'],
            'projects' => ['value' => 'unlimited', 'is_highlighted' => true],
            'advanced_analytics' => ['value' => 'true'],
            'custom_branding' => ['value' => 'true'],
            'priority_support' => ['value' => 'true', 'is_highlighted' => true],
            'sso_authentication' => ['value' => 'true', 'is_highlighted' => true],
            'api_access' => ['value' => 'true'],
            'support_tier' => ['value' => 'enterprise', 'is_highlighted' => true],
        ]);
    }

    /**
     * Helper to assign features to a plan.
     */
    protected function assignPlanFeatures(Plan $plan, array $features): void
    {
        foreach ($features as $featureName => $config) {
            $feature = Feature::where('name', $featureName)->first();
            
            if ($feature) {
                $plan->planFeatures()->attach($feature->id, [
                    'value' => $config['value'],
                    'is_highlighted' => $config['is_highlighted'] ?? false,
                    'sort_order' => $config['sort_order'] ?? 0,
                    'notes' => $config['notes'] ?? null,
                ]);
            }
        }
    }
}
