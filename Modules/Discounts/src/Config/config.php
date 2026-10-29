<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Discounts Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Discounts module.
    |
    */

    'name' => 'Discounts',
    'version' => '1.0.0',
    'description' => 'Discounts module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Discounts module.
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
    | Here you may define all of the dependencies for the Discounts module.
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
    | Here you may define all of the permissions for the Discounts module.
    |
    */

    'permissions' => [
        'view' => 'View Discounts',
        'create' => 'Create Discounts',
        'edit' => 'Edit Discounts',
        'delete' => 'Delete Discounts',
    ],
]; 