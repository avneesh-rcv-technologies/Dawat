<?php

namespace Modules\Rating\Listeners;

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
        //     'label' => 'Ratings & Reviews',
        //     'icon' => 'star',
        //     'permission' => 'ratings.index',
        //     'position' => 98,
        //     'route' => 'ratings.index'
        // ]);
    }
}
