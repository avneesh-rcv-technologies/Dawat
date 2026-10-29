<?php

namespace Modules\Reports\Services;

use Modules\Reports\Models\Report;
use Modules\Reports\Models\ReportExecution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Exception;

class ReportExecutionService
{
    /**
     * Execute a report and return results.
     */
    public function execute(Report $report, array $runtimeFilters = [], $executedBy = null): array
    {
        $startTime = microtime(true);
        $actor = $this->normalizeActor($executedBy);

        if ($actor['id'] === null || $actor['type'] === null) {
            throw new \InvalidArgumentException('Executed by actor is required for report execution.');
        }

        // Create execution record
        $execution = ReportExecution::create([
            'report_id' => $report->id,
            'executed_by_id' => $actor['id'],
            'executed_by_type' => $actor['type'],
            'status' => 'running',
            'applied_filters' => array_merge($report->filters ?? [], $runtimeFilters),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        try {
            // Check cache first
            $cacheKey = $this->getCacheKey($report, $runtimeFilters);
            if ($cachedResult = $this->getFromCache($cacheKey)) {
                $execution->update([
                    'status' => 'completed',
                    'rows_returned' => count($cachedResult['data']),
                    'execution_time_ms' => 0,
                    'is_cached' => true,
                ]);

                return array_merge($cachedResult, ['execution_id' => $execution->id]);
            }

            // Build and execute query
            $query = $this->buildQuery($report, $runtimeFilters);
            $results = $query->get();

            // Apply aggregations if needed
            if ($report->aggregations) {
                $results = $this->applyAggregations($results, $report->aggregations);
            }

            $executionTime = (microtime(true) - $startTime) * 1000;

            // Mark execution as completed
            $execution->markAsCompleted(count($results), (int) $executionTime);

            // Update report execution count
            $report->incrementExecutionCount();

            $result = [
                'success' => true,
                'data' => $results->toArray(),
                'meta' => [
                    'total_rows' => count($results),
                    'execution_time_ms' => (int) $executionTime,
                    'execution_time_seconds' => round($executionTime / 1000, 2),
                    'is_cached' => false,
                    'report_name' => $report->name,
                    'chart_type' => $report->chart_type,
                    'columns' => $report->columns,
                ],
                'execution_id' => $execution->id,
            ];

            // Cache the result
            $this->cacheResult($cacheKey, $result);

            return $result;
        } catch (Exception $e) {
            $execution->markAsFailed($e->getMessage());

            return [
                'success' => false,
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while executing the report.',
                'execution_id' => $execution->id,
            ];
        }
    }

    /**
     * Normalize the actor for morph columns.
     */
    protected function normalizeActor($actor): array
    {
        if ($actor instanceof Model) {
            return [
                'id' => $actor->getKey(),
                'type' => $actor->getMorphClass(),
            ];
        }

        if (is_array($actor)) {
            return [
                'id' => $actor['id'] ?? null,
                'type' => $actor['type'] ?? null,
            ];
        }

        if (is_object($actor) && method_exists($actor, 'getAuthIdentifier')) {
            return [
                'id' => $actor->getAuthIdentifier(),
                'type' => method_exists($actor, 'getMorphClass') ? $actor->getMorphClass() : get_class($actor),
            ];
        }

        return [
            'id' => $actor ?: null,
            'type' => null,
        ];
    }

    /**
     * Build query based on report configuration.
     */
    protected function buildQuery(Report $report, array $runtimeFilters = [])
    {
        // Start with base table/model
        $query = $this->getBaseQuery($report);

        // Select columns
        if ($report->columns && !empty($report->columns)) {
            $selectColumns = $this->buildSelectColumns($report->columns);
            $query->select($selectColumns);
        }

        // Apply filters
        $allFilters = array_merge($report->filters ?? [], $runtimeFilters);
        if (!empty($allFilters)) {
            $query = $this->applyFilters($query, $allFilters, $report->filter_logic ?? 'AND');
        }

        // Apply GROUP BY
        if ($report->group_by && !empty($report->group_by)) {
            $groupByColumns = [];
            foreach ($report->group_by as $groupBy) {
                // If it contains SQL functions (parentheses), use raw expression
                if (is_string($groupBy) && (strpos($groupBy, '(') !== false || strpos($groupBy, ')') !== false)) {
                    $groupByColumns[] = DB::raw($groupBy);
                } else {
                    $groupByColumns[] = $groupBy;
                }
            }
            $query->groupBy($groupByColumns);
        }

        // Apply ORDER BY
        if ($report->order_by && !empty($report->order_by)) {
            foreach ($report->order_by as $order) {
                $column = $order['column'];
                $direction = $order['direction'] ?? 'asc';

                // If column contains SQL functions, use raw expression
                if (is_string($column) && (strpos($column, '(') !== false || strpos($column, ')') !== false)) {
                    $query->orderByRaw("{$column} {$direction}");
                } else {
                    $query->orderBy($column, $direction);
                }
            }
        }

        // Apply LIMIT
        if ($report->limit) {
            $query->limit($report->limit);
        }

        return $query;
    }

    /**
     * Get base query from data source.
     */
    // protected function getBaseQuery(Report $report)
    // {
    //     if ($report->data_source_type === 'custom_query' && $report->custom_query) {
    //         // For advanced users with custom SQL
    //         return DB::table(DB::raw("({$report->custom_query}) as custom_data"));
    //     }

    //     if ($report->data_source_type === 'model') {
    //         // Use Eloquent model
    //         $modelClass = $this->getModelClass($report->data_source);
    //         if (class_exists($modelClass)) {
    //             return $modelClass::query();
    //         }
    //     }

    //     // Default: use table name
    //     return DB::table($report->data_source);
    // }

    protected function getBaseQuery(Report $report)
{
    if ($report->data_source_type === 'custom_query' && $report->custom_query) {
        // For advanced users with custom SQL
        return DB::table(DB::raw("({$report->custom_query}) as custom_data"));
    }

    if ($report->data_source_type === 'model') {
        // Use Eloquent model
        $modelClass = $this->getModelClass($report->data_source);
        if (class_exists($modelClass)) {
            $query = $modelClass::query();
            
            // Apply restaurant filter
            $query = $this->applyRestaurantFilter($query, $report->data_source);
            
            return $query;
        }
    }

    // Default: use table name
    $query = DB::table($report->data_source);
    
    // Apply restaurant filter
    $query = $this->applyRestaurantFilter($query, $report->data_source);
    
    return $query;
}


    /**
     * Apply restaurant filter to query if current admin owns a restaurant.
     * This ensures restaurant admins only see their own data.
     */
    protected function applyRestaurantFilter($query, $dataSource)
    {
        $currentAdmin = auth('admin')->user();

        if (!$currentAdmin) {
            return $query;
        }

        // Check if this admin owns a restaurant
        $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $currentAdmin->id)->first();

        if (!$restaurant) {
            // Super admin - no filtering, see all data
            return $query;
        }

        // Restaurant admin: filter data by vendor_id
        $restaurantClass = \Modules\Restaurant\Models\Restaurant::class;

        switch ($dataSource) {
            case 'orders':
                // Filter orders by restaurant
                $query->where('orders.vendor_id', $restaurant->id)
                    ->where('orders.vendor_type', $restaurantClass);
                break;

            case 'customers':
                // Filter customers who ordered from this restaurant
                $query->whereExists(function ($subQuery) use ($restaurant, $restaurantClass) {
                    $subQuery->select(DB::raw(1))
                        ->from('orders')
                        ->whereColumn('orders.customer_id', 'customers.id')
                        ->where('orders.customer_type', 'Modules\\Acl\\Models\\Customer')
                        ->where('orders.vendor_id', $restaurant->id)
                        ->where('orders.vendor_type', $restaurantClass);
                });
                break;

            case 'users':
                // Filter users who ordered from this restaurant
                $query->whereExists(function ($subQuery) use ($restaurant, $restaurantClass) {
                    $subQuery->select(DB::raw(1))
                        ->from('orders')
                        ->whereColumn('orders.customer_id', 'users.id')
                        ->where('orders.customer_type', 'App\\Models\\User')
                        ->where('orders.vendor_id', $restaurant->id)
                        ->where('orders.vendor_type', $restaurantClass);
                });
                break;

            case 'restaurants':
                // Show only their own restaurant
                $query->where('restaurants.id', $restaurant->id);
                break;
        }

        return $query;
    }


    /**
     * Build select columns with aggregations.
     */
    protected function buildSelectColumns(array $columns): array
    {
        $selectColumns = [];

        foreach ($columns as $column) {
            if (is_string($column)) {
                $selectColumns[] = $column;
            } elseif (is_array($column)) {
                $columnName = $column['name'] ?? $column['column'] ?? null;
                $aggregation = $column['aggregation'] ?? null;
                $alias = $column['alias'] ?? $columnName;

                if (!$columnName) {
                    continue;
                }

                if ($aggregation) {
                    // Apply aggregation function - wrap alias in backticks for spaces
                    $selectColumns[] = DB::raw("{$aggregation}({$columnName}) as `{$alias}`");
                } else {
                    // Check if column name contains SQL functions (has parentheses)
                    if (strpos($columnName, '(') !== false || strpos($columnName, ')') !== false) {
                        // Use raw expression for SQL functions
                        $selectColumns[] = DB::raw($columnName . ($alias !== $columnName ? " as `{$alias}`" : ''));
                    } else {
                        // Wrap alias in backticks if it differs from column name
                        $selectColumns[] = $columnName . ($alias !== $columnName ? " as `{$alias}`" : '');
                    }
                }
            }
        }

        return $selectColumns;
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters($query, array $filters, string $logic = 'AND')
    {
        if (empty($filters)) {
            return $query;
        }

        // Handle special date_range filter
        if (isset($filters['date_range']) && is_array($filters['date_range'])) {
            $dateRange = $filters['date_range'];
            if (isset($dateRange['start']) && isset($dateRange['end'])) {
                // Try to find created_at column, or use first date column
                $dateColumn = 'created_at'; // Default assumption

                $query->whereBetween($dateColumn, [
                    $dateRange['start'] . ' 00:00:00',
                    $dateRange['end'] . ' 23:59:59'
                ]);
            }

            // Remove date_range from filters array to avoid processing it again
            unset($filters['date_range']);
        }

        $method = strtolower($logic) === 'or' ? 'orWhere' : 'where';

        return $query->where(function ($q) use ($filters, $method) {
            foreach ($filters as $filter) {
                if (!isset($filter['column'], $filter['operator'])) {
                    continue;
                }

                $column = $filter['column'];
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;

                // Handle different operators
                switch (strtolower($operator)) {
                    case 'equals':
                    case '=':
                    case '==':
                        $q->$method($column, '=', $value);
                        break;

                    case 'not_equals':
                    case '!=':
                    case '<>':
                        $q->$method($column, '!=', $value);
                        break;

                    case 'greater_than':
                    case '>':
                        $q->$method($column, '>', $value);
                        break;

                    case 'greater_than_or_equal':
                    case '>=':
                        $q->$method($column, '>=', $value);
                        break;

                    case 'less_than':
                    case '<':
                        $q->$method($column, '<', $value);
                        break;

                    case 'less_than_or_equal':
                    case '<=':
                        $q->$method($column, '<=', $value);
                        break;

                    case 'like':
                    case 'contains':
                        $q->$method($column, 'LIKE', "%{$value}%");
                        break;

                    case 'not_like':
                    case 'not_contains':
                        $q->$method($column, 'NOT LIKE', "%{$value}%");
                        break;

                    case 'starts_with':
                        $q->$method($column, 'LIKE', "{$value}%");
                        break;

                    case 'ends_with':
                        $q->$method($column, 'LIKE', "%{$value}");
                        break;

                    case 'in':
                        $values = is_array($value) ? $value : explode(',', $value);
                        $q->whereIn($column, $values);
                        break;

                    case 'not_in':
                        $values = is_array($value) ? $value : explode(',', $value);
                        $q->whereNotIn($column, $values);
                        break;

                    case 'between':
                        if (isset($filter['value_min'], $filter['value_max'])) {
                            $q->whereBetween($column, [$filter['value_min'], $filter['value_max']]);
                        }
                        break;

                    case 'is_null':
                        $q->whereNull($column);
                        break;

                    case 'is_not_null':
                        $q->whereNotNull($column);
                        break;

                    case 'date_equals':
                        $q->whereDate($column, '=', $value);
                        break;

                    case 'date_before':
                        $q->whereDate($column, '<', $value);
                        break;

                    case 'date_after':
                        $q->whereDate($column, '>', $value);
                        break;

                    default:
                        $q->$method($column, $operator, $value);
                        break;
                }
            }
        });
    }

    /**
     * Apply aggregations to results.
     */
    protected function applyAggregations($results, array $aggregations)
    {
        // Post-processing aggregations if needed
        return $results;
    }

    /**
     * Get model class from data source name.
     */
    protected function getModelClass(string $dataSource): string
    {
        // Try common namespaces
        $namespaces = [
            "App\\Models\\",
            "Modules\\{$dataSource}\\Models\\",
        ];

        foreach ($namespaces as $namespace) {
            $class = $namespace . $dataSource;
            if (class_exists($class)) {
                return $class;
            }
        }

        return $dataSource;
    }

    /**
     * Get cache key for report execution.
     */
    // protected function getCacheKey(Report $report, array $runtimeFilters = []): string
    // {
    //     $key = 'report_' . $report->id . '_' . md5(json_encode([
    //         'filters' => $runtimeFilters,
    //         'columns' => $report->columns,
    //         'updated_at' => $report->updated_at,
    //     ]));

    //     return $key;
    // }

    protected function getCacheKey(Report $report, array $runtimeFilters = []): string
{
    // Get restaurant ID if current admin owns a restaurant
    $restaurantId = null;
    $currentAdmin = auth('admin')->user();
    if ($currentAdmin) {
        $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $currentAdmin->id)->first();
        if ($restaurant) {
            $restaurantId = $restaurant->id;
        }
    }
    
    $key = 'report_' . $report->id . '_' . md5(json_encode([
        'filters' => $runtimeFilters,
        'columns' => $report->columns,
        'updated_at' => $report->updated_at,
        'restaurant_id' => $restaurantId, // Separate cache per restaurant
    ]));
    
    return $key;
}

    /**
     * Get result from cache.
     */
    protected function getFromCache(string $key): ?array
    {
        return Cache::get($key);
    }

    /**
     * Cache result.
     */
    protected function cacheResult(string $key, array $result, int $minutes = 60): void
    {
        Cache::put($key, $result, now()->addMinutes($minutes));
    }

    /**
     * Get available data sources (tables and models).
     */
    public function getAvailableDataSources(): array
    {
        $tables = $this->getDatabaseTables();
        $models = $this->getAvailableModels();

        return [
            'tables' => $tables,
            'models' => $models,
        ];
    }

    /**
     * Get database tables.
     */
    protected function getDatabaseTables(): array
    {
        $tables = DB::select('SHOW TABLES');
        $databaseName = 'Tables_in_' . env('DB_DATABASE');

        return array_map(function ($table) use ($databaseName) {
            return $table->$databaseName;
        }, $tables);
    }

    /**
     * Get available Eloquent models.
     */
    protected function getAvailableModels(): array
    {
        // This is a simplified version. In production, you might want to scan model directories
        return [
            'Admin' => 'Modules\\Acl\\Models\\Admin',
            'Clients' => 'Modules\\Acl\\Models\\Clients',
            'Members' => 'Modules\\Acl\\Models\\Members'
        ];
    }

    /**
     * Get columns for a data source.
     */
    public function getDataSourceColumns(string $dataSource, string $type = 'table'): array
    {
        if ($type === 'model') {
            $modelClass = $this->getModelClass($dataSource);
            if (class_exists($modelClass)) {
                $instance = new $modelClass();
                $dataSource = $instance->getTable();
            }
        }

        try {
            $columns = Schema::getColumnListing($dataSource);
            return array_map(function ($column) {
                return [
                    'name' => $column,
                    'label' => ucwords(str_replace('_', ' ', $column)),
                    'type' => $this->getColumnType($column),
                ];
            }, $columns);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get column type (simplified).
     */
    protected function getColumnType(string $column): string
    {
        if (str_contains($column, '_at') || str_contains($column, 'date')) {
            return 'date';
        }

        if (str_contains($column, '_id') || $column === 'id') {
            return 'number';
        }

        if (str_contains($column, 'price') || str_contains($column, 'amount') || str_contains($column, 'value')) {
            return 'number';
        }

        return 'string';
    }
}
