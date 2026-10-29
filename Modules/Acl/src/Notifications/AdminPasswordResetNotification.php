<?php

namespace Modules\Acl\Notifications;

use Modules\Acl\Models\Admin;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Support\NotificationMappingLoader;

class AdminPasswordResetNotification implements Notification
{
    public const EVENT = 'admin_password_reset_link';

    public function __construct(private array $data = []) {}

    public function getEventCode(): string
    {
        return self::EVENT;
    }

    public function getTemplateIdentifier(): array
    {
        return NotificationMappingLoader::forEventCode(self::EVENT);
    }

    public function getVariables(): array
    {
        return array_merge(['app_name' => config('app.name', 'Dawat'),
            'expire_minutes' => config('auth.passwords.admins.expire', 60)], $this->data);
    }

    public function getAvailableVariables(): array
    {
        return ['app_name' => 'Application name', 'admin_name' => 'Account name',
            'reset_url' => 'Password reset link', 'expire_minutes' => 'Link lifetime in minutes'];
    }

    public function getAvailableRecipients(): array
    {
        return ['notify_account_owner' => 'Notify account owner'];
    }

    public function getRecipientTypeMapping(): array
    {
        return ['notify_account_owner' => 'notifiable'];
    }

    public function getRecipientClassMapping(): array
    {
        return ['notify_account_owner' => Admin::class];
    }

    public function toArray(): array
    {
        return ['event_code' => $this->getEventCode(), 'template' => $this->getTemplateIdentifier(),
            'variables' => $this->getVariables(), 'available_variables' => $this->getAvailableVariables()];
    }
}
