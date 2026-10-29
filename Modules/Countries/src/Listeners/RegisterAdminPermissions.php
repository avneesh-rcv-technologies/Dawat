<?php

namespace Modules\Countries\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Settings → Countries
         */
        $event->registerArray([
            'name' => 'settings',
            'code' => 'countries',
            'guard' => ['admin'],
            'key' => [
                'settings.countries.index' => 'View Countries',
                'settings.countries.create' => 'Create Countries',
                'settings.countries.store' => 'Store Countries',
                'settings.countries.edit' => 'Edit Countries',
                'settings.countries.update' => 'Update Countries',
                'settings.countries.destroy' => 'Delete Countries',
            ]
        ]);

        /**
         * Settings → States
         */
        $event->registerArray([
            'name' => 'settings',
            'code' => 'states',
            'guard' => ['admin'],
            'key' => [
                'settings.states.index' => 'View States',
                'settings.states.create' => 'Create States',
                'settings.states.store' => 'Store States',
                'settings.states.edit' => 'Edit States',
                'settings.states.update' => 'Update States',
                'settings.states.destroy' => 'Delete States',
            ]
        ]);

        /**
         * Settings → Cities
         */
        $event->registerArray([
            'name' => 'settings',
            'code' => 'cities',
            'guard' => ['admin'],
            'key' => [
                'settings.cities.index' => 'View Cities',
                'settings.cities.create' => 'Create Cities',
                'settings.cities.store' => 'Store Cities',
                'settings.cities.edit' => 'Edit Cities',
                'settings.cities.update' => 'Update Cities',
                'settings.cities.destroy' => 'Delete Cities',
            ]
        ]);
    }
}
