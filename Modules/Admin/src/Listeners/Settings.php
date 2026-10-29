<?php

namespace Modules\Admin\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class Settings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register(
            [
                'category_code' => "access_control",
                'title' => 'Team management',
                'description' => 'Manage team members',
                'route' => route('users.index'),
                'icon' => 'admin_panel_settings',
                'permission' => 'users.index'
            ]
        );
        
    }
}
