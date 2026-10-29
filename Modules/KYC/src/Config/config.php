<?php

return [
    /*
    |--------------------------------------------------------------------------
    | KYC Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the KYC module.
    |
    */

    'name' => 'KYC',
    'version' => '1.0.0',
    'description' => 'KYC module for restaurant owner onboarding and compliance',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the KYC module.
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
    | Here you may define all of the dependencies for the KYC module.
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
    | Here you may define all of the permissions for the KYC module.
    |
    */

    'permissions' => [
        'view' => 'View KYC',
        'create' => 'Create KYC',
        'edit' => 'Edit KYC',
        'delete' => 'Delete KYC',
    ],
]; 