<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notifications Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Notifications module.
    |
    */

    'name' => 'Notifications',
    'version' => '1.0.0',
    'description' => 'Notifications module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Notifications module.
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
    | Here you may define all of the dependencies for the Notifications module.
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
    | Here you may define all of the permissions for the Notifications module.
    |
    */

    'permissions' => [
        'view' => 'View Notifications',
        'create' => 'Create Notifications',
        'edit' => 'Edit Notifications',
        'delete' => 'Delete Notifications',
    ],
]; 