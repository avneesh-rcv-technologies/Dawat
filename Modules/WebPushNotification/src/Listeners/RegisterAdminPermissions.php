<?php

namespace Modules\WebPushNotification\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Settings → Currencies
         */
        $event->registerArray([
            'name' => 'push notification',
            'code' => 'web_push_notification',
            'key' => [
                'push-notification-template.index' => 'View Web Push Notifications',
                'push-notification-template.create' => 'Create Web Push Notifications',
                'push-notification-template.store' => 'Store Web Push Notifications',
                'push-notification-template.edit' => 'Edit Web Push Notifications',
                'push-notification-template.update' => 'Update Web Push Notifications',
                'push-notification-template.destroy' => 'Delete Web Push Notifications',                
            ]
        ]);

        $event->registerArray([
            'name' => 'Firebase Configuration',
            'code' => 'web_push_notification',
            'key' => [
                'firebase.index' => 'View Web Push Notifications',
                'firebase.create' => 'Create Web Push Notifications',
                'firebase.store' => 'Store Web Push Notifications',
                'firebase.edit' => 'Edit Web Push Notifications',
                'firebase.update' => 'Update Web Push Notifications',
                'firebase.destroy' => 'Delete Web Push Notifications',                
                'firebase.send-test-notification' => 'Send Test Notification',
            ]
        ]);
    }
}
