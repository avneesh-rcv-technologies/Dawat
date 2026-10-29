<?php

namespace Modules\Reports\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerAnalyticsReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = DB::table('admins')->first();

        if (!$admin) {
            $this->command->warn('Customer analytics seeder skipped: no admin user found.');
            return;
        }

        $adminId = $admin->id;
        $now = Carbon::now();

        $reports = $this->getCustomerReports($now);

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

        $this->command->info('Customer analytics reports seeded: ' . count($reports));
    }

    /**
     * Get customer analytics reports configuration
     */
    private function getCustomerReports(Carbon $now): array
    {
        return [
            // Customer Registration Trend
            [
                'name' => 'Customer Registration Trend',
                'slug' => 'customer-registration-trend',
                'description' => 'Track daily customer registration trends.',
                'data_source' => 'customers',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'DATE(created_at)', 'alias' => 'Date'],
                    ['name' => 'id', 'alias' => 'New Customers', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT']),
                'group_by' => json_encode(['DATE(created_at)']),
                'order_by' => json_encode([
                    ['column' => 'DATE(created_at)', 'direction' => 'asc'],
                ]),
                'limit' => 30,
                'chart_type' => 'line',
                'chart_config' => json_encode([
                    'curve' => 'smooth',
                    'colors' => ['#10B981'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Customers',
                    'tags' => ['registration', 'growth'],
                ]),
            ],

            // Customer Status Distribution
            [
                'name' => 'Customer Status Distribution',
                'slug' => 'customer-status-distribution',
                'description' => 'Breakdown of customers by status.',
                'data_source' => 'customers',
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
                    'category' => 'Customers',
                    'tags' => ['status', 'distribution'],
                ]),
            ],

            // Top Spending Customers
            [
                'name' => 'Top Spending Customers',
                'slug' => 'top-spending-customers',
                'description' => 'Highest revenue generating customers.',
                'data_source' => 'orders',
                'data_source_type' => 'custom_query',
                'custom_query' => "SELECT c.id as customer_id, c.name as customer_name, c.email, COUNT(o.id) as total_orders, SUM(o.total) as total_spent FROM customers c INNER JOIN orders o ON o.customer_id = c.id AND o.customer_type = 'Modules\\\\Acl\\\\Models\\\\Customer' WHERE o.status IN ('Z', 'P') AND o.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY) GROUP BY c.id, c.name, c.email ORDER BY total_spent DESC",
                'columns' => json_encode([
                    ['name' => 'customer_id', 'alias' => 'Customer ID'],
                    ['name' => 'customer_name', 'alias' => 'Name'],
                    ['name' => 'email', 'alias' => 'Email'],
                    ['name' => 'total_orders', 'alias' => 'Orders'],
                    ['name' => 'total_spent', 'alias' => 'Total Spent'],
                ]),
                'filters' => json_encode([]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode([]),
                'group_by' => json_encode([]),
                'order_by' => json_encode([
                    ['column' => 'total_spent', 'direction' => 'desc'],
                ]),
                'limit' => 50,
                'chart_type' => 'table',
                'chart_config' => json_encode([]),
                'metadata' => json_encode([
                    'category' => 'Customers',
                    'tags' => ['spending', 'revenue'],
                ]),
            ],
        ];
    }
}
