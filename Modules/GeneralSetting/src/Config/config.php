<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GeneralSetting Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the GeneralSetting module.
    |
    */

    'name' => 'GeneralSetting',
    'version' => '1.0.0',
    'description' => 'GeneralSetting module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the GeneralSetting module.
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
    | Here you may define all of the dependencies for the GeneralSetting module.
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
    | Here you may define all of the permissions for the GeneralSetting module.
    |
    */

    'permissions' => [
        'view' => 'View GeneralSetting',
        'create' => 'Create GeneralSetting',
        'edit' => 'Edit GeneralSetting',
        'delete' => 'Delete GeneralSetting',
    ],
]; 