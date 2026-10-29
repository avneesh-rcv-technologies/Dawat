<?php

namespace Modules\Reports\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestaurantAnalyticsReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = DB::table('admins')->first();

        if (!$admin) {
            $this->command->warn('Restaurant analytics seeder skipped: no admin user found.');
            return;
        }

        $adminId = $admin->id;
        $now = Carbon::now();

        $reports = $this->getRestaurantReports($now);

        foreach ($reports as $report) {
            DB::table('reports')->updateOrInsert(
                ['slug' => $report['slug']],
                array_merge($report, [
                    'guards' => json_encode(['admin']),
                    'created_by_id' => $adminId,
                    'created_by_type' => 'Modules\\Acl\\Models\\Admin',
                    'is_public' => true,
                    'allowed_roles' => json_encode([]),
                    'allowed_users' => json_encode([]),
                    'is_active' => true,
                    'is_scheduled' => false,
                    'execution_count' => 0,
                    'last_executed_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }

        $this->command->info('Restaurant analytics reports seeded: ' . count($reports));
    }

    /**
     * Get restaurant analytics reports configuration
     */
    private function getRestaurantReports(Carbon $now): array
    {
        return [
            // Top Revenue Restaurants
            [
                'name' => 'Top Revenue Generating Restaurants',
                'slug' => 'top-revenue-generating-restaurants',
                'description' => 'Highest earning restaurants by revenue.',
                'data_source' => 'orders',
                'data_source_type' => 'custom_query',
                'custom_query' => "SELECT r.id as restaurant_id, r.name as restaurant_name, COUNT(o.id) as total_orders, SUM(o.total) as total_revenue FROM restaurants r INNER JOIN orders o ON o.vendor_id = r.id AND o.vendor_type = 'Modules\\\\Restaurant\\\\Models\\\\Restaurant' WHERE o.status IN ('Z', 'P') AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY r.id, r.name ORDER BY total_revenue DESC",
                'columns' => json_encode([
                    ['name' => 'restaurant_id', 'alias' => 'Restaurant ID'],
                    ['name' => 'restaurant_name', 'alias' => 'Restaurant'],
                    ['name' => 'total_orders', 'alias' => 'Orders'],
                    ['name' => 'total_revenue', 'alias' => 'Revenue'],
                ]),
                'filters' => json_encode([]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode([]),
                'group_by' => json_encode([]),
                'order_by' => json_encode([
                    ['column' => 'total_revenue', 'direction' => 'desc'],
                ]),
                'limit' => 20,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => true,
                    'colors' => ['#10B981'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Restaurants',
                    'tags' => ['revenue', 'top-performers'],
                ]),
            ],

            // Restaurant Status
            [
                'name' => 'Restaurant Status Overview',
                'slug' => 'restaurant-status-overview',
                'description' => 'Distribution of restaurants by status.',
                'data_source' => 'restaurants',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'status', 'alias' => 'Status'],
                    ['name' => 'id', 'alias' => 'Count', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT']),
                'group_by' => json_encode(['status']),
                'order_by' => json_encode([
                    ['column' => 'COUNT(id)', 'direction' => 'desc'],
                ]),
                'limit' => 10,
                'chart_type' => 'donut',
                'chart_config' => json_encode([
                    'show_legend' => true,
                    'colors' => ['#10B981', '#EF4444', '#F59E0B'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Restaurants',
                    'tags' => ['status', 'operations'],
                ]),
            ],

            // Restaurant Performance
            [
                'name' => 'Restaurant Order Fulfillment Rate',
                'slug' => 'restaurant-order-fulfillment-rate',
                'description' => 'Track order completion rates by restaurant.',
                'data_source' => 'orders',
                'data_source_type' => 'custom_query',
                'custom_query' => "SELECT r.id as restaurant_id, r.name as restaurant_name, COUNT(o.id) as total_orders, SUM(CASE WHEN o.status = 'Z' THEN 1 ELSE 0 END) as completed_orders, ROUND((SUM(CASE WHEN o.status = 'Z' THEN 1 ELSE 0 END) / COUNT(o.id)) * 100, 2) as completion_rate FROM restaurants r INNER JOIN orders o ON o.vendor_id = r.id AND o.vendor_type = 'Modules\\\\Restaurant\\\\Models\\\\Restaurant' WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY r.id, r.name HAVING total_orders > 0 ORDER BY completion_rate DESC",
                'columns' => json_encode([
                    ['name' => 'restaurant_id', 'alias' => 'Restaurant ID'],
                    ['name' => 'restaurant_name', 'alias' => 'Restaurant'],
                    ['name' => 'total_orders', 'alias' => 'Total Orders'],
                    ['name' => 'completed_orders', 'alias' => 'Completed'],
                    ['name' => 'completion_rate', 'alias' => 'Completion Rate (%)'],
                ]),
                'filters' => json_encode([]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode([]),
                'group_by' => json_encode([]),
                'order_by' => json_encode([
                    ['column' => 'completion_rate', 'direction' => 'desc'],
                ]),
                'limit' => 20,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => true,
                    'colors' => ['#10B981'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Restaurants',
                    'tags' => ['fulfillment', 'performance'],
                ]),
            ],
        ];
    }
}
