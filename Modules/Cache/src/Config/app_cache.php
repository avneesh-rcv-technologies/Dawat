<?php

return [
    /*
    |--------------------------------------------------------------------------
    | App cache (profile-based Redis cache)
    |--------------------------------------------------------------------------
    |
    | Profiles define TTL policy. Entry names are chosen at call time:
    |   AppCache::remember('currencies', 'currencies:list', fn () => ...)
    |   AppCache::flush('currencies');
    |
    | Redis keys:
    |   {prefix}:{profile}:{name}      → data
    |   {registry_prefix}:{profile}    → list of entry names
    |   {meta_prefix}:{profile}:{name} → size / written_at (admin)
    |
    */

    'enabled' => env('APP_CACHE_ENABLED', true),

    'store' => env('CACHE_STORE', 'redis'),

    'prefix' => 'app',

    'registry_prefix' => 'app:registry',

    'meta_prefix' => 'app:meta',

    'profiles' => [
        'currencies' => [
            'ttl' => 3600,
            'forever' => false,
            'label' => 'Currencies & exchange rates',
            'admin_clearable' => true,
        ],

        'merchant_stores' => [
            'ttl' => 3600,
            'forever' => false,
            'label' => 'Merchant stores',
            'admin_clearable' => true,
        ],

        'languages' => [
            'ttl' => 86400,
            'forever' => false,
            'label' => 'Active languages',
            'admin_clearable' => true,
        ],

        'translations' => [
            'ttl' => null,
            'forever' => true,
            'label' => 'Synced translations per locale',
            'admin_clearable' => true,
        ],

        'plans' => [
            'ttl' => 3600,
            'forever' => false,
            'label' => 'Plans & pricing (Frontend)',
            'admin_clearable' => true,
        ],
   'checkout' => [
            'ttl' => 86400,
            'forever' => false,
            'label' => 'Checkout idempotency',
            'admin_clearable' => true,
        ],

        'features' => [
            'ttl' => 84600, // 1 day
            'forever' => false,
            'label' => 'Subscription features',
            'admin_clearable' => true,
        ],
        'seo' => [
            'ttl' => 3600,
            'forever' => false,
            'label' => 'SEO settings / redirects / sitemap',
            'admin_clearable' => true,
        ],
         'frontend' => [
            'ttl' => 84600, // 1 day
            'forever' => false,
            'label' => 'Frontend blogs / CMS pages',
            'admin_clearable' => true,
        ],

        'tax' => [
            'ttl' => 3600,
            'forever' => false,
            'label' => 'Tax rates',
            'admin_clearable' => true,
        ],
         'payments' => [
            'ttl' => 3600,
            'forever' => false,
            'label' => 'Payment methods & configurations',
            'admin_clearable' => true,
        ],

        // Activitylog: short-lived request-log buffers drained by queued jobs.
        'activity' => [
            'ttl' => 600,
            'forever' => false,
            'label' => 'Activity log request buffers',
            'admin_clearable' => false,
        ],

        // Activitylog: blocked / allowed IP lists used by threat protection.
        'security' => [
            'ttl' => 3600,
            'forever' => false,
            'label' => 'Blocked & allowed IP lists',
            'admin_clearable' => true,
        ],
    ],
];
