<?php

namespace Modules\Subscriptions\Notifications;

use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;

class RenewedNotification implements Notification
{
    /**
     * The subscription instance.
     */
    protected $subscription;

    /**
     * Create a new notification instance.
     */
    public function __construct($subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the event code for this notification
     */
    public function getEventCode(): string
    {
        return 'subscription.renewed';
    }

    /**
     * Get the notification message
     */
    public function getMessage(): string
    {
        $planName = $this->subscription->plan?->name ?? $this->subscription->plan_name ?? 'your subscription';
        $nextPeriodEnd = $this->subscription->current_period_end?->format('F d, Y');

        return "Your subscription for {$planName} has been renewed until {$nextPeriodEnd}";
    }

    /**
     * Get the notification title
     */
    public function getTitle(): string
    {
        return 'Subscription Renewed';
    }

    /**
     * Get the notification data
     */
    public function getData(): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan?->name ?? $this->subscription->plan_name,
            'current_period_end' => $this->subscription->current_period_end?->toDateString(),
            'status' => $this->subscription->status,
            'type' => 'subscription',
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
            // Ignore lookup errors; dispatcher may fallback
        }

        return $channels;
    }

    public function getVariables(): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan?->name ?? $this->subscription->plan_name,
            'status' => $this->subscription->status,
            'current_period_end' => $this->subscription->current_period_end?->toIso8601String(),
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'subscription_id' => 'Subscription ID',
            'plan_name' => 'Subscription plan name',
            'status' => 'Subscription status',
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
        $subscriberClass = $this->subscription->subscribable ? get_class($this->subscription->subscribable) : \Modules\Acl\Models\Admin::class;

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
