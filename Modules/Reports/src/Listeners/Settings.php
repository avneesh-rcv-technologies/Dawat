<?php

namespace Modules\Reports\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class Settings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        // $event->register(
        //     [
        //         'category_code' => "general",
        //         'title' => 'Reports',
        //         'description' => 'Create and manage custom reports',
        //         'route' => route('reports.index'),
        //         'icon' => 'chart-bar',
        //         'permission' => 'reports.index'
        //     ]
        // );
    }
}
