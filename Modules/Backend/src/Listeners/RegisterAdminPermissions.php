<?php

namespace Modules\Backend\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Dashboard
         */
        $event->registerArray([
            'name' => 'dashboard',
            'key' => [
                'dashboard.index' => 'Access Dashboard',
            ]
        ]);

        /**
         * Settings → Account
         */
        $event->registerArray([
            'name' => 'settings',
            'code' => 'account',
            'key' => [
                'profile.edit' => 'Edit Account Details',
                'profile.update' => 'Update Account Details',
            ]
        ]);
    }
}
