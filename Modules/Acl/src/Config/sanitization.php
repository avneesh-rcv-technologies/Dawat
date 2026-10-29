<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Request Sanitization Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration controls how the SanitizeRequest middleware
    | processes and cleans incoming request data.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Sanitization
    |--------------------------------------------------------------------------
    |
    | Set to false to disable sanitization entirely (not recommended for production)
    |
    */
    'enabled' => env('SANITIZATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Controls what gets logged during sanitization
    |
    */
    'logging' => [
        'log_suspicious_requests' => env('LOG_SUSPICIOUS_REQUESTS', true),
        'log_blocked_patterns' => env('LOG_BLOCKED_PATTERNS', true),
        'log_file_uploads' => env('LOG_FILE_UPLOADS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dangerous Patterns
    |--------------------------------------------------------------------------
    |
    | Regular expressions that identify potentially dangerous content
    | Note: These patterns are designed to avoid false positives with datetime strings
    |
    */
    'dangerous_patterns' => [
        // Script tags
        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',

        // JavaScript events
        '/on\w+\s*=\s*["\'][^"\']*["\']/',

        // SQL injection patterns (modified to avoid datetime false positives)
        '/(\bunion\b|\bselect\b|\binsert\b|\bupdate\b|\bdelete\b|\bdrop\b|\bcreate\b|\balter\b|\bexec\b|\bexecute\b)\s+.*(\bfrom\b|\binto\b|\bwhere\b|\bvalues\b)/i',

        // XSS patterns
        '/javascript\s*:/i',
        '/vbscript\s*:/i',
        '/data\s*:\s*text\/html/i',

        // PHP code injection
        '/<\?php/i',
        '/<\?=/i',

        // Command injection (modified to avoid datetime false positives)
        '/(\||;|&|\$\(|\`)(?![+-]\d{2}:\d{2})/i',

        // HTML entities that could be dangerous
        '/&#x?[0-9a-f]+;?/i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocked File Extensions
    |--------------------------------------------------------------------------
    |
    | File extensions that should never be allowed in uploads
    |
    */
    'blocked_extensions' => [
        'php',
        'php3',
        'php4',
        'php5',
        'phtml',
        'asp',
        'aspx',
        'jsp',
        'exe',
        'bat',
        'cmd',
        'com',
        'scr',
        'vbs',
        'js',
        'jar',
        'sh',
        'pl',
        'py',
        'rb',
        'cgi'
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Limits
    |--------------------------------------------------------------------------
    |
    | Limits to prevent abuse
    |
    */
    'limits' => [
        'max_query_string_length' => env('MAX_QUERY_STRING_LENGTH', 2000),
        'max_parameters_count' => env('MAX_PARAMETERS_COUNT', 100),
        'max_parameter_length' => env('MAX_PARAMETER_LENGTH', 10000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sanitization Rules
    |--------------------------------------------------------------------------
    |
    | Configure how different types of data should be sanitized
    | All HTML content is now encoded for maximum security
    |
    */
    'rules' => [
        'html_encode' => true, // Always encode HTML characters for security
        'strip_tags' => false, // Not used - we encode instead of stripping
        'remove_null_bytes' => true,
        'trim_whitespace' => true,
        'decode_entities' => true, // Decode then re-encode to prevent bypasses
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Routes that should be excluded from sanitization entirely
    | Use route names or patterns
    | WARNING: Excluded routes bypass ALL sanitization - use with extreme caution
    |
    */
    'excluded_routes' => [
        'notification.email.templates.create',
        'notification.email.templates.update',
        'settings.store',
        'settings.general.store',
        'notification.email-design.update',
        'cms.pages.store',
        'cms.pages.update',
        'cms.pages.update-content'

    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Parameters
    |--------------------------------------------------------------------------
    |
    | Parameter names that should be excluded from sanitization
    | WARNING: Use with extreme caution - excluded parameters bypass ALL sanitization
    | Only exclude parameters that have their own validation/sanitization
    |
    */
    'excluded_parameters' => [
        'phone',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Specific Excluded Parameters
    |--------------------------------------------------------------------------
    |
    | Parameter names that should preserve raw HTML only for selected routes.
    | These fields must be validated and purified before they are saved/rendered.
    |
    */
    'excluded_parameters_by_route' => [
        'catalog.categories.store' => ['description'],
        'catalog.categories.update' => ['description'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for suspicious requests
    |
    */
    'rate_limiting' => [
        'enabled' => env('SANITIZATION_RATE_LIMITING', false),
        'max_requests_per_minute' => env('MAX_SUSPICIOUS_REQUESTS_PER_MINUTE', 10),
        'block_duration_minutes' => env('SUSPICIOUS_BLOCK_DURATION', 15),
    ],
];
