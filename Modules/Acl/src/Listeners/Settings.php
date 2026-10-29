<?php

namespace Modules\Acl\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class Settings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register(
            [
                'category_code' => "access_control",
                'title' => 'Roles & Permissions',
                'description' => 'Assign roles and permissions to users.',
                'route' => route('settings.roles.index'),
                'icon' => 'admin_panel_settings',
                'permission' => 'settings.roles.index'
            ]
        );
        
    }
}
