<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blogs Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Blogs module.
    |
    */

    'name' => 'Blogs',
    'version' => '1.0.0',
    'description' => 'Blogs module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Blogs module.
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
    | Here you may define all of the dependencies for the Blogs module.
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
    | Here you may define all of the permissions for the Blogs module.
    |
    */

    'permissions' => [
        'view' => 'View Blogs',
        'create' => 'Create Blogs',
        'edit' => 'Edit Blogs',
        'delete' => 'Delete Blogs',
    ],
]; 