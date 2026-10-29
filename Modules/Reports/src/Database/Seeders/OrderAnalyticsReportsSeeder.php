<?php

namespace Modules\Reports\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderAnalyticsReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = DB::table('admins')->first();

        if (!$admin) {
            $this->command->warn('Order analytics seeder skipped: no admin user found.');
            return;
        }

        $adminId = $admin->id;
        $now = Carbon::now();

        $reports = $this->getOrderReports($now);

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

        $this->command->info('Order analytics reports seeded: ' . count($reports));
    }


    /**
     * Get order analytics reports configuration
     */
    private function getOrderReports(Carbon $now): array
    {
        return [
            // Revenue & Sales Reports
            [
                'name' => 'Daily Revenue Trend',
                'slug' => 'daily-revenue-trend',
                'description' => 'Track daily revenue to monitor sales performance and identify trends.',
                'data_source' => 'orders',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'DATE(created_at)', 'alias' => 'Date'],
                    ['name' => 'total', 'alias' => 'Revenue', 'aggregation' => 'SUM'],
                    ['name' => 'id', 'alias' => 'Orders', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'status', 'operator' => 'in', 'value' => ['Z', 'P']],
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['SUM', 'COUNT']),
                'group_by' => json_encode(['DATE(created_at)']),
                'order_by' => json_encode([
                    ['column' => 'DATE(created_at)', 'direction' => 'asc'],
                ]),
                'limit' => 30,
                'chart_type' => 'line',
                'chart_config' => json_encode([
                    'curve' => 'smooth',
                    'colors' => ['#10B981', '#3B82F6'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Revenue',
                    'tags' => ['revenue', 'daily', 'sales', 'kpi'],
                ]),
            ],

            [
                'name' => 'Monthly Revenue Summary',
                'slug' => 'monthly-revenue-summary',
                'description' => 'Month-over-month revenue comparison to track business growth.',
                'data_source' => 'orders',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))', 'alias' => 'Month'],
                    ['name' => 'total', 'alias' => 'Total Revenue', 'aggregation' => 'SUM'],
                    ['name' => 'id', 'alias' => 'Total Orders', 'aggregation' => 'COUNT'],
                    ['name' => 'total', 'alias' => 'Avg Order Value', 'aggregation' => 'AVG'],
                ]),
                'filters' => json_encode([
                    ['column' => 'status', 'operator' => 'in', 'value' => ['Z', 'P']],
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subYear()->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['SUM', 'COUNT', 'AVG']),
                'group_by' => json_encode(['CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))']),
                'order_by' => json_encode([
                    ['column' => 'CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))', 'direction' => 'asc'],
                ]),
                'limit' => 12,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => false,
                    'colors' => ['#10B981'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Revenue',
                    'tags' => ['revenue', 'monthly', 'growth'],
                ]),
            ],

            // Order Status & Performance Reports
            [
                'name' => 'Order Status Distribution',
                'slug' => 'order-status-distribution',
                'description' => 'Breakdown of orders by status to monitor order lifecycle.',
                'data_source' => 'orders',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'status', 'alias' => 'Status'],
                    ['name' => 'id', 'alias' => 'Count', 'aggregation' => 'COUNT'],
                    ['name' => 'total', 'alias' => 'Total Value', 'aggregation' => 'SUM'],
                ]),
                'filters' => json_encode([
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT', 'SUM']),
                'group_by' => json_encode(['status']),
                'order_by' => json_encode([
                    ['column' => 'COUNT(id)', 'direction' => 'desc'],
                ]),
                'limit' => 20,
                'chart_type' => 'donut',
                'chart_config' => json_encode([
                    'show_legend' => true,
                    'colors' => ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#6B7280'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Orders',
                    'tags' => ['status', 'distribution', 'lifecycle'],
                ]),
            ],

            [
                'name' => 'Order Type Performance',
                'slug' => 'order-type-performance',
                'description' => 'Compare performance across delivery, takeaway, and dine-in orders.',
                'data_source' => 'orders',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'order_type', 'alias' => 'Order Type'],
                    ['name' => 'id', 'alias' => 'Total Orders', 'aggregation' => 'COUNT'],
                    ['name' => 'total', 'alias' => 'Revenue', 'aggregation' => 'SUM'],
                    ['name' => 'total', 'alias' => 'Avg Order Value', 'aggregation' => 'AVG'],
                ]),
                'filters' => json_encode([
                    ['column' => 'status', 'operator' => 'in', 'value' => ['Z', 'P']],
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT', 'SUM', 'AVG']),
                'group_by' => json_encode(['order_type']),
                'order_by' => json_encode([
                    ['column' => 'SUM(total)', 'direction' => 'desc'],
                ]),
                'limit' => 10,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => true,
                    'colors' => ['#3B82F6', '#10B981', '#F59E0B'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Orders',
                    'tags' => ['order-type', 'performance', 'comparison'],
                ]),
            ],

            // Payment & Transaction Reports
            // [
            //     'name' => 'Payment Method Analysis',
            //     'slug' => 'payment-method-analysis',
            //     'description' => 'Analyze payment method preferences and transaction success rates.',
            //     'data_source' => 'orders',
            //     'data_source_type' => 'table',
            //     'custom_query' => null,
            //     'columns' => json_encode([
            //         ['name' => 'payment_method', 'alias' => 'Payment Method'],
            //         ['name' => 'id', 'alias' => 'Orders', 'aggregation' => 'COUNT'],
            //         ['name' => 'total', 'alias' => 'Revenue', 'aggregation' => 'SUM'],
            //     ]),
            //     'filters' => json_encode([
            //         ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
            //     ]),
            //     'filter_logic' => 'AND',
            //     'aggregations' => json_encode(['COUNT', 'SUM']),
            //     'group_by' => json_encode(['payment_method']),
            //     'order_by' => json_encode([
            //         ['column' => 'COUNT(id)', 'direction' => 'desc'],
            //     ]),
            //     'limit' => 10,
            //     'chart_type' => 'pie',
            //     'chart_config' => json_encode([
            //         'show_legend' => true,
            //         'colors' => ['#10B981', '#3B82F6', '#F59E0B', '#8B5CF6'],
            //     ]),
            //     'metadata' => json_encode([
            //         'category' => 'Payments',
            //         'tags' => ['payment', 'method', 'analysis'],
            //     ]),
            // ],

            // [
            //     'name' => 'Payment Status Overview',
            //     'slug' => 'payment-status-overview',
            //     'description' => 'Monitor payment completion rates and identify payment issues.',
            //     'data_source' => 'orders',
            //     'data_source_type' => 'table',
            //     'custom_query' => null,
            //     'columns' => json_encode([
            //         ['name' => 'payment_status', 'alias' => 'Payment Status'],
            //         ['name' => 'id', 'alias' => 'Orders', 'aggregation' => 'COUNT'],
            //         ['name' => 'total', 'alias' => 'Amount', 'aggregation' => 'SUM'],
            //     ]),
            //     'filters' => json_encode([
            //         ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
            //     ]),
            //     'filter_logic' => 'AND',
            //     'aggregations' => json_encode(['COUNT', 'SUM']),
            //     'group_by' => json_encode(['payment_status']),
            //     'order_by' => json_encode([
            //         ['column' => 'COUNT(id)', 'direction' => 'desc'],
            //     ]),
            //     'limit' => 10,
            //     'chart_type' => 'donut',
            //     'chart_config' => json_encode([
            //         'show_legend' => true,
            //         'colors' => ['#10B981', '#F59E0B', '#EF4444', '#6B7280'],
            //     ]),
            //     'metadata' => json_encode([
            //         'category' => 'Payments',
            //         'tags' => ['payment', 'status', 'completion'],
            //     ]),
            // ],

            // Vendor Performance Reports
            [
                'name' => 'Top Performing Vendors',
                'slug' => 'top-performing-vendors',
                'description' => 'Identify top vendors by order volume and revenue generation.',
                'data_source' => 'orders',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'vendor_id', 'alias' => 'Vendor ID'],
                    ['name' => 'id', 'alias' => 'Total Orders', 'aggregation' => 'COUNT'],
                    ['name' => 'total', 'alias' => 'Revenue', 'aggregation' => 'SUM'],
                    ['name' => 'total', 'alias' => 'Avg Order Value', 'aggregation' => 'AVG'],
                ]),
                'filters' => json_encode([
                    ['column' => 'status', 'operator' => 'in', 'value' => ['Z', 'P']],
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT', 'SUM', 'AVG']),
                'group_by' => json_encode(['vendor_id']),
                'order_by' => json_encode([
                    ['column' => 'SUM(total)', 'direction' => 'desc'],
                ]),
                'limit' => 20,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => true,
                    'colors' => ['#10B981'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Vendors',
                    'tags' => ['vendor', 'performance', 'top'],
                ]),
            ],

            [
                'name' => 'Vendor Order Fulfillment Rate',
                'slug' => 'vendor-order-fulfillment-rate',
                'description' => 'Track vendor order completion vs cancellation rates.',
                'data_source' => 'orders',
                'data_source_type' => 'custom_query',
                'custom_query' => "SELECT vendor_id, COUNT(*) as total_orders, SUM(CASE WHEN status = 'Z' THEN 1 ELSE 0 END) as completed, SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) as cancelled, ROUND((SUM(CASE WHEN status = 'Z' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as fulfillment_rate FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY vendor_id ORDER BY fulfillment_rate DESC",
                'columns' => json_encode([
                    ['name' => 'vendor_id', 'alias' => 'Vendor ID'],
                    ['name' => 'total_orders', 'alias' => 'Total Orders'],
                    ['name' => 'completed', 'alias' => 'Completed'],
                    ['name' => 'cancelled', 'alias' => 'Cancelled'],
                    ['name' => 'fulfillment_rate', 'alias' => 'Fulfillment Rate (%)'],
                ]),
                'filters' => json_encode([]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode([]),
                'group_by' => json_encode([]),
                'order_by' => json_encode([
                    ['column' => 'fulfillment_rate', 'direction' => 'desc'],
                ]),
                'limit' => 20,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => true,
                    'colors' => ['#3B82F6'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Vendors',
                    'tags' => ['vendor', 'fulfillment', 'quality'],
                ]),
            ],

            // Customer Insights Reports
            [
                'name' => 'Customer Order Frequency',
                'slug' => 'customer-order-frequency',
                'description' => 'Analyze customer ordering patterns and identify loyal customers.',
                'data_source' => 'orders',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'customer_id', 'alias' => 'Customer ID'],
                    ['name' => 'id', 'alias' => 'Total Orders', 'aggregation' => 'COUNT'],
                    ['name' => 'total', 'alias' => 'Total Spent', 'aggregation' => 'SUM'],
                    ['name' => 'total', 'alias' => 'Avg Order Value', 'aggregation' => 'AVG'],
                ]),
                'filters' => json_encode([
                    ['column' => 'status', 'operator' => 'in', 'value' => ['Z', 'P']],
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(90)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT', 'SUM', 'AVG']),
                'group_by' => json_encode(['customer_id']),
                'order_by' => json_encode([
                    ['column' => 'COUNT(id)', 'direction' => 'desc'],
                ]),
                'limit' => 50,
                'chart_type' => 'table',
                'chart_config' => json_encode([]),
                'metadata' => json_encode([
                    'category' => 'Customers',
                    'tags' => ['customer', 'frequency', 'loyalty'],
                ]),
            ],

            [
                'name' => 'New vs Returning Customers',
                'slug' => 'new-vs-returning-customers',
                'description' => 'Track customer acquisition and retention metrics.',
                'data_source' => 'orders',
                'data_source_type' => 'custom_query',
                'custom_query' => "SELECT DATE(o.created_at) as order_date, COUNT(DISTINCT CASE WHEN first_order.first_order_date = DATE(o.created_at) THEN o.customer_id END) as new_customers, COUNT(DISTINCT CASE WHEN first_order.first_order_date < DATE(o.created_at) THEN o.customer_id END) as returning_customers FROM orders o LEFT JOIN (SELECT customer_id, MIN(DATE(created_at)) as first_order_date FROM orders GROUP BY customer_id) first_order ON o.customer_id = first_order.customer_id WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(o.created_at) ORDER BY order_date ASC",
                'columns' => json_encode([
                    ['name' => 'order_date', 'alias' => 'Date'],
                    ['name' => 'new_customers', 'alias' => 'New Customers'],
                    ['name' => 'returning_customers', 'alias' => 'Returning Customers'],
                ]),
                'filters' => json_encode([]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode([]),
                'group_by' => json_encode([]),
                'order_by' => json_encode([
                    ['column' => 'order_date', 'direction' => 'asc'],
                ]),
                'limit' => 30,
                'chart_type' => 'line',
                'chart_config' => json_encode([
                    'curve' => 'smooth',
                    'colors' => ['#10B981', '#3B82F6'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Customers',
                    'tags' => ['customer', 'acquisition', 'retention'],
                ]),
            ],

            // Operational Reports
            [
                'name' => 'Hourly Order Volume',
                'slug' => 'hourly-order-volume',
                'description' => 'Identify peak ordering hours for resource planning.',
                'data_source' => 'orders',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'HOUR(created_at)', 'alias' => 'Hour'],
                    ['name' => 'id', 'alias' => 'Orders', 'aggregation' => 'COUNT'],
                    ['name' => 'total', 'alias' => 'Revenue', 'aggregation' => 'SUM'],
                ]),
                'filters' => json_encode([
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(7)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT', 'SUM']),
                'group_by' => json_encode(['HOUR(created_at)']),
                'order_by' => json_encode([
                    ['column' => 'HOUR(created_at)', 'direction' => 'asc'],
                ]),
                'limit' => 24,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => false,
                    'colors' => ['#3B82F6'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Operations',
                    'tags' => ['hourly', 'volume', 'peak-hours'],
                ]),
            ],

            [
                'name' => 'Day of Week Analysis',
                'slug' => 'day-of-week-analysis',
                'description' => 'Compare order volume and revenue across different days of the week.',
                'data_source' => 'orders',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'DAYNAME(created_at)', 'alias' => 'Day'],
                    ['name' => 'id', 'alias' => 'Orders', 'aggregation' => 'COUNT'],
                    ['name' => 'total', 'alias' => 'Revenue', 'aggregation' => 'SUM'],
                    ['name' => 'total', 'alias' => 'Avg Order Value', 'aggregation' => 'AVG'],
                ]),
                'filters' => json_encode([
                    ['column' => 'status', 'operator' => 'in', 'value' => ['Z', 'P']],
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT', 'SUM', 'AVG']),
                'group_by' => json_encode(['DAYNAME(created_at)']),
                'order_by' => json_encode([
                    ['column' => 'COUNT(id)', 'direction' => 'desc'],
                ]),
                'limit' => 7,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => false,
                    'colors' => ['#10B981', '#3B82F6'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Operations',
                    'tags' => ['weekday', 'pattern', 'analysis'],
                ]),
            ],

            // Cancellation & Issues Reports
            [
                'name' => 'Order Cancellation Analysis',
                'slug' => 'order-cancellation-analysis',
                'description' => 'Track cancellation trends and identify reasons for order cancellations.',
                'data_source' => 'orders',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'cancellation_reason', 'alias' => 'Cancellation Reason'],
                    ['name' => 'id', 'alias' => 'Count', 'aggregation' => 'COUNT'],
                    ['name' => 'total', 'alias' => 'Lost Revenue', 'aggregation' => 'SUM'],
                ]),
                'filters' => json_encode([
                    ['column' => 'status', 'operator' => 'equals', 'value' => 'C'],
                    ['column' => 'cancellation_reason', 'operator' => 'is_not_null'],
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT', 'SUM']),
                'group_by' => json_encode(['cancellation_reason']),
                'order_by' => json_encode([
                    ['column' => 'COUNT(id)', 'direction' => 'desc'],
                ]),
                'limit' => 15,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => true,
                    'colors' => ['#EF4444'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Quality',
                    'tags' => ['cancellation', 'reasons', 'issues'],
                ]),
            ],

            [
                'name' => 'Failed Orders Report',
                'slug' => 'failed-orders-report',
                'description' => 'Monitor failed orders to identify and resolve payment or system issues.',
                'data_source' => 'orders',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'DATE(created_at)', 'alias' => 'Date'],
                    ['name' => 'id', 'alias' => 'Failed Orders', 'aggregation' => 'COUNT'],
                    ['name' => 'total', 'alias' => 'Lost Revenue', 'aggregation' => 'SUM'],
                ]),
                'filters' => json_encode([
                    ['column' => 'status', 'operator' => 'equals', 'value' => 'F'],
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT', 'SUM']),
                'group_by' => json_encode(['DATE(created_at)']),
                'order_by' => json_encode([
                    ['column' => 'DATE(created_at)', 'direction' => 'asc'],
                ]),
                'limit' => 30,
                'chart_type' => 'line',
                'chart_config' => json_encode([
                    'curve' => 'smooth',
                    'colors' => ['#EF4444'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Quality',
                    'tags' => ['failed', 'issues', 'monitoring'],
                ]),
            ],

            // Delivery & Logistics Reports
            [
                'name' => 'Delivery Performance Metrics',
                'slug' => 'delivery-performance-metrics',
                'description' => 'Track delivery times and identify delays in order fulfillment.',
                'data_source' => 'orders',
                'data_source_type' => 'custom_query',
                'custom_query' => "SELECT DATE(created_at) as order_date, COUNT(*) as total_deliveries, AVG(TIMESTAMPDIFF(MINUTE, estimated_delivery_time, actual_delivery_time)) as avg_delay_minutes, SUM(CASE WHEN actual_delivery_time <= estimated_delivery_time THEN 1 ELSE 0 END) as on_time_deliveries, ROUND((SUM(CASE WHEN actual_delivery_time <= estimated_delivery_time THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as on_time_percentage FROM orders WHERE order_type = 'delivery' AND status = 'Z' AND actual_delivery_time IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY order_date ASC",
                'columns' => json_encode([
                    ['name' => 'order_date', 'alias' => 'Date'],
                    ['name' => 'total_deliveries', 'alias' => 'Total Deliveries'],
                    ['name' => 'on_time_deliveries', 'alias' => 'On-Time'],
                    ['name' => 'avg_delay_minutes', 'alias' => 'Avg Delay (mins)'],
                    ['name' => 'on_time_percentage', 'alias' => 'On-Time %'],
                ]),
                'filters' => json_encode([]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode([]),
                'group_by' => json_encode([]),
                'order_by' => json_encode([
                    ['column' => 'order_date', 'direction' => 'asc'],
                ]),
                'limit' => 30,
                'chart_type' => 'line',
                'chart_config' => json_encode([
                    'curve' => 'smooth',
                    'colors' => ['#10B981', '#EF4444'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Logistics',
                    'tags' => ['delivery', 'performance', 'timing'],
                ]),
            ],

            // Discount & Coupon Reports
            // [
            //     'name' => 'Coupon Usage Analysis',
            //     'slug' => 'coupon-usage-analysis',
            //     'description' => 'Track coupon code usage and discount impact on revenue.',
            //     'data_source' => 'orders',
            //     'data_source_type' => 'table',
            //     'custom_query' => null,
            //     'columns' => json_encode([
            //         ['name' => 'coupon_code', 'alias' => 'Coupon Code'],
            //         ['name' => 'id', 'alias' => 'Times Used', 'aggregation' => 'COUNT'],
            //         ['name' => 'discount', 'alias' => 'Total Discount', 'aggregation' => 'SUM'],
            //         ['name' => 'total', 'alias' => 'Revenue Generated', 'aggregation' => 'SUM'],
            //     ]),
            //     'filters' => json_encode([
            //         ['column' => 'coupon_code', 'operator' => 'is_not_null'],
            //         ['column' => 'status', 'operator' => 'in', 'value' => ['Z', 'P']],
            //         ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
            //     ]),
            //     'filter_logic' => 'AND',
            //     'aggregations' => json_encode(['COUNT', 'SUM']),
            //     'group_by' => json_encode(['coupon_code']),
            //     'order_by' => json_encode([
            //         ['column' => 'COUNT(id)', 'direction' => 'desc'],
            //     ]),
            //     'limit' => 20,
            //     'chart_type' => 'table',
            //     'chart_config' => json_encode([]),
            //     'metadata' => json_encode([
            //         'category' => 'Marketing',
            //         'tags' => ['coupon', 'discount', 'promotion'],
            //     ]),
            // ],

            // [
            //     'name' => 'Discount Impact on Revenue',
            //     'slug' => 'discount-impact-on-revenue',
            //     'description' => 'Analyze the relationship between discounts and order volume.',
            //     'data_source' => 'orders',
            //     'data_source_type' => 'table',
            //     'custom_query' => null,
            //     'columns' => json_encode([
            //         ['name' => 'DATE(created_at)', 'alias' => 'Date'],
            //         ['name' => 'discount', 'alias' => 'Total Discounts', 'aggregation' => 'SUM'],
            //         ['name' => 'total', 'alias' => 'Revenue', 'aggregation' => 'SUM'],
            //         ['name' => 'id', 'alias' => 'Orders', 'aggregation' => 'COUNT'],
            //     ]),
            //     'filters' => json_encode([
            //         ['column' => 'status', 'operator' => 'in', 'value' => ['Z', 'P']],
            //         ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
            //     ]),
            //     'filter_logic' => 'AND',
            //     'aggregations' => json_encode(['SUM', 'COUNT']),
            //     'group_by' => json_encode(['DATE(created_at)']),
            //     'order_by' => json_encode([
            //         ['column' => 'DATE(created_at)', 'direction' => 'asc'],
            //     ]),
            //     'limit' => 30,
            //     'chart_type' => 'line',
            //     'chart_config' => json_encode([
            //         'curve' => 'smooth',
            //         'colors' => ['#F59E0B', '#10B981'],
            //     ]),
            //     'metadata' => json_encode([
            //         'category' => 'Marketing',
            //         'tags' => ['discount', 'revenue', 'impact'],
            //     ]),
            // ],

            // Average Order Value Reports
            [
                'name' => 'Average Order Value Trend',
                'slug' => 'average-order-value-trend',
                'description' => 'Monitor changes in average order value over time.',
                'data_source' => 'orders',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'DATE(created_at)', 'alias' => 'Date'],
                    ['name' => 'total', 'alias' => 'Avg Order Value', 'aggregation' => 'AVG'],
                    ['name' => 'id', 'alias' => 'Orders', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'status', 'operator' => 'in', 'value' => ['Z', 'P']],
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['AVG', 'COUNT']),
                'group_by' => json_encode(['DATE(created_at)']),
                'order_by' => json_encode([
                    ['column' => 'DATE(created_at)', 'direction' => 'asc'],
                ]),
                'limit' => 30,
                'chart_type' => 'line',
                'chart_config' => json_encode([
                    'curve' => 'smooth',
                    'colors' => ['#8B5CF6'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Revenue',
                    'tags' => ['aov', 'average', 'trend'],
                ]),
            ],

            // Order Items Analysis
            [
                'name' => 'Top Selling Items',
                'slug' => 'top-selling-items',
                'description' => 'Identify best-selling items by quantity and revenue.',
                'data_source' => 'order_items',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'item_name', 'alias' => 'Item Name'],
                    ['name' => 'quantity', 'alias' => 'Total Quantity', 'aggregation' => 'SUM'],
                    ['name' => 'total_price', 'alias' => 'Revenue', 'aggregation' => 'SUM'],
                    ['name' => 'id', 'alias' => 'Times Ordered', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['SUM', 'COUNT']),
                'group_by' => json_encode(['item_name']),
                'order_by' => json_encode([
                    ['column' => 'SUM(quantity)', 'direction' => 'desc'],
                ]),
                'limit' => 20,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => true,
                    'colors' => ['#10B981'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Products',
                    'tags' => ['items', 'bestsellers', 'products'],
                ]),
            ],

            [
                'name' => 'Order Items Performance by Vendor',
                'slug' => 'order-items-performance-by-vendor',
                'description' => 'Analyze item sales performance across different vendors.',
                'data_source' => 'order_items',
                'data_source_type' => 'custom_query',
                'custom_query' => "SELECT oi.item_name, o.vendor_id, SUM(oi.quantity) as total_quantity, SUM(oi.total_price) as revenue, COUNT(DISTINCT o.id) as order_count FROM order_items oi INNER JOIN orders o ON oi.order_id = o.id WHERE o.status IN ('Z', 'P') AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY oi.item_name, o.vendor_id ORDER BY revenue DESC",
                'columns' => json_encode([
                    ['name' => 'item_name', 'alias' => 'Item Name'],
                    ['name' => 'vendor_id', 'alias' => 'Vendor ID'],
                    ['name' => 'total_quantity', 'alias' => 'Quantity Sold'],
                    ['name' => 'revenue', 'alias' => 'Revenue'],
                    ['name' => 'order_count', 'alias' => 'Orders'],
                ]),
                'filters' => json_encode([]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode([]),
                'group_by' => json_encode([]),
                'order_by' => json_encode([
                    ['column' => 'revenue', 'direction' => 'desc'],
                ]),
                'limit' => 50,
                'chart_type' => 'table',
                'chart_config' => json_encode([]),
                'metadata' => json_encode([
                    'category' => 'Products',
                    'tags' => ['items', 'vendor', 'performance'],
                ]),
            ],
        ];
    }
}
