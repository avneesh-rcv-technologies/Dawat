<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Filemanager Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Filemanager module.
    |
    */

    'name' => 'Filemanager',
    'version' => '1.0.0',
    'description' => 'Filemanager module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Filemanager module.
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
    | Here you may define all of the dependencies for the Filemanager module.
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
    | Here you may define all of the permissions for the Filemanager module.
    |
    */

    'permissions' => [
        'view' => 'View Filemanager',
        'create' => 'Create Filemanager',
        'edit' => 'Edit Filemanager',
        'delete' => 'Delete Filemanager',
    ],
]; 