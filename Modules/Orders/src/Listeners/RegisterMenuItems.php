<?php

namespace Modules\Orders\Listeners;

use Modules\Core\Events\RegisterMenu;
use Modules\Orders\Services\OrderCommissionAccess;
use Modules\Payments\Services\RefundAccess;

class RegisterMenuItems
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(RegisterMenu $event)
    {
        $event->addItem([
            'label' => 'Order Management',
            'icon' => 'receipt',
            'permission' => 'admin.orders.index',
            'position' => 79,
            'children' => [
                [
                    'label' => 'Orders',
                    'route' => 'admin.orders.index',
                    'guard' => 'admin',
                    'permission' => 'admin.orders.index',
                ],
                [
                    'label' => 'Transactions',
                    'route' => 'admin.transactions.index',
                    'guard' => 'admin',
                    'permission' => 'admin.transactions.index',
                ],
            ],
        ]);
        $event->addItem([
            'label' => 'Payouts',
            'route' => 'admin.order-commissions.index',
            'icon' => 'account_balance_wallet',
            'visibility' => [OrderCommissionAccess::class, 'visibleInSidebar'],
            'position' => 81,
        ]);
        $event->addItem([
            'label' => 'Refunds',
            'route' => 'admin.refunds.index',
            'icon' => 'undo',
            'visibility' => [RefundAccess::class, 'visibleInSidebar'],
            'position' => 80,
        ]);
    }
}
