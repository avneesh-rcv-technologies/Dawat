<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Website Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Website module.
    |
    */

    'name' => 'Website',
    'version' => '1.0.0',
    'description' => 'Website module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Website module.
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
    | Here you may define all of the dependencies for the Website module.
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
    | Here you may define all of the permissions for the Website module.
    |
    */

    'permissions' => [
        'view' => 'View Website',
        'create' => 'Create Website',
        'edit' => 'Edit Website',
        'delete' => 'Delete Website',
    ],
]; 