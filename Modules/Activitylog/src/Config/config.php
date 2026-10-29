<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activitylog Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Activitylog module.
    |
    */

    'name' => 'Activitylog',
    'version' => '1.0.0',
    'description' => 'Activitylog module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Activitylog module.
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
    | Here you may define all of the dependencies for the Activitylog module.
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
    | Here you may define all of the permissions for the Activitylog module.
    |
    */

    'permissions' => [
        'view' => 'View Activitylog',
        'create' => 'Create Activitylog',
        'edit' => 'Edit Activitylog',
        'delete' => 'Delete Activitylog',
    ],
]; 