<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reports Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Reports module.
    |
    */

    'name' => 'Reports',
    'version' => '1.0.0',
    'description' => 'Reports module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Reports module.
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
    | Here you may define all of the dependencies for the Reports module.
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
    | Here you may define all of the permissions for the Reports module.
    |
    */

    'permissions' => [
        'view' => 'View Reports',
        'create' => 'Create Reports',
        'edit' => 'Edit Reports',
        'delete' => 'Delete Reports',
    ],
]; 