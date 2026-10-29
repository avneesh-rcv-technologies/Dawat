<?php

namespace Modules\Orders\Listeners;

use Modules\Core\Events\RegisterMenu;

class RegisterMenuItems
{
    /**
     * Handle the event.
     *
     * @param  \Modules\Core\Events\RegisterMenu  $event
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
    }
}
