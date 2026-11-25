<?php

namespace Modules\Rating\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class Settings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register([
            'category_code' => 'general',
            'title' => 'Ratings & Reviews',
            'description' => 'Review food item feedback, respond to customers, and read admin-approved restaurant reviews.',
            'route' => route('restaurant.ratings.index'),
            'icon' => 'star',
            'guard' => 'admin',
            // Restaurant owners already receive this view permission through their seeded role.
            'permission' => 'settings.restaurant.general-information.index',
            'owner_only' => true,
        ]);
    }
}
