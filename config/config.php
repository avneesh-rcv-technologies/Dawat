<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Themes Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Themes module.
    |
    */

    'name' => 'Themes',
    'version' => '1.0.0',
    'description' => 'Themes module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Themes module.
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
    | Here you may define all of the dependencies for the Themes module.
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
    | Here you may define all of the permissions for the Themes module.
    |
    */

    'permissions' => [
        'view' => 'View Themes',
        'create' => 'Create Themes',
        'edit' => 'Edit Themes',
        'delete' => 'Delete Themes',
    ],
]; 