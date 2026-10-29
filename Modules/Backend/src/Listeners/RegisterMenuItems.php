<?php

namespace Modules\Backend\Listeners;

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
        // Add default menu items
        $event->addItem([
            'label' => 'Dashboard',
            'route' => 'dashboard.index',
            'icon' => 'space_dashboard',
            'permission' => 'dashboard.index',
            'position' => 1,
        ]);
    }
}
