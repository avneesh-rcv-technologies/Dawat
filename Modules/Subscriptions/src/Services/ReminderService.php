<?php

namespace Modules\Subscriptions\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Models\SubscriptionReminder;
use Modules\Notifications\Services\NotificationService;

class ReminderService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService = null)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Create reminders based on subscription configuration
     */
    public function createReminders(Subscription $subscription)
    {
        $config = $this->getSubscriptionConfig();
        
        // Create expiry reminders
        if ($config['send_reminder_emails'] || $config['send_reminder_notifications']) {
            $this->createExpiryReminders($subscription, $config);
        }

        // Create payment reminders
        $this->createPaymentReminders($subscription, $config);
    }

    /**
     * Create expiry reminders
     */
    private function createExpiryReminders(Subscription $subscription, array $config)
    {
        if (!$subscription->current_period_end) {
            return;
        }

        $reminderCount = $config['reminder_count'] ?? 3;
        $startDays = $config['reminder_start_days'] ?? 30;
        $interval = $config['reminder_interval'] ?? 7;

        // Calculate when to start reminders
        $reminderStartDate = $subscription->current_period_end
            ->copy()
            ->subDays($startDays);

        // Create multiple reminders
        for ($i = 1; $i <= $reminderCount; $i++) {
            $scheduledAt = $reminderStartDate->copy()->addDays(($i - 1) * $interval);

            // Don't create reminders in the past
            if ($scheduledAt->isPast()) {
                continue;
            }

            // Check if reminder already exists
            $exists = SubscriptionReminder::where('subscription_id', $subscription->id)
                ->where('type', SubscriptionReminder::TYPE_EXPIRY)
                ->where('reminder_number', $i)
                ->exists();

            if ($exists) {
                continue;
            }

            // Create email reminder
            if ($config['send_reminder_emails']) {
                SubscriptionReminder::create([
                    'subscription_id' => $subscription->id,
                    'type' => SubscriptionReminder::TYPE_EXPIRY,
                    'channel' => SubscriptionReminder::CHANNEL_EMAIL,
                    'reminder_number' => $i,
                    'scheduled_at' => $scheduledAt,
                    'status' => SubscriptionReminder::STATUS_PENDING,
                    'subject' => "Subscription Expiring - Reminder #{$i}",
                    'message' => "Your subscription will expire on {$subscription->current_period_end->format('F d, Y')}",
                ]);
            }

            // Create in-app reminder
            if ($config['send_reminder_notifications']) {
                SubscriptionReminder::create([
                    'subscription_id' => $subscription->id,
                    'type' => SubscriptionReminder::TYPE_EXPIRY,
                    'channel' => SubscriptionReminder::CHANNEL_IN_APP,
                    'reminder_number' => $i,
                    'scheduled_at' => $scheduledAt,
                    'status' => SubscriptionReminder::STATUS_PENDING,
                    'message' => "Your subscription will expire on {$subscription->current_period_end->format('F d, Y')}",
                ]);
            }
        }
    }

    /**
     * Create payment reminders
     */
    private function createPaymentReminders(Subscription $subscription, array $config)
    {
        if (!$subscription->current_period_end) {
            return;
        }

        $reminderDays = $config['payment_reminder_days'] ?? 3;
        $scheduledAt = $subscription->current_period_end
            ->copy()
            ->subDays($reminderDays);

        // Don't create reminders in the past
        if ($scheduledAt->isPast()) {
            return;
        }

        // Check if reminder already exists
        $exists = SubscriptionReminder::where('subscription_id', $subscription->id)
            ->where('type', SubscriptionReminder::TYPE_PAYMENT)
            ->whereNull('sent_at')
            ->exists();

        if ($exists) {
            return;
        }

        // Create payment reminder
        SubscriptionReminder::create([
            'subscription_id' => $subscription->id,
            'type' => SubscriptionReminder::TYPE_PAYMENT,
            'channel' => SubscriptionReminder::CHANNEL_EMAIL,
            'reminder_number' => 1,
            'scheduled_at' => $scheduledAt,
            'status' => SubscriptionReminder::STATUS_PENDING,
            'subject' => 'Upcoming Payment Due',
            'message' => "Your next payment is due on {$subscription->current_period_end->format('F d, Y')}",
        ]);
    }

    /**
     * Send pending reminders
     */
    public function sendPendingReminders()
    {
        $reminders = SubscriptionReminder::pending()
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($reminders as $reminder) {
            $this->sendReminder($reminder);
        }
    }

    /**
     * Send a single reminder
     */
    public function sendReminder(SubscriptionReminder $reminder)
    {
        try {
            $subscription = $reminder->subscription;
            
            // Trigger reminder notification
            if ($this->notificationService && $subscription && $subscription->subscribable) {
                try {
                    $this->notificationService->trigger(
                        'Subscriptions',
                        'Reminder',
                        $subscription->subscribable,
                        $reminder
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send reminder notification', [
                        'reminder_id' => $reminder->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Mark as sent
            $reminder->markAsSent();
            
            return true;
        } catch (\Exception $e) {
            $reminder->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Get subscription configuration
     */
    public function getSubscriptionConfig(): array
    {
        return [
            'reminder_count' => fn_get_setting('subscriptions.reminder_count', 3),
            'reminder_interval' => fn_get_setting('subscriptions.reminder_interval', 7),
            'reminder_start_days' => fn_get_setting('subscriptions.reminder_start_days', 30),
            'send_reminder_emails' => fn_get_setting('subscriptions.send_reminder_emails', true),
            'send_reminder_notifications' => fn_get_setting('subscriptions.send_reminder_notifications', true),
            'payment_reminder_days' => fn_get_setting('subscriptions.payment_reminder_days', 3),
            'payment_overdue_days' => fn_get_setting('subscriptions.payment_overdue_days', 5),
            'max_payment_retries' => fn_get_setting('subscriptions.max_payment_retries', 3),
            'retry_interval_hours' => fn_get_setting('subscriptions.retry_interval_hours', 24),
            'expiry_warning_days' => fn_get_setting('subscriptions.expiry_warning_days', 14),
            'final_notice_days' => fn_get_setting('subscriptions.final_notice_days', 1),
            'auto_renew_expiry' => fn_get_setting('subscriptions.auto_renew_expiry', false),
            'pause_on_expiry' => fn_get_setting('subscriptions.pause_on_expiry', true),
            'allow_renewal_after_expiry' => fn_get_setting('subscriptions.allow_renewal_after_expiry', true),
        ];
    }
}
