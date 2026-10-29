<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Stripe module.
    |
    */

    'name' => 'Stripe',
    'version' => '1.0.0',
    'description' => 'Stripe module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Stripe module.
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
    | Here you may define all of the dependencies for the Stripe module.
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
    | Here you may define all of the permissions for the Stripe module.
    |
    */

    'permissions' => [
        'view' => 'View Stripe',
        'create' => 'Create Stripe',
        'edit' => 'Edit Stripe',
        'delete' => 'Delete Stripe',
    ],
]; 