<?php

namespace Modules\Tax\Listeners;

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
        // $event->addItem([
        //     'label' => 'Tax',
        //     'icon' => 'receipt',
        //     'permission' => 'admin.tax.index',
        //     'position' => 98,
        //     'route' => 'admin.tax.index'
        //     // 'children' => [
        //     //     [
        //     //         'label' => 'Manage Food Categories',
        //     //         'route' => 'admin.foodmanagements.category.index',
        //     //         'guard' => 'admin',
        //     //         'permission' => 'admin.foodmanagements.index',
        //     //     ],
               
        //     // ],
        // ]);
    }
}
