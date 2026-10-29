<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backend Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Backend module.
    |
    */

    'name' => 'Backend',
    'version' => '1.0.0',
    'description' => 'Backend module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Backend module.
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
    | Here you may define all of the dependencies for the Backend module.
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
    | Here you may define all of the permissions for the Backend module.
    |
    */

    'permissions' => [
        'view' => 'View Backend',
        'create' => 'Create Backend',
        'edit' => 'Edit Backend',
        'delete' => 'Delete Backend',
    ],
]; 