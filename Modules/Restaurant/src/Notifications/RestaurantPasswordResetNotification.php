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
            'restaurant_name' => e($this->data['restaurant_name'] ?? 'Restaurant'),
            'owner_name' => e($this->data['owner_name'] ?? 'Restaurant owner'),
            'restaurant_email' => e($this->data['restaurant_email'] ?? $this->data['email'] ?? ''),
            'reset_time' => e($this->data['reset_time'] ?? 'Not recorded'),
            'login_url' => route('website.partner-login'),
            'forgot_password_url' => route('website.partner.password.request'),
            'app_name' => fn_get_setting('general.company.name') ?: config('app.name', 'Dawat'),
            'current_year' => now()->year,
            'support_url' => route('website.contact'),
            'privacy_url' => route('website.privacy-policy'),
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'restaurant_name' => 'Restaurant name',
            'owner_name' => 'Restaurant owner name',
            'restaurant_email' => 'Restaurant owner email address',
            'reset_time' => 'Password reset timestamp',
            'login_url' => 'Restaurant login URL',
            'forgot_password_url' => 'Restaurant password recovery URL',
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
