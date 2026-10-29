<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Checkout Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Checkout module.
    |
    */

    'name' => 'Checkout',
    'version' => '1.0.0',
    'description' => 'Checkout module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Checkout module.
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
    | Here you may define all of the dependencies for the Checkout module.
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
    | Here you may define all of the permissions for the Checkout module.
    |
    */

    'permissions' => [
        'view' => 'View Checkout',
        'create' => 'Create Checkout',
        'edit' => 'Edit Checkout',
        'delete' => 'Delete Checkout',
    ],
]; 