<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Twilio Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Twilio module.
    |
    */

    'name' => 'Twilio',
    'version' => '1.0.0',
    'description' => 'Twilio module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Twilio module.
    |
    */

    'settings' => [
        'enabled' => true,
        'debug' => false,
        'cache' => true,
        'cache_ttl' => 3600,

        /*
        |--------------------------------------------------------------------------
        | Messaging Automation Settings
        |--------------------------------------------------------------------------
        |
        | Configure settings for automated messaging responses.
        |
        */

        'automation' => [
            'enabled' => true,
            'enable_general_chatgpt' => false,
            'log_unmatched_messages' => true,
            'default_priority' => 0,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Dependencies
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the dependencies for the Twilio module.
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
    | Here you may define all of the permissions for the Twilio module.
    |
    */

    'permissions' => [
        'view' => 'View Twilio',
        'create' => 'Create Twilio',
        'edit' => 'Edit Twilio',
        'delete' => 'Delete Twilio',
        'automation' => 'Manage Automation Rules',
    ],
]; 