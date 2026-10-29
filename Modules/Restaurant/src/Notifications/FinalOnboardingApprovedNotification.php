<?php

namespace Modules\Restaurant\Notifications;

use Modules\Acl\Models\Admin;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;

class FinalOnboardingApprovedNotification implements Notification
{
    protected const EVENT_CODE = 'final_onboarding_approved';

    public function __construct(protected array $data = []) {}

    public function getEventCode(): string
    {
        return static::EVENT_CODE;
    }

    public function getTemplateIdentifier(): array
    {
        $event = NotificationEvent::where('event_code', $this->getEventCode())->first();
        if (!$event) return [];
        $channels = [];
        foreach (NotificationMapping::with('channel')->where('event_id', $event->id)->get() as $mapping) {
            if ($mapping->channel) {
                $channels[$mapping->channel->name] = $mapping->template_id;
                $channels = array_merge($channels, $mapping->recipients ?? []);
            }
        }

        return $channels;
    }

    public function getVariables(): array
    {
        return [
            'restaurant_name' => e($this->data['restaurant_name'] ?? 'Restaurant'),
            'restaurant_id' => (int) ($this->data['restaurant_id'] ?? 0),
            'approved_at' => $this->data['approved_at'] ?? '',
            'app_name' => e(config('app.name', 'Dawat')),
        ];
    }

    public function getEmailAttachments(): array
    {
        return $this->data['attachments'] ?? [];
    }

    public function getAvailableVariables(): array
    {
        return ['restaurant_name' => 'Restaurant name', 'restaurant_id' => 'Restaurant ID', 'approved_at' => 'Final onboarding approval time', 'app_name' => 'Application name'];
    }

    public function getAvailableRecipients(): array
    {
        return ['notify_restaurant' => 'Notify Restaurant Owner', 'notify_admin' => 'Notify Admin'];
    }

    public function getRecipientTypeMapping(): array
    {
        return ['notify_restaurant' => 'notifiable', 'notify_admin' => 'admin'];
    }

    public function getRecipientClassMapping(): array
    {
        return ['notify_restaurant' => Admin::class, 'notify_admin' => Admin::class];
    }

    public function toArray(): array
    {
        return ['event_code' => $this->getEventCode(), 'template' => $this->getTemplateIdentifier(), 'variables' => $this->getVariables(), 'available_variables' => $this->getAvailableVariables()];
    }
}
