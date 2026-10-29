<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Acl Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Acl module.
    |
    */

    'name' => 'Acl',
    'version' => '1.0.0',
    'description' => 'Acl module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Acl module.
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
    | Here you may define all of the dependencies for the Acl module.
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
    | Here you may define all of the permissions for the Acl module.
    |
    */

    'permissions' => [
        'view' => 'View Acl',
        'create' => 'Create Acl',
        'edit' => 'Edit Acl',
        'delete' => 'Delete Acl',
    ],
]; 