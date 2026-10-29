<?php

return [
    /*
    |--------------------------------------------------------------------------
    | EmailNotification Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the EmailNotification module.
    |
    */

    'name' => 'EmailNotification',
    'version' => '1.0.0',
    'description' => 'EmailNotification module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the EmailNotification module.
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
    | Here you may define all of the dependencies for the EmailNotification module.
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
    | Here you may define all of the permissions for the EmailNotification module.
    |
    */

    'permissions' => [
        'view' => 'View EmailNotification',
        'create' => 'Create EmailNotification',
        'edit' => 'Edit EmailNotification',
        'delete' => 'Delete EmailNotification',
    ],
]; 