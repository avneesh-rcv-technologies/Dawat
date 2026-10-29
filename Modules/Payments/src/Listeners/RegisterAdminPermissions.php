<?php

namespace Modules\Payments\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event): void
    {
        $event->registerArray([
            'name' => 'Payments',
            'code' => 'payments',
            'guard' => ['admin'],
            'key' => [
                'admin.payments.index' => 'View Payments',
                'admin.payments.create' => 'Create Payments',
                'admin.payments.store' => 'Store Payments',
                'admin.payments.show' => 'View Payment Details',
                'admin.payments.edit' => 'Edit Payments',
                'admin.payments.update' => 'Update Payments',
                'admin.payments.destroy' => 'Delete Payments',
            ],
        ]);

        $event->registerArray([
            'name' => 'Payments',
            'code' => 'payment_methods',
            'guard' => ['admin'],
            'key' => [
                'admin.payment_methods.index' => 'View Payment Methods',
                'admin.payment_methods.create' => 'Create Payment Methods',
                'admin.payment_methods.store' => 'Store Payment Methods',
                'admin.payment_methods.show' => 'View Payment Method Details',
                'admin.payment_methods.edit' => 'Edit Payment Methods',
                'admin.payment_methods.update' => 'Update Payment Methods',
                'admin.payment_methods.destroy' => 'Delete Payment Methods',
                'admin.payment_methods.config' => 'Load Payment Method Configuration',
            ],
        ]);
    }
}
