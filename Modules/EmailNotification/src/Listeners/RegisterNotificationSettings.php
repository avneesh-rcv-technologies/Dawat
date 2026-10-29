<?php

namespace Modules\EmailNotification\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterNotificationSettings
{
    public function handle(RegisterSettingsMenu $event): void
    {

        $event->register(
            [
                'category_code' => "notifications",
                'title' => 'Email Templates',
                'description' => 'Manage the Email templates.',
                'route' => route('notification.email.templates.index'),
                'permission' => 'notification.email.templates.index',
                'icon' => 'mail'
            ]
        );
    }
}
