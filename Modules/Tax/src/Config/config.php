<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tax Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Tax module.
    |
    */

    'name' => 'Tax',
    'version' => '1.0.0',
    'description' => 'Tax module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Tax module.
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
    | Here you may define all of the dependencies for the Tax module.
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
    | Here you may define all of the permissions for the Tax module.
    |
    */

    'permissions' => [
        'view' => 'View Tax',
        'create' => 'Create Tax',
        'edit' => 'Edit Tax',
        'delete' => 'Delete Tax',
    ],
]; 