<?php

namespace Modules\Activitylog\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterSettings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register([
            'category_code' => "access_control",
            'title' => 'Activity Logs',
            'description' => 'Track application',
            'route' => route('activity-log.index'),
            'icon' => 'flag',
            'permission' => 'activity-log.index'
        ]);
    }
}
