<?php

namespace Modules\Rating\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class Settings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        // $event->register(
        //     [
        //         'category_code' => "general",
        //         'title' => 'Ratings & Reviews',
        //         'description' => 'Manage customer ratings and reviews',
        //         'route' => route('ratings.index'),
        //         'icon' => 'star',
        //         'permission' => 'ratings.index'
        //     ]
        // );
    }
}
