<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Countries Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Countries module.
    |
    */

    'name' => 'Countries',
    'version' => '1.0.0',
    'description' => 'Countries module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Countries module.
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
    | Here you may define all of the dependencies for the Countries module.
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
    | Here you may define all of the permissions for the Countries module.
    |
    */

    'permissions' => [
        'view' => 'View Countries',
        'create' => 'Create Countries',
        'edit' => 'Edit Countries',
        'delete' => 'Delete Countries',
    ],
]; 