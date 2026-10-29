<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Restaurant Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Restaurant module.
    |
    */

    'name' => 'Restaurant',
    'version' => '1.0.0',
    'description' => 'Restaurant module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Restaurant module.
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
    | Here you may define all of the dependencies for the Restaurant module.
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
    | Here you may define all of the permissions for the Restaurant module.
    |
    */

    'permissions' => [
        'view' => 'View Restaurant',
        'create' => 'Create Restaurant',
        'edit' => 'Edit Restaurant',
        'delete' => 'Delete Restaurant',
    ],
]; 