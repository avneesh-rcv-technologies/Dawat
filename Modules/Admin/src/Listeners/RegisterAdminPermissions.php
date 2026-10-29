<?php

namespace Modules\Admin\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Settings → Users
         */
        $event->registerArray([
            'name' => 'settings',
            'code' => 'users',
            'key' => [
                'users.index' => 'View Users',
                'users.create' => 'Create Users',
                'users.store' => 'Store Users',
                'users.edit' => 'Edit Users',
                'users.update' => 'Update Users',
                'users.toggle-status' => 'Change Users Status',
            ]
        ]);
    }
}
