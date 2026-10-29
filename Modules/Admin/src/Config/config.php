<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Admin module.
    |
    */

    'name' => 'Admin',
    'version' => '1.0.0',
    'description' => 'Admin module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Admin module.
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
    | Here you may define all of the dependencies for the Admin module.
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
    | Here you may define all of the permissions for the Admin module.
    |
    */

    'permissions' => [
        'view' => 'View Admin',
        'create' => 'Create Admin',
        'edit' => 'Edit Admin',
        'delete' => 'Delete Admin',
    ],

     'statuses' => [
        // Order Statuses
        ['type_code' => 'O', 'status_code' => 'O',  'name' => 'New'],
        ['type_code' => 'O', 'status_code' => 'P',  'name' => 'Pending'],
        ['type_code' => 'O', 'status_code' => 'A',  'name' => 'Processing'],
        ['type_code' => 'O', 'status_code' => 'J',  'name' => 'Completed'],
        ['type_code' => 'O', 'status_code' => 'N',  'name' => 'Cancelled'],
        ['type_code' => 'O', 'status_code' => 'R',  'name' => 'Returned'],

        // Shipping Statuses
        ['type_code' => 'S', 'status_code' => 'P',  'name' => 'Picked'],
        ['type_code' => 'S', 'status_code' => 'K',  'name' => 'Packed'],
        ['type_code' => 'S', 'status_code' => 'S',  'name' => 'Shipped'],
        ['type_code' => 'S', 'status_code' => 'D',  'name' => 'Delivered'],
        ['type_code' => 'S', 'status_code' => 'R',  'name' => 'Returned to Sender'],
    ]
]; 