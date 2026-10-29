<?php

namespace Modules\Subscriptions\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Models\SubscriptionReminder;
use Modules\Notifications\Services\NotificationService;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Send notification about subscription creation
     */
    public function notifyCreated(Request $request, $subscriptionId)
    {
        try {
            $subscription = Subscription::findOrFail($subscriptionId);
            
            $this->notificationService->trigger(
                'Subscriptions',
                'Created',
                $subscription->subscribable,
                $subscription
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscription creation notification sent',
            ]);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Send notification about subscription renewal
     */
    public function notifyRenewed(Request $request, $subscriptionId)
    {
        try {
            $subscription = Subscription::findOrFail($subscriptionId);
            
            $this->notificationService->trigger(
                'Subscriptions',
                'Renewed',
                $subscription->subscribable,
                $subscription
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscription renewal notification sent',
            ]);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Send notification about subscription cancellation
     */
    public function notifyCanceled(Request $request, $subscriptionId)
    {
        try {
            $subscription = Subscription::findOrFail($subscriptionId);
            
            $this->notificationService->trigger(
                'Subscriptions',
                'Canceled',
                $subscription->subscribable,
                $subscription
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancellation notification sent',
            ]);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Send notification about subscription expiry
     */
    public function notifyExpired(Request $request, $subscriptionId)
    {
        try {
            $subscription = Subscription::findOrFail($subscriptionId);
            
            $this->notificationService->trigger(
                'Subscriptions',
                'Expired',
                $subscription->subscribable,
                $subscription
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscription expiry notification sent',
            ]);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Send reminder notification
     */
    public function sendReminder(Request $request, $reminderId)
    {
        try {
            $reminder = SubscriptionReminder::findOrFail($reminderId);
            $subscription = $reminder->subscription;

            $this->notificationService->trigger(
                'Subscriptions',
                'Reminder',
                $subscription->subscribable,
                $reminder
            );

            // Mark reminder as sent
            $reminder->markAsSent();

            return response()->json([
                'success' => true,
                'message' => 'Reminder notification sent',
            ]);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Send all pending reminders for a subscription
     */
    public function sendPendingReminders(Request $request, $subscriptionId)
    {
        try {
            $subscription = Subscription::findOrFail($subscriptionId);
            $reminders = $subscription->reminders()
                ->where('status', SubscriptionReminder::STATUS_PENDING)
                ->where('scheduled_at', '<=', now())
                ->get();

            if ($reminders->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No pending reminders to send',
                    'sent_count' => 0,
                ]);
            }

            foreach ($reminders as $reminder) {
                try {
                    $this->notificationService->trigger(
                        'Subscriptions',
                        'Reminder',
                        $subscription->subscribable,
                        $reminder
                    );
                    $reminder->markAsSent();
                } catch (\Exception $e) {
                    $reminder->markAsFailed($e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pending reminders sent',
                'sent_count' => $reminders->count(),
            ]);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Resend a failed reminder
     */
    public function resendFailedReminder(Request $request, $reminderId)
    {
        try {
            $reminder = SubscriptionReminder::findOrFail($reminderId);
            
            if ($reminder->status !== SubscriptionReminder::STATUS_FAILED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only failed reminders can be resent',
                ], 400);
            }

            $subscription = $reminder->subscription;

            $this->notificationService->trigger(
                'Subscriptions',
                'Reminder',
                $subscription->subscribable,
                $reminder
            );

            $reminder->markAsSent();

            return response()->json([
                'success' => true,
                'message' => 'Failed reminder resent successfully',
            ]);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
