<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shipping Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Shipping module.
    |
    */

    'name' => 'Shipping',
    'version' => '1.0.0',
    'description' => 'Shipping module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Shipping module.
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
    | Here you may define all of the dependencies for the Shipping module.
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
    | Here you may define all of the permissions for the Shipping module.
    |
    */

    'permissions' => [
        'view' => 'View Shipping',
        'create' => 'Create Shipping',
        'edit' => 'Edit Shipping',
        'delete' => 'Delete Shipping',
    ],
]; 