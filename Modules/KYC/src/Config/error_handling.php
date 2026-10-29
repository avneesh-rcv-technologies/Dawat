<?php

return [
    /*
    |--------------------------------------------------------------------------
    | KYC Error Handling Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for KYC module error handling
    |
    */

    // File upload settings
    'max_file_size' => env('KYC_MAX_FILE_SIZE', 10240), // KB
    'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],

    // Error logging settings
    'log_channel' => env('KYC_LOG_CHANNEL', 'daily'),
    'log_level' => env('KYC_LOG_LEVEL', 'error'),

    // Notification settings
    'notification_retry_attempts' => env('KYC_NOTIFICATION_RETRY_ATTEMPTS', 3),
    'notification_retry_delay' => env('KYC_NOTIFICATION_RETRY_DELAY', 60), // seconds

    // Validation settings
    'strict_validation' => env('KYC_STRICT_VALIDATION', true),
    'allow_duplicate_submissions' => env('KYC_ALLOW_DUPLICATE_SUBMISSIONS', false),

    // Error response settings
    'include_trace_in_response' => env('KYC_INCLUDE_TRACE', false),
    'mask_sensitive_data' => env('KYC_MASK_SENSITIVE_DATA', true),

    // Rate limiting
    'rate_limit_requests' => env('KYC_RATE_LIMIT_REQUESTS', 100),
    'rate_limit_window' => env('KYC_RATE_LIMIT_WINDOW', 60), // minutes
];