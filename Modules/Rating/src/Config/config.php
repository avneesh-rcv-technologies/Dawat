<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rating Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Rating module.
    |
    */

    'name' => 'Rating',
    'version' => '1.0.0',
    'description' => 'Rating module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Rating module.
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
    | Here you may define all of the dependencies for the Rating module.
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
    | Here you may define all of the permissions for the Rating module.
    |
    */

    'permissions' => [
        'view' => 'View Rating',
        'create' => 'Create Rating',
        'edit' => 'Edit Rating',
        'delete' => 'Delete Rating',
    ],
]; 