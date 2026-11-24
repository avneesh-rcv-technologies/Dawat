<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Cache module.
    |
    */

    'name' => 'Cache',
    'version' => '1.0.0',
    'description' => 'Cache module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Cache module.
    |
    */

    'settings' => [
        'enabled' => true,
        'debug' => false,
        'cache' => true,
        'cache_ttl' => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Dependencies
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the dependencies for the Cache module.
    |
    */

    'dependencies' => [
        // Profile TTLs / registry live in Config/app_cache.php → config('cache::app_cache')
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Permissions
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the permissions for the Cache module.
    |
    */

    'permissions' => [
        'cache.index' => 'View cache profiles',
        'cache.show' => 'View cache keys',
        'cache.flush' => 'Flush cache profile',
        'cache.flush-all' => 'Flush all clearable caches',
        'cache.bulk-flush' => 'Bulk flush cache profiles',
        'cache.forget' => 'Delete individual cache keys',
    ],
];
