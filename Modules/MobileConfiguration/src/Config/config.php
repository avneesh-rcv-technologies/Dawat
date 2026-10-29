<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MobileConfiguration Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the MobileConfiguration module.
    |
    */

    'name' => 'MobileConfiguration',
    'version' => '1.0.0',
    'description' => 'MobileConfiguration module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the MobileConfiguration module.
    |
    */

    'settings' => [
        'enabled' => true,
        'debug' => false,
        'cache' => true,
        'cache_ttl' => 3600,
    ],

    // caching options for homepage layouts retrieval
    'homepage_layouts' => [
        'cache' => true,
        'cache_ttl' => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Dependencies
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the dependencies for the MobileConfiguration module.
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
    | Here you may define all of the permissions for the MobileConfiguration module.
    |
    */

    'permissions' => [
        'view' => 'View MobileConfiguration',
        'create' => 'Create MobileConfiguration',
        'edit' => 'Edit MobileConfiguration',
        'delete' => 'Delete MobileConfiguration',
    ],
]; 