<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscriptions Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Subscriptions module.
    |
    */

    'name' => 'Subscriptions',
    'version' => '1.0.0',
    'description' => 'Subscriptions module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    'subscription_default_class' => \Modules\Acl\Models\Admin::class,

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Subscriptions module.
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
    | Here you may define all of the dependencies for the Subscriptions module.
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
    | Here you may define all of the permissions for the Subscriptions module.
    |
    */

    'permissions' => [
        'view' => 'View Subscriptions',
        'create' => 'Create Subscriptions',
        'edit' => 'Edit Subscriptions',
        'delete' => 'Delete Subscriptions',
    ],
]; 