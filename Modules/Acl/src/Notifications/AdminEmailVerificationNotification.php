<?php

namespace Modules\Acl\Notifications;

use Modules\Acl\Models\Admin;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Support\NotificationMappingLoader;

class AdminEmailVerificationNotification implements Notification
{
    public const EVENT = 'admin_email_verification';

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
        return array_merge([
            'app_name' => config('app.name', 'Dawat'),
            'expires_minutes' => 60,
        ], $this->data);
    }

    public function getAvailableVariables(): array
    {
        return [
            'admin_name' => 'Account name',
            'verification_url' => 'Expiring email verification link',
            'expires_minutes' => 'Link lifetime in minutes',
            'app_name' => 'Application name',
        ];
    }

    public function getAvailableRecipients(): array
    {
        return ['notify_admin' => 'Notify account owner'];
    }

    public function getRecipientTypeMapping(): array
    {
        return ['notify_admin' => 'notifiable'];
    }

    public function getRecipientClassMapping(): array
    {
        return ['notify_admin' => Admin::class];
    }

    public function toArray(): array
    {
        return [
            'event_code' => $this->getEventCode(),
            'template' => $this->getTemplateIdentifier(),
            'variables' => $this->getVariables(),
            'available_variables' => $this->getAvailableVariables(),
        ];
    }
}
