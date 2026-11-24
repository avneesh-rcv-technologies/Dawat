<?php

return [
    'name' => 'Activitylog',
    'version' => '1.0.0',
    'description' => 'Activitylog module for the application',

    'settings' => [
        'enabled' => true,
        'debug' => false,
        'cache' => true,
        'cache_ttl' => 3600,
    ],

    'permissions' => [
        'view' => 'View Activitylog',
        'create' => 'Create Activitylog',
        'edit' => 'Edit Activitylog',
        'delete' => 'Delete Activitylog',
    ],

    /*
    |--------------------------------------------------------------------------
    | Request logging scale settings
    |--------------------------------------------------------------------------
    */
    'queue_backlog_threshold' => (int) env('ACTIVITY_LOG_QUEUE_BACKLOG_THRESHOLD', 500),
    'failed_log_jobs_alert_threshold' => (int) env('ACTIVITY_LOG_FAILED_JOBS_ALERT_THRESHOLD', 50),
    'log_queue' => env('ACTIVITY_LOG_QUEUE', 'default'),
    'archive_after_days' => (int) env('ACTIVITY_LOG_ARCHIVE_DAYS', 90),
    'request_sampling_rate' => (int) env('ACTIVITY_LOG_PUBLIC_SAMPLING_RATE', 10),

    /*
    |--------------------------------------------------------------------------
    | Spatie activity_log table
    |--------------------------------------------------------------------------
    |
    | Reserved for intentional audit events (login, settings changes), not HTTP
    | request logging. Request traffic uses `request_activity_logs`.
    |
    */
    'spatie_audit_enabled' => env('ACTIVITY_LOG_SPATIE_AUDIT_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Log storage driver (mysql today; redis_stream/kafka planned for scale-out)
    |--------------------------------------------------------------------------
    */
    'log_driver' => env('ACTIVITY_LOG_DRIVER', 'mysql'),
];
