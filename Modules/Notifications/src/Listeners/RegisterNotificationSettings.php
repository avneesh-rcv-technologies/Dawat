<?php

namespace Modules\Notifications\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterNotificationSettings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register(
            [
                'category_code' => "notifications",
                'title' => 'Notifications',
                'description' => 'View system notification logs.',
                'route' => route('notification.index'),
                'icon' => 'edit_notifications',
                'permission' => 'notification.index'
            ]
        );

        $event->register(
            [
                'category_code' => "notifications",
                'title' => 'Notification Logs',
                'description' => 'View system activity logs and error reports.',
                'route' => route('notification-logs.index'),
                'icon' => 'list_alt',
                'permission' => 'notification-logs.index'
            ]
        );
    }
}
