<?php

namespace Modules\Reports\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessAnalyticsReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = DB::table('admins')->first();

        if (!$admin) {
            $this->command->warn('Business analytics seeder skipped: no admin user found.');
            return;
        }

        $adminId = $admin->id;
        $now = Carbon::now();

        $reports = [
            [
                'name' => 'Subscription Growth Trend',
                'slug' => 'subscription-growth-trend',
                'description' => 'Track month-over-month subscription growth to measure recurring demand.',
                'data_source' => 'subscriptions',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))', 'alias' => 'Month'],
                    ['name' => 'id', 'alias' => 'New Subscriptions', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subYear()->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT']),
                'group_by' => json_encode(['CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))']),
                'order_by' => json_encode([
                    ['column' => 'CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))', 'direction' => 'asc'],
                ]),
                'limit' => 12,
                'chart_type' => 'line',
                'chart_config' => json_encode([
                    'curve' => 'smooth',
                    'colors' => ['#2563EB'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Subscriptions',
                    'tags' => ['growth', 'monthly', 'kpi'],
                ]),
            ],
            [
                'name' => 'Subscription Status Distribution',
                'slug' => 'subscription-status-distribution',
                'description' => 'Understand active, trialing, paused, canceled, and expired subscription mix.',
                'data_source' => 'subscriptions',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'status', 'alias' => 'Status'],
                    ['name' => 'id', 'alias' => 'Total', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT']),
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
                    'category' => 'Subscriptions',
                    'tags' => ['status', 'distribution', 'retention'],
                ]),
            ],
            [
                'name' => 'Upcoming Renewal & Expiry Calendar',
                'slug' => 'upcoming-renewal-expiry-calendar',
                'description' => 'View subscriptions ending soon to support retention campaigns and renewal ops.',
                'data_source' => 'subscriptions',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'DATE(current_period_end)', 'alias' => 'Renewal Date'],
                    ['name' => 'id', 'alias' => 'Subscriptions Due', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'current_period_end', 'operator' => 'date_after', 'value' => $now->copy()->subDays(7)->format('Y-m-d')],
                    ['column' => 'current_period_end', 'operator' => 'date_before', 'value' => $now->copy()->addDays(45)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT']),
                'group_by' => json_encode(['DATE(current_period_end)']),
                'order_by' => json_encode([
                    ['column' => 'DATE(current_period_end)', 'direction' => 'asc'],
                ]),
                'limit' => 60,
                'chart_type' => 'area',
                'chart_config' => json_encode([
                    'fill_opacity' => 0.25,
                    'colors' => ['#0EA5E9'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Subscriptions',
                    'tags' => ['renewal', 'expiry', 'forecast'],
                ]),
            ],
            [
                'name' => 'Monthly Booking Demand',
                'slug' => 'monthly-booking-demand',
                'description' => 'Monitor booking demand trend by month for capacity and staffing planning.',
                'data_source' => 'bookings',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))', 'alias' => 'Month'],
                    ['name' => 'id', 'alias' => 'Bookings', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subYear()->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT']),
                'group_by' => json_encode(['CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))']),
                'order_by' => json_encode([
                    ['column' => 'CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))', 'direction' => 'asc'],
                ]),
                'limit' => 12,
                'chart_type' => 'line',
                'chart_config' => json_encode([
                    'curve' => 'smooth',
                    'colors' => ['#7C3AED'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Bookings',
                    'tags' => ['demand', 'trend', 'forecasting'],
                ]),
            ],
            [
                'name' => 'Booking Outcome Funnel',
                'slug' => 'booking-outcome-funnel',
                'description' => 'Analyze booking lifecycle outcomes (confirmed, completed, cancelled, no-show).',
                'data_source' => 'bookings',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'status', 'alias' => 'Booking Status'],
                    ['name' => 'id', 'alias' => 'Count', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subDays(90)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT']),
                'group_by' => json_encode(['status']),
                'order_by' => json_encode([
                    ['column' => 'COUNT(id)', 'direction' => 'desc'],
                ]),
                'limit' => 10,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => false,
                    'colors' => ['#22C55E', '#0EA5E9', '#F59E0B', '#EF4444', '#6B7280'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Bookings',
                    'tags' => ['funnel', 'conversion', 'operations'],
                ]),
            ],
            [
                'name' => 'Host Utilization Snapshot',
                'slug' => 'host-utilization-snapshot',
                'description' => 'Identify high-load hosts based on booking volume and average meeting duration.',
                'data_source' => 'bookings',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'host_user_id', 'alias' => 'Host ID'],
                    ['name' => 'id', 'alias' => 'Total Bookings', 'aggregation' => 'COUNT'],
                    ['name' => 'TIMESTAMPDIFF(MINUTE, start_time, end_time)', 'alias' => 'Avg Duration (mins)', 'aggregation' => 'AVG'],
                ]),
                'filters' => json_encode([
                    ['column' => 'start_time', 'operator' => 'date_after', 'value' => $now->copy()->subDays(30)->format('Y-m-d')],
                    ['column' => 'status', 'operator' => 'in', 'value' => ['confirmed', 'completed']],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT', 'AVG']),
                'group_by' => json_encode(['host_user_id']),
                'order_by' => json_encode([
                    ['column' => 'COUNT(id)', 'direction' => 'desc'],
                ]),
                'limit' => 20,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => true,
                    'colors' => ['#14B8A6'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Scheduling',
                    'tags' => ['utilization', 'hosts', 'capacity'],
                ]),
            ],
            [
                'name' => 'Event Type Portfolio Mix',
                'slug' => 'event-type-portfolio-mix',
                'description' => 'Measure scheduling portfolio mix across one-to-one, group, and round-robin event types.',
                'data_source' => 'event_types',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'type', 'alias' => 'Event Type'],
                    ['name' => 'id', 'alias' => 'Total Event Types', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'is_active', 'operator' => 'equals', 'value' => 1],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT']),
                'group_by' => json_encode(['type']),
                'order_by' => json_encode([
                    ['column' => 'COUNT(id)', 'direction' => 'desc'],
                ]),
                'limit' => 10,
                'chart_type' => 'pie',
                'chart_config' => json_encode([
                    'show_legend' => true,
                    'colors' => ['#3B82F6', '#8B5CF6', '#F97316'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Scheduling',
                    'tags' => ['event-types', 'portfolio', 'mix'],
                ]),
            ],
            [
                'name' => 'Average Session Duration by Event Type',
                'slug' => 'average-session-duration-by-event-type',
                'description' => 'Business view of default scheduled duration and volume by event type category.',
                'data_source' => 'event_types',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'type', 'alias' => 'Event Type'],
                    ['name' => 'duration', 'alias' => 'Avg Duration (mins)', 'aggregation' => 'AVG'],
                    ['name' => 'id', 'alias' => 'Type Count', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'is_active', 'operator' => 'equals', 'value' => 1],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['AVG', 'COUNT']),
                'group_by' => json_encode(['type']),
                'order_by' => json_encode([
                    ['column' => 'AVG(duration)', 'direction' => 'desc'],
                ]),
                'limit' => 10,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => false,
                    'colors' => ['#0EA5E9', '#22C55E'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Scheduling',
                    'tags' => ['duration', 'efficiency', 'planning'],
                ]),
            ],
            [
                'name' => 'Monthly Booking Completions',
                'slug' => 'monthly-booking-completions',
                'description' => 'Completed bookings trend by month to track delivery reliability.',
                'data_source' => 'bookings',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'CONCAT(YEAR(start_time), "-", LPAD(MONTH(start_time), 2, "0"))', 'alias' => 'Month'],
                    ['name' => 'id', 'alias' => 'Completed Bookings', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'status', 'operator' => 'equals', 'value' => 'completed'],
                    ['column' => 'start_time', 'operator' => 'date_after', 'value' => $now->copy()->subYear()->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT']),
                'group_by' => json_encode(['CONCAT(YEAR(start_time), "-", LPAD(MONTH(start_time), 2, "0"))']),
                'order_by' => json_encode([
                    ['column' => 'CONCAT(YEAR(start_time), "-", LPAD(MONTH(start_time), 2, "0"))', 'direction' => 'asc'],
                ]),
                'limit' => 12,
                'chart_type' => 'area',
                'chart_config' => json_encode([
                    'fill_opacity' => 0.35,
                    'curve' => 'smooth',
                    'colors' => ['#16A34A'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Bookings',
                    'tags' => ['completed', 'delivery', 'trend'],
                ]),
            ],
            [
                'name' => 'Cancellation Reasons Breakdown',
                'slug' => 'cancellation-reasons-breakdown',
                'description' => 'Top cancellation reasons to improve conversion and service quality.',
                'data_source' => 'bookings',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'cancellation_reason', 'alias' => 'Reason'],
                    ['name' => 'id', 'alias' => 'Cancellations', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'status', 'operator' => 'equals', 'value' => 'cancelled'],
                    ['column' => 'cancellation_reason', 'operator' => 'is_not_null'],
                    ['column' => 'created_at', 'operator' => 'date_after', 'value' => $now->copy()->subMonths(6)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT']),
                'group_by' => json_encode(['cancellation_reason']),
                'order_by' => json_encode([
                    ['column' => 'COUNT(id)', 'direction' => 'desc'],
                ]),
                'limit' => 10,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => true,
                    'colors' => ['#EF4444'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Bookings',
                    'tags' => ['cancellation', 'reasons', 'quality'],
                ]),
            ],
            [
                'name' => 'Booking Volume by Weekday',
                'slug' => 'booking-volume-by-weekday',
                'description' => 'Identify demand pattern by day of week for staff scheduling.',
                'data_source' => 'bookings',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'DAYNAME(start_time)', 'alias' => 'Weekday'],
                    ['name' => 'id', 'alias' => 'Bookings', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'start_time', 'operator' => 'date_after', 'value' => $now->copy()->subDays(90)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT']),
                'group_by' => json_encode(['DAYNAME(start_time)']),
                'order_by' => json_encode([
                    ['column' => 'COUNT(id)', 'direction' => 'desc'],
                ]),
                'limit' => 7,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => false,
                    'colors' => ['#0EA5E9'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Scheduling',
                    'tags' => ['weekday', 'demand', 'workforce'],
                ]),
            ],
            [
                'name' => 'Plan Revenue Potential',
                'slug' => 'plan-revenue-potential',
                'description' => 'Estimate revenue mix by plan using plan price and active subscription count.',
                'data_source' => 'subscriptions',
                'data_source_type' => 'custom_query',
                'custom_query' => "SELECT p.name AS plan_name, COUNT(s.id) AS active_subscriptions, (COUNT(s.id) * p.price) AS estimated_revenue FROM subscriptions s INNER JOIN plans p ON p.id = s.plan_id WHERE s.status IN ('active','trialing','past_due') GROUP BY p.id, p.name, p.price ORDER BY estimated_revenue DESC",
                'columns' => json_encode([
                    ['name' => 'plan_name', 'alias' => 'Plan'],
                    ['name' => 'active_subscriptions', 'alias' => 'Active Subscriptions'],
                    ['name' => 'estimated_revenue', 'alias' => 'Estimated Revenue'],
                ]),
                'filters' => json_encode([]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode([]),
                'group_by' => json_encode([]),
                'order_by' => json_encode([
                    ['column' => 'estimated_revenue', 'direction' => 'desc'],
                ]),
                'limit' => 15,
                'chart_type' => 'bar',
                'chart_config' => json_encode([
                    'horizontal' => true,
                    'colors' => ['#F59E0B'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Subscriptions',
                    'tags' => ['revenue', 'plans', 'commercial'],
                ]),
            ],
            [
                'name' => 'Upcoming Sessions Pipeline',
                'slug' => 'upcoming-sessions-pipeline',
                'description' => 'Count upcoming confirmed sessions by day for operational planning.',
                'data_source' => 'bookings',
                'data_source_type' => 'table',
                'custom_query' => null,
                'columns' => json_encode([
                    ['name' => 'DATE(start_time)', 'alias' => 'Date'],
                    ['name' => 'id', 'alias' => 'Upcoming Sessions', 'aggregation' => 'COUNT'],
                ]),
                'filters' => json_encode([
                    ['column' => 'status', 'operator' => 'in', 'value' => ['pending', 'confirmed']],
                    ['column' => 'start_time', 'operator' => 'date_after', 'value' => $now->copy()->subDays(1)->format('Y-m-d')],
                    ['column' => 'start_time', 'operator' => 'date_before', 'value' => $now->copy()->addDays(30)->format('Y-m-d')],
                ]),
                'filter_logic' => 'AND',
                'aggregations' => json_encode(['COUNT']),
                'group_by' => json_encode(['DATE(start_time)']),
                'order_by' => json_encode([
                    ['column' => 'DATE(start_time)', 'direction' => 'asc'],
                ]),
                'limit' => 31,
                'chart_type' => 'line',
                'chart_config' => json_encode([
                    'curve' => 'smooth',
                    'colors' => ['#6366F1'],
                ]),
                'metadata' => json_encode([
                    'category' => 'Scheduling',
                    'tags' => ['pipeline', 'upcoming', 'operations'],
                ]),
            ],
        ];

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
                    'is_scheduled' => true,
                    'execution_count' => 0,
                    'last_executed_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }

        $this->command->info('Business analytics reports seeded: ' . count($reports));
    }
}
