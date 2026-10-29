<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cart Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Cart module.
    |
    */

    'name' => 'Cart',
    'version' => '1.0.0',
    'description' => 'Cart module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Cart module.
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
    | Here you may define all of the dependencies for the Cart module.
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
    | Here you may define all of the permissions for the Cart module.
    |
    */

    'permissions' => [
        'view' => 'View Cart',
        'create' => 'Create Cart',
        'edit' => 'Edit Cart',
        'delete' => 'Delete Cart',
    ],
]; 