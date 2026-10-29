<?php

namespace Modules\Countries\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class Settings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register(
            [
                'category_code' => "general",
                'title' => 'Countries & States',
                'description' => 'Manage countries and States where you operate and ship to.',
                'route' => route('settings.countries.index'),
                'icon' => 'public',
                'permission' => 'settings.countries.index'
            ]
        );
        
    }
}
