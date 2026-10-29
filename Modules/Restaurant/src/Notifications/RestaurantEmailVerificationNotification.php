<?php

namespace Modules\Restaurant\Notifications;

use Exception;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;

class RestaurantEmailVerificationNotification implements Notification
{
    public function __construct(
        protected $entity = null,
        protected $data = []
    ) {
        if (is_array($entity)) {
            $this->data = $entity;
            $this->entity = null;
        }
    }

    public function getEventCode(): string
    {
        return 'restaurant_email_verification';
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
        $restaurantName = $this->data['restaurant_name'] ?? ($this->entity->name ?? 'Your Restaurant');
        $email = $this->data['email'] ?? ($this->entity->email ?? '');
        $otp = $this->data['otp'] ?? '';
        $expiresMinutes = (int) ($this->data['expires_minutes'] ?? $this->data['expiry_minutes'] ?? 10);

        return [
            'restaurant_name' => $restaurantName,
            'restaurant_email' => $email,
            'otp_code' => $otp,
            'expires_minutes' => $expiresMinutes,
            'expiry_minutes' => $expiresMinutes,
            'verification_message' => 'Use this code to verify your restaurant email: ' . $otp,
            'app_name' => config('app.name', 'Dawat'),
            'current_year' => now()->year,
            'support_email' => config('mail.from.address', 'support@example.com'),
            'security_warning' => 'Do not share this code with anyone.',
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'restaurant_name' => 'Restaurant legal name',
            'restaurant_email' => 'Restaurant owner email address',
            'otp_code' => 'Verification OTP code',
            'expires_minutes' => 'OTP expiry window in minutes',
            'verification_message' => 'Formatted OTP message',
            'app_name' => 'Application name',
            'current_year' => 'Current year',
            'support_email' => 'Support email',
            'security_warning' => 'Security reminder',
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
