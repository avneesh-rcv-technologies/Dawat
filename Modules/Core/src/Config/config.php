<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Core Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Core module.
    |
    */

    'name' => 'Core',
    'version' => '1.0.0',
    'description' => 'Core module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Core module.
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
    | Here you may define all of the dependencies for the Core module.
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
    | Here you may define all of the permissions for the Core module.
    |
    */

    'permissions' => [
        'view' => 'View Core',
        'create' => 'Create Core',
        'edit' => 'Edit Core',
        'delete' => 'Delete Core',
    ],
]; 