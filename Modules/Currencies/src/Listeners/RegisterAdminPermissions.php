<?php

namespace Modules\Currencies\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Settings → Currencies
         */
        $event->registerArray([
            'name' => 'settings',
            'code' => 'currencies',
            'key' => [
                'settings.currencies.index' => 'View Currencies',
                'settings.currencies.create' => 'Create Currencies',
                'settings.currencies.store' => 'Store Currencies',
                'settings.currencies.edit' => 'Edit Currencies',
                'settings.currencies.update' => 'Update Currencies',
                'settings.currencies.destroy' => 'Delete Currencies',
            ]
        ]);
    }
}
