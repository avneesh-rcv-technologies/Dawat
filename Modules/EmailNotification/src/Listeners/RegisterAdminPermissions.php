<?php

namespace Modules\EmailNotification\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Email Notifications → Templates
         */
        $event->registerArray([
            'name' => 'email_notifications',
            'code' => 'templates',
            'key' => [
                'notification.email.templates.index' => 'View Email Templates',
                'notification.email.templates.create' => 'Create Email Template',
                'notification.email.templates.store' => 'Store Email Template',
                'notification.email.templates.show' => 'View Email Template',
                'notification.email.templates.edit' => 'Edit Email Template',
                'notification.email.templates.update' => 'Update Email Template',
                'notification.email.templates.destroy' => 'Delete Email Template',
                'notification.email-design.edit' => 'Edit Email Design',
                'notification.email-design.update' => 'Update Email Design',
            ]
        ]);
    }
}
