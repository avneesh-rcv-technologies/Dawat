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
        return array_replace($this->data, [
            'app_name' => e(config('app.name', 'Dawat')),
            'admin_name' => e($this->data['admin_name'] ?? 'Account owner'),
            'reset_url' => e($this->data['reset_url'] ?? ''),
            'expire_minutes' => (int) config('auth.passwords.admins.expire', 60),
            'support_url' => route('website.contact'),
            'current_year' => now()->year,
        ]);
    }

    public function getAvailableVariables(): array
    {
        return ['app_name' => 'Application name', 'admin_name' => 'Account name',
            'reset_url' => 'Password reset link', 'expire_minutes' => 'Link lifetime in minutes',
            'support_url' => 'Contact support URL', 'current_year' => 'Current year'];
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
