<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Orders Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Orders module.
    |
    */

    'name' => 'Orders',
    'version' => '1.0.0',
    'description' => 'Orders module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Orders module.
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
    | Here you may define all of the dependencies for the Orders module.
    |
    */

    'dependencies' => [
        // 'Core',
        // 'Auth',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Permissions
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the permissions for the Orders module.
    |
    */

    'permissions' => [
        'view' => 'View Orders',
        'create' => 'Create Orders',
        'edit' => 'Edit Orders',
        'delete' => 'Delete Orders',
    ],
]; 