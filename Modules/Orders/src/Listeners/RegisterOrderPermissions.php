<?php

namespace Modules\Orders\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterOrderPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Order Management → Orders
         */
        $event->registerArray([
            'name' => 'Order Management',
            'code' => 'orders',
            'guard' => ['admin'],
            'key' => [
                'admin.orders.index' => 'View Orders',
                'admin.orders.create' => 'Create Order',
                'admin.orders.store' => 'Store Order',
                'admin.orders.show' => 'View Order Details',
                'admin.orders.edit' => 'Edit Order',
                'admin.orders.update' => 'Update Order',
                'admin.orders.destroy' => 'Delete Order',
                'admin.orders.bulk-delete' => 'Bulk Delete Orders',
                'admin.orders.toggle-status' => 'Update Order Status',
                'admin.orders.view-attachment' => 'View Order Attachments',
                'admin.orders.store-attachment' => 'Upload Order Attachments',
                'admin.orders.setPaymentMethod' => 'Set Payment Method',
            ]
        ]);

        /**
         * Order Management → Transactions
         */
        $event->registerArray([
            'name' => 'Order Management',
            'code' => 'transactions',
            'guard' => ['admin'],
            'key' => [
                'admin.transactions.index' => 'View Transactions',
                'admin.transactions.create' => 'Create Transaction',
                'admin.transactions.store' => 'Store Transaction',
                'admin.transactions.show' => 'View Transaction Details',
                'admin.transactions.edit' => 'Edit Transaction',
                'admin.transactions.update' => 'Update Transaction',
                'admin.transactions.destroy' => 'Delete Transaction',
                'admin.transactions.reload' => 'Reload Transaction',
                'admin.transactions.bulk-delete' => 'Bulk Delete Transactions',
                'admin.transactions.toggle-status' => 'Update Transaction Status',
            ]
        ]);

        /**
         * Order Management → Order Statuses
         */
        $event->registerArray([
            'name' => 'Order Management',
            'code' => 'order_statuses',
            'guard' => ['admin'],
            'key' => [
                'admin.orders-statuses.index' => 'View Order Statuses',
                'admin.orders-statuses.create' => 'Create Order Status',
                'admin.orders-statuses.store' => 'Store Order Status',
                'admin.orders-statuses.show' => 'View Order Status Details',
                'admin.orders-statuses.edit' => 'Edit Order Status',
                'admin.orders-statuses.update' => 'Update Order Status',
                'admin.orders-statuses.destroy' => 'Delete Order Status',
                'admin.orders.update-status' => 'Update Order Status from detail',
                
            ]
        ]);
    }
}
