<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FoodManagements Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the FoodManagements module.
    |
    */

    'name' => 'FoodManagements',
    'version' => '1.0.0',
    'description' => 'FoodManagements module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the FoodManagements module.
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
    | Here you may define all of the dependencies for the FoodManagements module.
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
    | Here you may define all of the permissions for the FoodManagements module.
    |
    */

    'permissions' => [
        'view' => 'View FoodManagements',
        'create' => 'Create FoodManagements',
        'edit' => 'Edit FoodManagements',
        'delete' => 'Delete FoodManagements',
    ],
]; 