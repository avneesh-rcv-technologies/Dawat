<?php

namespace Modules\WebPushNotification\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterWebPushListener
{
    /**
     * Handle the event.
     */
    public function handle(RegisterSettingsMenu $event): void
    {
         $event->register([
            'category_code' => "notifications",
            'title' => 'Firebase Configuration',
            'description' => 'Set up push notifications.',
            'route' => route('firebase.index'),
            'icon' => 'notifications',
            'permission' => 'firebase.index'
        ]);

        $event->register([
             'category_code' => "notifications",
            'title' => 'Push Notification Templates',
            'description' => 'Set up push notifications Templates.',
            'route' => route('push-notification-template.index'),
            'icon' => 'notifications',
            'permission' => 'push-notification-template.index'
        ]);
    }
}
