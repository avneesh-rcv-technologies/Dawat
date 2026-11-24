<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DietaryManagment Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the DietaryManagment module.
    |
    */

    'name' => 'DietaryManagment',
    'version' => '1.0.0',
    'description' => 'DietaryManagment module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the DietaryManagment module.
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
    | Required Modules
    |--------------------------------------------------------------------------
    |
    | Here you may list module names that DietaryManagment requires.
    |
    */

    'dependents' => [
        // 'Core',
        // 'Auth',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Permissions
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the permissions for the DietaryManagment module.
    |
    */

    'permissions' => [
        'view' => 'View DietaryManagment',
        'create' => 'Create DietaryManagment',
        'edit' => 'Edit DietaryManagment',
        'delete' => 'Delete DietaryManagment',
    ],
]; 
