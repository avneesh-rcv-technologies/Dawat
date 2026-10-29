<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WebPushNotification Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the WebPushNotification module.
    |
    */

    'name' => 'WebPushNotification',
    'version' => '1.0.0',
    'description' => 'WebPushNotification module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the WebPushNotification module.
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
    | Here you may define all of the dependencies for the WebPushNotification module.
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
    | Here you may define all of the permissions for the WebPushNotification module.
    |
    */

    'permissions' => [
        'view' => 'View WebPushNotification',
        'create' => 'Create WebPushNotification',
        'edit' => 'Edit WebPushNotification',
        'delete' => 'Delete WebPushNotification',
    ],
]; 