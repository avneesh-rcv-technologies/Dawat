<?php

namespace Modules\Payments\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterPaymentSettings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register([
            'category_code' => "general",
            'title' => 'Payment Methods',
            'description' => 'Set up payment gateways like PayPal, Stripe, and others.',
            'route' => route('admin.payment_methods.index'),
            'icon' => 'payments',
            'permission' => 'admin.payment_methods.index'
        ]);
    }
}
