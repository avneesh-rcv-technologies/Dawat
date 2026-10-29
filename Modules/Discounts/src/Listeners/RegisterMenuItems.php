<?php

namespace Modules\Discounts\Listeners;

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
        //     'label' => 'Discount & Coupons',
        //     'icon' => 'tag',
        //     'permission' => 'discount.index',
        //     'position' => 98,
        //     'route' => 'discount.index'
        // ]);
    }
}
