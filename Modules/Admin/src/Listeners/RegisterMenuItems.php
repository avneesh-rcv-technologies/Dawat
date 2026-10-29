<?php

namespace Modules\Admin\Listeners;

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
        // Add Gallery menu item
        $event->addItem([
            'label' => 'Gallery',
            'route' => 'gallery.index',
            'icon' => 'collections',
            'permission' => 'gallery.index',
            'position' => 15,
        ]);
    }
}
