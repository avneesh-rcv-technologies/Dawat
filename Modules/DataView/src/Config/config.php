<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DataView Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the DataView module.
    |
    */

    'name' => 'DataView',
    'version' => '1.0.0',
    'description' => 'DataView module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the DataView module.
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
    | Here you may define all of the dependencies for the DataView module.
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
    | Here you may define all of the permissions for the DataView module.
    |
    */

    'permissions' => [
        'view' => 'View DataView',
        'create' => 'Create DataView',
        'edit' => 'Edit DataView',
        'delete' => 'Delete DataView',
    ],
]; 