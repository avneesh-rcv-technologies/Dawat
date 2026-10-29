<?php

namespace Modules\Subscriptions\Notifications;

use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Subscriptions\Models\Subscription;

class ResumedNotification implements Notification
{
    protected $subscription;

    public function __construct($subscription = null)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the event code/identifier
     */
    public function getEventCode(): string
    {
        return 'subscription.resumed';
    }

    /**
     * Get the notification title
     */
    public function getTitle(): string
    {
        return 'Subscription Resumed';
    }

    /**
     * Get the notification message
     */
    public function getMessage(): string
    {
        if (!$this->subscription instanceof \Modules\Subscriptions\Models\Subscription) {
            return 'Subscription resumed';
        }

        $planName = $this->subscription->plan_name ?? $this->subscription->plan?->name ?? 'Your subscription';
        return "{$planName} has been resumed and is now active.";
    }

    /**
     * Get the notification data
     */
    public function getData(): array
    {
        if (!$this->subscription instanceof \Modules\Subscriptions\Models\Subscription) {
            return [];
        }

        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan_name ?? $this->subscription->plan?->name ?? null,
            'status' => $this->subscription->status,
            'resumed_at' => now()->toIso8601String(),
            'current_period_end' => $this->subscription->current_period_end,
            'type' => 'subscription.resumed'
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
            // Ignore lookup errors
        }

        return $channels;
    }

    public function getVariables(): array
    {
        if (!$this->subscription instanceof \Modules\Subscriptions\Models\Subscription) {
            return [];
        }

        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan_name ?? $this->subscription->plan?->name ?? null,
            'status' => $this->subscription->status,
            'resumed_at' => now()->toIso8601String(),
            'current_period_end' => $this->subscription->current_period_end,
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'subscription_id' => 'Subscription ID',
            'plan_name' => 'Subscription plan name',
            'status' => 'Subscription status',
            'resumed_at' => 'Resume timestamp',
            'current_period_end' => 'Current period end date',
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
        $subscriberClass = \Modules\Acl\Models\Admin::class;
        
        if ($this->subscription instanceof \Modules\Subscriptions\Models\Subscription && $this->subscription->subscribable) {
            $subscriberClass = get_class($this->subscription->subscribable);
        }

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
