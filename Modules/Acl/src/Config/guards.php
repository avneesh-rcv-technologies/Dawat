<?php

return [
    'guards' => [
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
            'prefix' => 'admin',
        ],
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
            'prefix' => 'customer',
        ],
    ],

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => 'Modules\\Acl\\Models\\Admin',
        ],
        'customers' => [
            'driver' => 'eloquent',
            'model' => 'Modules\\Acl\\Models\\Customer',
        ],
    ],

    'passwords' => [
        'admins' => [
            'provider' => 'admins',
            'table' => 'admin_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'customer' => [
            'provider' => 'customers',
            'table' => 'customer_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
