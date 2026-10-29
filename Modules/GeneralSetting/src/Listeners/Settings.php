<?php

namespace Modules\GeneralSetting\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class Settings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register(
            [
                'category_code' => "general",
                'title' => 'General Settings',
                'description' => 'Configure store name, contact information, and basic preferences.',
                'route' => route('settings.configuration'),
                'icon' => 'settings',
                'permission' => 'settings.configuration'
            ]
        );        
    }
}
