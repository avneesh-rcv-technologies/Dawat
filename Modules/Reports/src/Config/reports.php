<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Report Module Configuration
    |--------------------------------------------------------------------------
    */

    'name' => 'Reports',

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 60, // Cache TTL in minutes
        'driver' => 'file', // redis, file, database
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    */
    'export' => [
        'max_rows' => 10000, // Maximum rows per export
        'expiration_days' => 7, // Days before export files expire
        'disk' => 'local', // Storage disk
        'formats' => ['csv', 'excel', 'pdf'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Execution Limits
    |--------------------------------------------------------------------------
    */
    'execution' => [
        'timeout' => 300, // Seconds
        'max_rows' => 100000, // Maximum rows to return
        'default_limit' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */
    'security' => [
        'allow_custom_queries' => false, // Allow advanced users to write custom SQL
        'sanitize_inputs' => true,
        'log_executions' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduling
    |--------------------------------------------------------------------------
    */
    'scheduling' => [
        'enabled' => true,
        'max_schedules_per_report' => 5,
        'default_time' => '09:00',
    ],

    /*
    |--------------------------------------------------------------------------
    | Chart Configuration
    |--------------------------------------------------------------------------
    */
    'charts' => [
        'default_type' => 'table',
        'library' => 'chartjs', // chartjs, apexcharts, highcharts
        'colors' => [
            '#4F46E5', '#06B6D4', '#10B981', '#F59E0B', '#EF4444',
            '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#6366F1',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Aggregations
    |--------------------------------------------------------------------------
    */
    'aggregations' => [
        'COUNT' => 'Count',
        'SUM' => 'Sum',
        'AVG' => 'Average',
        'MIN' => 'Minimum',
        'MAX' => 'Maximum',
        'GROUP_CONCAT' => 'Group Concat',
    ],

    /*
    |--------------------------------------------------------------------------
    | Filter Operators
    |--------------------------------------------------------------------------
    */
    'operators' => [
        'equals' => '=',
        'not_equals' => '!=',
        'greater_than' => '>',
        'greater_than_or_equal' => '>=',
        'less_than' => '<',
        'less_than_or_equal' => '<=',
        'like' => 'LIKE',
        'not_like' => 'NOT LIKE',
        'in' => 'IN',
        'not_in' => 'NOT IN',
        'between' => 'BETWEEN',
        'is_null' => 'IS NULL',
        'is_not_null' => 'IS NOT NULL',
    ],
];
