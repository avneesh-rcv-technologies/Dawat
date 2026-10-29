<?php

namespace Modules\Notifications\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Notifications → Notifications
         */
        $event->registerArray([
            'name' => 'notifications',
            'code' => 'notifications',
            'key' => [
                'notification.index' => 'View Notifications',
                'notification.create' => 'Create Notification',
                'notification.store' => 'Store Notification',
                'notification.show' => 'View Notification',
                'notification.edit' => 'Edit Notification',
                'notification.update' => 'Update Notification',
                'notification.destroy' => 'Delete Notification',
            ]
        ]);

        /**
         * Notifications → Events
         */
        $event->registerArray([
            'name' => 'notifications',
            'code' => 'events',
            'key' => [
                'notification.event.create' => 'Create Event',
                'notification.event.store' => 'Store Event',
            ]
        ]);

        /**
         * Notifications → Logs
         */
        $event->registerArray([
            'name' => 'notifications',
            'code' => 'logs',
            'key' => [
                'notification-logs.index' => 'View Logs',
                'notification-logs.create' => 'Create Log',
                'notification-logs.store' => 'Store Log',
                'notification-logs.show' => 'View Log',
                'notification-logs.edit' => 'Edit Log',
                'notification-logs.update' => 'Update Log',
                'notification-logs.destroy' => 'Delete Log',
            ]
        ]);

        /**
         * Notifications → My Notifications (Bell Icon)
         */
        $event->registerArray([
            'name' => 'notifications',
            'code' => 'my-notifications',
            'key' => [
                'system.notifications' => 'View My Notifications',
                'system.notification-show' => 'View My Notification Details',
                'system.markAsRead' => 'Mark Notification as Read',
                'notifications.bell.unread-count' => 'Get Unread Count',
                'notifications.bell.recent' => 'Get Recent Notifications',
                'notifications.bell.mark-as-read' => 'Mark as Read',
                'notifications.bell.toggle-read-status' => 'Toggle Read Status',
                'notifications.bell.mark-all-as-read' => 'Mark All as Read',
            ]
        ]);
    }
}
