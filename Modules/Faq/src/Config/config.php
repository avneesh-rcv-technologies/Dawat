<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Faq Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Faq module.
    |
    */

    'name' => 'Faq',
    'version' => '1.0.0',
    'description' => 'Faq module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Faq module.
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
    | Here you may define all of the dependencies for the Faq module.
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
    | Here you may define all of the permissions for the Faq module.
    |
    */

    'permissions' => [
        'view' => 'View Faq',
        'create' => 'Create Faq',
        'edit' => 'Edit Faq',
        'delete' => 'Delete Faq',
    ],
]; 