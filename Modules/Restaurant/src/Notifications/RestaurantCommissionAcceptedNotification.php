<?php

namespace Modules\Restaurant\Notifications;

use Modules\Acl\Models\Admin;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Support\NotificationMappingLoader;
use Modules\Restaurant\Support\PlatformCommission;

class RestaurantCommissionAcceptedNotification implements Notification
{
    public const EVENT = 'restaurant_commission_accepted';

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
        $percentage = $this->data['commission_percentage'] ?? null;
        return [
            'owner_name' => e($this->data['owner_name'] ?? 'Restaurant owner'),
            'restaurant_name' => e($this->data['restaurant_name'] ?? 'Restaurant'),
            'commission_percentage' => is_numeric($percentage) ? PlatformCommission::format((float) $percentage) : 'Not recorded',
            'accepted_at' => e($this->data['accepted_at'] ?? 'Not recorded'),
            'agreement_text' => is_numeric($percentage) ? e(PlatformCommission::label((float) $percentage)) : 'Not recorded',
            'login_url' => route('website.partner-login'),
            'support_url' => route('website.contact'), 'current_year' => now()->year,
            'app_name' => e(config('app.name', 'Dawat')),
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'owner_name' => 'Restaurant owner name', 'restaurant_name' => 'Restaurant name',
            'commission_percentage' => 'Recorded accepted commission percentage',
            'accepted_at' => 'Recorded acceptance date and time', 'agreement_text' => 'Accepted commission agreement',
            'login_url' => 'Partner login URL', 'support_url' => 'Contact support URL',
            'current_year' => 'Current year', 'app_name' => 'Application name',
        ];
    }

    public function getAvailableRecipients(): array
    {
        return ['notify_restaurant' => 'Notify Restaurant Owner'];
    }

    public function getRecipientTypeMapping(): array
    {
        return ['notify_restaurant' => 'notifiable'];
    }

    public function getRecipientClassMapping(): array
    {
        return ['notify_restaurant' => Admin::class];
    }

    public function toArray(): array
    {
        return ['event_code' => $this->getEventCode(), 'template' => $this->getTemplateIdentifier(),
            'variables' => $this->getVariables(), 'available_variables' => $this->getAvailableVariables()];
    }
}
