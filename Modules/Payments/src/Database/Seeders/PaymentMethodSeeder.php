<?php

// database/seeders/PaymentMethodSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Payment\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run()
    {
        PaymentMethod::create([
            'name' => 'Stripe',
            'processor' => 'stripe',
            'is_active' => true,
            'configuration' => [
                'publishable_key' => '',
                'secret_key' => '',
                'webhook_secret' => ''
            ]
        ]);

        PaymentMethod::create([
            'name' => 'PayPal',
            'processor' => 'paypal',
            'is_active' => true,
            'configuration' => [
                'client_id' => '',
                'client_secret' => '',
                'mode' => 'sandbox'
            ]
        ]);
    }
}
