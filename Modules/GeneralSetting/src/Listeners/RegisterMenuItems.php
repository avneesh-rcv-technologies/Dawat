<?php

namespace Modules\GeneralSetting\Listeners;

use Illuminate\Support\Facades\Auth;
use Modules\Core\Events\RegisterMenu;

class RegisterMenuItems
{
    /**
     * Handle the event.
     *
     * @param  \Modules\Core\Events\RegisterMenu  $event
     * @return void
     */
    public function handle(RegisterMenu $event)
    {
        $admin = Auth::guard('admin')->user();
        if (strcasecmp(trim((string) ($admin?->role?->name ?? '')), 'Restaurant Owner') === 0) {
            return;
        }

        $event->addItem([
            'label' => 'Settings',
            'route' => 'settings.index',
            'icon' => 'settings', // Settings
            'permission' => 'settings.index',
            'position' => 99
        ]);
    }
}
