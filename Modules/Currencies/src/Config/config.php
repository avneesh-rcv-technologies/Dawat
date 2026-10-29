<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Currencies Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Currencies module.
    |
    */

    'name' => 'Currencies',
    'version' => '1.0.0',
    'description' => 'Currencies module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Currencies module.
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
    | Here you may define all of the dependencies for the Currencies module.
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
    | Here you may define all of the permissions for the Currencies module.
    |
    */

    'permissions' => [
        'view' => 'View Currencies',
        'create' => 'Create Currencies',
        'edit' => 'Edit Currencies',
        'delete' => 'Delete Currencies',
    ],
]; 