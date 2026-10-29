<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cms Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Cms module.
    |
    */

    'name' => 'Cms',
    'version' => '1.0.0',
    'description' => 'Cms module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Cms module.
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
    | Here you may define all of the dependencies for the Cms module.
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
    | Here you may define all of the permissions for the Cms module.
    |
    */

    'permissions' => [
        'view' => 'View Cms',
        'create' => 'Create Cms',
        'edit' => 'Edit Cms',
        'delete' => 'Delete Cms',
    ],
]; 