<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shipping Module Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Shipping module.
    |
    */

    'default_currency' => 'USD',
    
    'processors' => [
        'takeaway' => [
            'enabled' => true,
            'class' => \Modules\Shipping\Processors\TakeawayProcessor::class,
        ],
        'delivery' => [
            'enabled' => true,
            'class' => \Modules\Shipping\Processors\DeliveryProcessor::class,
        ],
    ],

    'default_settings' => [
        'takeaway' => [
            'is_enabled' => true,
            'takeaway_fee' => 0,
            'preparation_time' => 15,
            'buffer_time' => 5,
            'pickup_location' => '',
            'pickup_instructions' => '',
            'contact_phone' => '',
            'operating_hours' => [
                'open' => '09:00',
                'close' => '22:00',
            ],
        ],
        'delivery' => [
            'is_enabled' => true,
            'base_delivery_fee' => 5.00,
            'minimum_order' => 10.00,
            'free_delivery_threshold' => null,
            'delivery_radius' => 10,
            'preparation_time' => 20,
            'buffer_time' => 10,
            'per_km_rate' => 1.00,
            'delivery_hours' => [
                'start' => '10:00',
                'end' => '23:00',
            ],
            'store_coordinates' => [
                'lat' => null,
                'lng' => null,
            ],
            'delivery_zones' => [],
        ],
    ],

    'tracking' => [
        'prefix' => [
            'takeaway' => 'PICKUP-',
            'delivery' => 'DEL-',
        ],
    ],

    'notifications' => [
        'status_updates' => true,
        'delivery_reminders' => true,
    ],

    'external_tracking' => [
        'enabled' => env('SHIPPING_EXTERNAL_TRACKING_ENABLED', false),
        'api_url' => env('SHIPPING_EXTERNAL_TRACKING_API_URL'),
        'api_key' => env('SHIPPING_EXTERNAL_TRACKING_API_KEY'),
        'webhook_url' => env('SHIPPING_EXTERNAL_TRACKING_WEBHOOK_URL'),
    ],

    'metrics' => [
        'cache_duration' => 24 * 60, // 24 hours in minutes
        'track_performance' => true,
        'track_cancellations' => true,
    ],
];