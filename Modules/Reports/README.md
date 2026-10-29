# Reports Module - Comprehensive Documentation

## Overview

The Reports Module is a powerful self-service reporting system that allows administrators to create, execute, schedule, and export custom reports without writing code. It features a visual report builder, advanced filtering with AND/OR logic, aggregations, multiple chart types, role-based permissions, and automated scheduling.

## Features

### ✅ Core Features
- **Visual Report Builder**: Drag-and-drop interface for creating reports
- **Data Source Selection**: Connect to any database table or Eloquent model
- **Column Configuration**: Select specific columns with aliases and aggregations
- **Advanced Filtering**: Support for AND/OR logic, 15+ operators
- **Aggregations**: COUNT, SUM, AVG, MIN, MAX, GROUP BY
- **Multiple Chart Types**: Table, Bar, Line, Pie, Area, Donut, Scatter
- **Role-Based Access Control**: Public/private reports with granular permissions
- **Report Scheduling**: Daily, weekly, monthly, or custom cron schedules
- **Multi-Format Export**: CSV, Excel, PDF
- **Execution History & Audit Logs**: Track all report executions
- **Performance Optimization**: Query caching, result caching
- **Security Controls**: SQL injection prevention, input sanitization

### 📊 Chart Types
1. **Table** - Traditional tabular data display
2. **Bar Chart** - Vertical bar comparisons
3. **Line Chart** - Trend analysis over time
4. **Pie Chart** - Proportional data visualization
5. **Area Chart** - Cumulative trends
6. **Donut Chart** - Percentage breakdown with center space
7. **Scatter Plot** - Correlation analysis

### 🔍 Filter Operators
- **Comparison**: Equals, Not Equals, Greater Than, Less Than, etc.
- **String**: Contains, Starts With, Ends With, Like, Not Like
- **List**: In, Not In
- **Range**: Between
- **Null Checks**: Is Null, Is Not Null
- **Date Operations**: Date Equals, Before, After

## Database Schema

### Tables

#### `reports`
Main table storing report definitions:
- `id` - Primary key
- `name` - Report display name
- `slug` - URL-friendly identifier
- `description` - Report purpose/description
- `data_source` - Table/model name
- `data_source_type` - table, model, custom_query
- `custom_query` - Optional custom SQL
- `columns` - JSON array of selected columns
- `filters` - JSON array of filter conditions
- `filter_logic` - AND, OR, or custom
- `aggregations` - JSON array of aggregation functions
- `group_by` - JSON array of GROUP BY columns
- `order_by` - JSON array of sorting configuration
- `limit` - Result limit
- `chart_type` - Visualization type
- `chart_config` - Chart-specific settings
- `created_by_id` - ID of creator (polymorphic)
- `created_by_type` - Type of creator model (polymorphic)
- `is_public` - Public/private flag
- `allowed_roles` - JSON array of authorized roles
- `allowed_users` - JSON array of authorized users
- `is_active` - Active status
- `is_scheduled` - Scheduling flag
- `execution_count` - Total executions
- `last_executed_at` - Last execution timestamp
- `metadata` - Additional settings

#### `report_schedules`
Automated report scheduling:
- `report_id` - Foreign key to reports
- `frequency` - daily, weekly, monthly, custom
- `cron_expression` - Custom cron schedule
- `scheduled_time` - Execution time
- `scheduled_days` - Days for weekly schedules
- `scheduled_date` - Date for monthly schedules
- `recipients` - Email addresses JSON array
- `export_format` - pdf, excel, csv
- `email_subject` - Email subject line
- `email_body` - Email content
- `is_active` - Schedule status
- `last_run_at` - Last execution
- `next_run_at` - Next scheduled run
- `run_count` - Total executions
- `failure_count` - Failed executions

#### `report_executions`
Execution history and audit logs:
- `report_id` - Foreign key to reports
- `executed_by_id`/`executed_by_type` - Polymorphic executor reference
- `schedule_id` - Schedule that triggered (if applicable)
- `status` - pending, running, completed, failed
- `rows_returned` - Result row count
- `execution_time_ms` - Performance metric
- `error_message` - Failure details
- `applied_filters` - Filters used
- `result_hash` - Hash for caching
- `is_cached` - Cache flag
- `cache_expires_at` - Cache expiration
- `ip_address` - User IP
- `user_agent` - Browser info

#### `report_exports`
Export file management:
- `report_id` - Foreign key to reports
- `execution_id` - Related execution
- `exported_by_id`/`exported_by_type` - Polymorphic export requester
- `format` - csv, excel, pdf
- `filename` - Generated filename
- `file_path` - Storage path
- `file_size` - Size in bytes
- `status` - pending, processing, completed, failed
- `export_options` - Format-specific options
- `rows_exported` - Export row count
- `download_count` - Download tracking
- `last_downloaded_at` - Last download
- `expires_at` - Expiration date

#### `report_saved_filters`
Reusable filter presets:
- `report_id` - Foreign key to reports
- `user_id`/`user_type` - Polymorphic filter owner
- `name` - Filter preset name
- `filters` - JSON filter configuration
- `is_default` - Default filter flag
- `is_public` - Share with others

## Architecture

### Repository Pattern
`ReportRepository` - Data access layer
- CRUD operations
- Query methods (getActive, getScheduled, getAccessibleByUser)
- Search and statistics
- Report duplication

### Service Layer

#### `ReportService`
Business logic for report management:
- Create/update/delete reports
- Validation and security checks
- Access control management
- Report sharing and permissions

#### `ReportExecutionService`
Core query execution engine:
- Dynamic query building from report configuration
- Filter application with AND/OR logic
- Aggregation processing
- Result caching
- Performance optimization
- Data source introspection (tables, columns)

Methods:
- `execute(Report $report, array $runtimeFilters, ?int $userId): array`
- `buildQuery(Report $report, array $runtimeFilters)`
- `applyFilters($query, array $filters, string $logic)`
- `getAvailableDataSources(): array`
- `getDataSourceColumns(string $dataSource, string $type): array`

#### `ReportExportService`
Export functionality:
- CSV generation
- Excel generation (XLSX)
- PDF generation
- File storage and retrieval
- Download tracking
- Expired export cleanup

### Controllers

#### `ReportController`
Main CRUD and execution controller:
- `index()` - List all accessible reports
- `create()` - Show create form
- `store(Request $request)` - Save new report
- `show(int $id, Request $request)` - View report with results
- `edit(int $id)` - Show edit form
- `update(Request $request, int $id)` - Update report
- `destroy(int $id)` - Delete report
- `duplicate(int $id)` - Clone report
- `toggleActive(int $id)` - Activate/deactivate
- `export(Request $request, int $id)` - Export report
- `statistics()` - Get module statistics

#### `ReportBuilderController`
Interactive builder interface:
- `index()` - Show builder UI
- `getColumns(Request $request)` - Fetch available columns
- `preview(Request $request)` - Preview report results
- `getAggregations()` - List aggregation functions
- `getOperators()` - List filter operators
- `getChartTypes()` - List chart options

## API Endpoints

### Report Management
```
GET    /admin/reports              - List reports
GET    /admin/reports/create       - Show create form
POST   /admin/reports              - Create report
GET    /admin/reports/{id}         - View report
GET    /admin/reports/{id}/edit    - Show edit form
PUT    /admin/reports/{id}         - Update report
DELETE /admin/reports/{id}         - Delete report
POST   /admin/reports/{id}/duplicate - Duplicate report
POST   /admin/reports/{id}/toggle-active - Toggle status
POST   /admin/reports/{id}/export  - Export report
GET    /admin/reports/statistics/all - Get statistics
```

### Report Builder
```
GET    /admin/reports/builder              - Builder UI
POST   /admin/reports/builder/get-columns  - Get data source columns
POST   /admin/reports/builder/preview      - Preview report
GET    /admin/reports/builder/aggregations - List aggregations
GET    /admin/reports/builder/operators    - List operators
GET    /admin/reports/builder/chart-types  - List chart types
```

## Usage Examples

### Creating a Report Programmatically

```php
use Modules\Reports\Services\ReportService;
use Modules\Reports\Repositories\ReportRepository;

$reportService = new ReportService(new ReportRepository());

$reportData = [
    'name' => 'Monthly Sales Report',
    'description' => 'Sales breakdown by month',
    'data_source' => 'orders',
    'data_source_type' => 'table',
    'columns' => [
        ['name' => 'id', 'alias' => 'Order ID'],
        ['name' => 'total', 'aggregation' => 'SUM', 'alias' => 'Total Sales'],
        ['name' => 'created_at', 'alias' => 'Date'],
    ],
    'filters' => [
        [
            'column' => 'status',
            'operator' => 'equals',
            'value' => 'completed'
        ],
        [
            'column' => 'created_at',
            'operator' => 'date_after',
            'value' => '2024-01-01'
        ]
    ],
    'filter_logic' => 'AND',
    'group_by' => ['MONTH(created_at)'],
    'order_by' => [
        ['column' => 'created_at', 'direction' => 'desc']
    ],
    'chart_type' => 'bar',
    'is_public' => false,
];

$report = $reportService->create($reportData, auth()->id());
```

### Executing a Report

```php
use Modules\Reports\Services\ReportExecutionService;

$executionService = new ReportExecutionService();

// Execute with runtime filters
$result = $executionService->execute($report, [
    [
        'column' => 'created_at',
        'operator' => 'between',
        'value_min' => '2024-01-01',
        'value_max' => '2024-12-31'
    ]
], auth()->user());

if ($result['success']) {
    $data = $result['data'];
    $meta = $result['meta'];
    // Process results...
}
```

### Exporting a Report

```php
use Modules\Reports\Services\ReportExportService;

$exportService = new ReportExportService();

// Get report data
$result = $executionService->execute($report, [], auth()->user());

// Export to Excel
$export = $exportService->export(
    $report,
    'excel',
    $result['data'],
    auth()->user(),
    $result['execution_id']
);

// Download
return $exportService->download($export);
```

## Security Features

### 1. SQL Injection Prevention
- Custom queries are validated and sanitized
- Only SELECT statements allowed
- Dangerous keywords (DROP, DELETE, UPDATE, etc.) are blocked
- Parameterized queries used throughout

### 2. Access Control
- Report-level permissions (view, create, edit, delete, execute, export)
- Public/private reports
- Role-based access lists
- User-specific sharing

### 3. Input Validation
- All user inputs validated
- Filter values sanitized
- Column names whitelisted
- Data type checking

### 4. Audit Logging
- All executions logged
- User tracking (IP, user agent)
- Error tracking
- Download tracking

## Performance Optimization

### 1. Query Optimization
- Selective column loading
- Index usage encouraged
- Result limiting
- Query timeout protection

### 2. Caching Strategy
- Result caching (configurable TTL)
- Cache invalidation on data changes
- Hash-based cache keys
- Per-user cache support

### 3. Resource Limits
- Maximum rows per execution: 100,000
- Export row limit: 10,000
- Execution timeout: 300 seconds
- Cache expiration: 60 minutes

### 4. Background Processing
- Scheduled reports run asynchronously
- Large exports queued
- Email delivery queued

## Configuration

Edit `/Modules/Reports/src/Config/reports.php`:

```php
return [
    'cache' => [
        'enabled' => true,
        'ttl' => 60, // minutes
    ],
    'export' => [
        'max_rows' => 10000,
        'expiration_days' => 7,
    ],
    'execution' => [
        'timeout' => 300, // seconds
        'max_rows' => 100000,
    ],
    'security' => [
        'allow_custom_queries' => false,
        'log_executions' => true,
    ],
];
```

## Permissions

Register these permissions in your ACL system:

- `reports.view` - View reports
- `reports.create` - Create new reports
- `reports.edit` - Edit existing reports
- `reports.delete` - Delete reports
- `reports.execute` - Run reports
- `reports.export` - Export reports
- `reports.schedule` - Create schedules
- `reports.duplicate` - Clone reports

## Scheduled Reports

### Setting Up a Schedule

```php
use Modules\Reports\Models\ReportSchedule;

ReportSchedule::create([
    'report_id' => $report->id,
    'frequency' => 'daily',
    'scheduled_time' => '09:00:00',
    'recipients' => ['admin@example.com', 'manager@example.com'],
    'export_format' => 'pdf',
    'email_subject' => 'Daily Sales Report',
    'email_body' => 'Please find attached your daily sales report.',
    'is_active' => true,
]);
```

### Cron Setup

Add to your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('reports:execute-schedules')->everyMinute();
    $schedule->command('reports:cleanup-exports')->daily();
}
```

## Best Practices

1. **Use Indexes**: Ensure filtered and sorted columns are indexed
2. **Limit Results**: Always set reasonable limits
3. **Cache Wisely**: Enable caching for frequently-run reports
4. **Test Queries**: Use preview before saving
5. **Monitor Performance**: Check execution times regularly
6. **Clean Up**: Remove unused reports and expired exports
7. **Document Reports**: Add clear descriptions
8. **Security First**: Never enable custom queries for untrusted users

## Troubleshooting

### Common Issues

**Report execution timeout:**
- Reduce result limit
- Add indexes to filtered columns
- Simplify filters
- Increase timeout in config

**Export fails:**
- Check file permissions
- Verify storage disk configuration
- Check export row limit
- Ensure dependencies installed (PhpSpreadsheet, DomPDF)

**Access denied:**
- Check user permissions
- Verify report access settings
- Check role assignments

**Incorrect results:**
- Verify filter logic (AND vs OR)
- Check filter operators
- Test with preview
- Review GROUP BY configuration

## Migration Guide

Run migrations:

```bash
php artisan migrate
```

Clear cache:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

Regenerate autoload:

```bash
composer dump-autoload
```

## Support

For issues, feature requests, or contributions, contact your system administrator or refer to the module documentation.

---

**Version:** 1.0.0  
**Last Updated:** December 31, 2024  
**License:** Proprietary
