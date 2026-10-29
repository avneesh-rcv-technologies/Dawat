<?php

namespace Modules\Subscriptions\Notifications;

use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;

class ReminderNotification implements Notification
{
    /**
     * The subscription reminder instance.
     */
    protected $reminder;

    /**
     * Create a new notification instance.
     */
    public function __construct($reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Get the event code for this notification
     */
    public function getEventCode(): string
    {
        return 'subscription.reminder';
    }

    /**
     * Get the notification message
     */
    public function getMessage(): string
    {
        $subscription = $this->reminder->subscription;
        $planName = $subscription->plan?->name ?? $subscription->plan_name ?? 'your subscription';

        return $this->reminder->message ?? "Subscription reminder: {$planName}";
    }

    /**
     * Get the notification title
     */
    public function getTitle(): string
    {
        return $this->reminder->subject ?? 'Subscription Reminder';
    }

    /**
     * Get the notification data
     */
    public function getData(): array
    {
        $subscription = $this->reminder->subscription;

        return [
            'reminder_id' => $this->reminder->id,
            'subscription_id' => $subscription->id,
            'type' => $this->reminder->type,
            'channel' => $this->reminder->channel,
            'reminder_number' => $this->reminder->reminder_number,
            'scheduled_at' => $this->reminder->scheduled_at?->toIso8601String(),
            'plan_name' => $subscription->plan?->name ?? $subscription->plan_name,
            'notification_type' => 'subscription_reminder',
        ];
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
        } catch (\Exception $e) {
            // Allow dispatcher to handle missing templates
        }

        return $channels;
    }

    public function getVariables(): array
    {
        $subscription = $this->reminder->subscription;

        return [
            'subscription_id' => $subscription->id,
            'plan_name' => $subscription->plan?->name ?? $subscription->plan_name,
            'status' => $subscription->status,
            'expires_at' => $subscription->expires_at?->toIso8601String(),
            'reminder_date' => $this->reminder->scheduled_at?->toIso8601String(),
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'subscription_id' => 'Subscription ID',
            'plan_name' => 'Subscription plan name',
            'status' => 'Subscription status',
            'expires_at' => 'Subscription end date',
            'reminder_date' => 'Reminder date',
        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_admin' => 'Notify Admin',
            'notify_user' => 'Notify Subscriber',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_admin' => 'admin',
            'notify_user' => 'notifiable',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        $subscription = $this->reminder->subscription;
        $subscriberClass = $subscription->subscribable ? get_class($subscription->subscribable) : \Modules\Acl\Models\Admin::class;

        return [
            'notify_admin' => \Modules\Acl\Models\Admin::class,
            'notify_user' => $subscriberClass,
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
