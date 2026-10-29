<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
        'customer' => [
            'driver' => 'sanctum',
            'provider' => 'customers',
        ],
        'user' => [
            'driver' => 'session',
            'provider' => 'user',
        ]
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => 'App\\Models\\User',
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model' => 'Modules\\Acl\\Models\\Admin',
        ],
        'customers' => [
            'driver' => 'eloquent',
            'model' => 'Modules\\Acl\\Models\\Customer',
        ]
    ],
    'passwords' => [
        'admins' => [
            'provider' => 'admins',
            'table' => 'admin_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
    'password_timeout' => 10800,
];
