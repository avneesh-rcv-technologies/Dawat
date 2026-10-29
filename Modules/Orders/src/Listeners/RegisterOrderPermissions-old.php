<?php

namespace Modules\Orders\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterOrderPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Settings → Orders
         */
        $event->registerArray([
            'name' => 'Orders',
            'code' => 'orders',
            'key' => [
                'admin.orders.index' => 'View Orders',
                'admin.orders.create' => 'Create Orders',
                'admin.orders-statuses.index' => 'View Order Statuses',
                'admin.orders-statuses.create' => 'Create Order Status',
                'admin.orders-statuses.edit' => 'Edit Order Status',
                'admin.orders-statuses.destroy' => 'Delete Order Status',
                
            ]
        ]);
    }
}
