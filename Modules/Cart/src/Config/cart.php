<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cart Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Cart module
    |
    */

    // Maximum quantity per cart item
    'max_quantity_per_item' => env('CART_MAX_QUANTITY_PER_ITEM', 100),

    // Maximum items per cart
    'max_items_per_cart' => env('CART_MAX_ITEMS_PER_CART', 50),

    // Cart session timeout (in minutes)
    'session_timeout' => env('CART_SESSION_TIMEOUT', 1440), // 24 hours

    // Auto-clear empty carts after (in minutes)
    'auto_clear_empty_carts' => env('CART_AUTO_CLEAR_EMPTY', 10080), // 7 days

    // Default currency
    'default_currency' => env('CART_DEFAULT_CURRENCY', 'USD'),

    // Enable cart events
    'enable_events' => env('CART_ENABLE_EVENTS', true),

    // Enable cart logging
    'enable_logging' => env('CART_ENABLE_LOGGING', true),

    // Cart validation rules
    'validation' => [
        'quantity' => [
            'min' => 1,
            'max' => 100
        ],
        'special_instructions' => [
            'max_length' => 500
        ]
    ],

    // Cart cleanup settings
    'cleanup' => [
        'enabled' => env('CART_CLEANUP_ENABLED', true),
        'schedule' => env('CART_CLEANUP_SCHEDULE', 'daily'),
        'keep_days' => env('CART_CLEANUP_KEEP_DAYS', 30)
    ],

    // Cache settings
    'cache' => [
        'enabled' => env('CART_CACHE_ENABLED', false),
        'ttl' => env('CART_CACHE_TTL', 300), // 5 minutes
        'prefix' => env('CART_CACHE_PREFIX', 'cart_')
    ]
];