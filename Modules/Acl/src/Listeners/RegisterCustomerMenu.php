<?php

namespace Modules\Acl\Listeners;

use Modules\Core\Events\RegisterMenu;

class RegisterCustomerMenu
{
    /**
     * Handle the event.
     *
     * @param  \Modules\Core\Events\RegisterMenu  $event
     * @return void
     */
    public function handle(RegisterMenu $event)
    {
        // Customers Menu
        $event->addItem([
            'label' => 'Customers',
            'route' => 'admin.customer.index',
            'icon' => 'people',
            'permission' => 'admin.customer.index',
            'position' => 30
        ]);

        // Support Messages Menu
        // $event->addItem([
        //     'label' => 'Support',
        //     'route' => 'support.index',
        //     'icon' => 'support_agent',
        //     'permission' => 'support.index',
        //     'position' => 31,
        // ]);
    }
}
