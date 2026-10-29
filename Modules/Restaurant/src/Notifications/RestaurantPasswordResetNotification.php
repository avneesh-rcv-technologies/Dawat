<?php

namespace Modules\Restaurant\Notifications;

use Exception;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;

class RestaurantPasswordResetNotification implements Notification
{
    public function __construct(protected $data = []) {}

    public function getEventCode(): string
    {
        return 'restaurant_password_reset';
    }

    public function getTemplateIdentifier(): array
    {
        $channels = [];

        try {
            $event = NotificationEvent::where('event_code', $this->getEventCode())->first();

            if (!$event) {
                return $channels;
            }

            $mappings = NotificationMapping::where('event_id', $event->id)->get();

            foreach ($mappings as $mapping) {
                $channel = NotificationChannel::find($mapping->channel_id);
                if ($channel) {
                    $channels[$channel->name] = $mapping->template_id;

                    if (!empty($mapping->recipients)) {
                        $channels = array_merge($channels, $mapping->recipients);
                    }
                }
            }
        } catch (Exception $e) {
            // Ignore template resolution issues gracefully.
        }

        return $channels;
    }

    public function getVariables(): array
    {
        if (empty($this->data)) {
            return [];
        }

        return [
            'restaurant_name' => $this->data['restaurant_name'] ?? 'Restaurant',
            'restaurant_email' => $this->data['restaurant_email'] ?? $this->data['email'] ?? '',
            'reset_time' => $this->data['reset_time'] ?? now()->format('Y-m-d H:i:s'),
            'login_url' => route('admin.login.form'),
            'app_name' => fn_get_setting('general.company.name') ?: config('app.name', 'Dawat'),
            'current_year' => now()->year,
            'support_url' => config('app.url', 'https://app.local') . '/support',
            'privacy_url' => config('app.url', 'https://app.local') . '/privacy-policy',
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'restaurant_name' => 'Restaurant name',
            'restaurant_email' => 'Restaurant owner email address',
            'reset_time' => 'Password reset timestamp',
            'login_url' => 'Restaurant login URL',
            'app_name' => 'Application name',
            'current_year' => 'Current year',
            'support_url' => 'Support page URL',
            'privacy_url' => 'Privacy policy URL',
        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_restaurant' => 'Notify Restaurant Owner',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_restaurant' => 'notifiable',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_restaurant' => \Modules\Acl\Models\Admin::class,
        ];
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
