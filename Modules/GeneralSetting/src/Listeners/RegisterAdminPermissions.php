<?php

namespace Modules\GeneralSetting\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {        
        /**
         * Settings → Index
         */
        $event->registerArray([
            'name' => 'Settings',
            'code' => 'settings',
            'key' => [
                'settings.index' => 'View Settings',
                'settings.configuration' => 'View Configuration Settings',
                'settings.store' => 'Update Settings',
                'settings.clear_cache' => 'Clear Cache',
            ]
        ]);
    }
}
