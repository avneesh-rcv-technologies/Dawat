<?php

use Modules\Payments\Processors\StripeProcessor;

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Payment module.
    |
    */

    'name' => 'Payment',
    'version' => '1.0.0',
    'description' => 'Payment module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Payment module.
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
    | Here you may define all of the dependencies for the Payment module.
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
    | Here you may define all of the permissions for the Payment module.
    |
    */

    'permissions' => [
        'view' => 'View Payment',
        'create' => 'Create Payment',
        'edit' => 'Edit Payment',
        'delete' => 'Delete Payment',
    ]
]; 