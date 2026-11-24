<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Acl Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the configuration settings for the Acl module.
    |
    */

    'name' => 'Acl',
    'version' => '1.0.0',
    'description' => 'Acl module for the application',
    'author' => 'Your Name',
    'email' => 'your.email@example.com',
    'website' => 'https://example.com',

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the settings for the Acl module.
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
    | Here you may define all of the dependencies for the Acl module.
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
    | Here you may define all of the permissions for the Acl module.
    |
    */

    'permissions' => [
        'view' => 'View Acl',
        'create' => 'Create Acl',
        'edit' => 'Edit Acl',
        'delete' => 'Delete Acl',
    ],

    /*
    |--------------------------------------------------------------------------
    | Web Password Transport (admin + restaurant partner)
    |--------------------------------------------------------------------------
    |
    | Web login / reset-password forms SHA-256 hash the password in the browser
    | and send it with a single-use, session-bound nonce inside an RSA-OAEP
    | envelope (see PasswordTransportService). Set ADMIN_PASSWORD_TRANSPORT_KEY
    | (PEM) when running multiple servers so they share one key.
    |
    */

    'password_transport' => [
        'nonce_ttl' => (int) env('ADMIN_PASSWORD_TRANSPORT_NONCE_TTL', 300),
        'private_key' => env('ADMIN_PASSWORD_TRANSPORT_KEY', ''),
        'private_key_path' => env('ADMIN_PASSWORD_TRANSPORT_KEY_PATH'),
        'fields' => ['password', 'password_confirmation', 'current_password'],
    ],
]; 