<?php

namespace Modules\Subscriptions\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Subscriptions\Services\PlanService;
use Modules\Subscriptions\DataView\SubscriptionsGrid;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Notifications\Services\NotificationService;
use Modules\Subscriptions\Services\SubscriptionService;

class SubscriptionController extends Controller
{
    protected $subscriptionService;
    protected $planService;
    protected $notificationService;

    public function __construct(
        SubscriptionService $subscriptionService,
        PlanService $planService,
        NotificationService $notificationService = null
    ) {
        $this->subscriptionService = $subscriptionService;
        $this->planService = $planService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(SubscriptionsGrid::class)->process();

        // Get statistics
        $stats = $this->subscriptionService->getSubscriptionStatistics();

        return view('subscriptions::subscriptions.index', compact('lists', 'stats'));
    }

    public function show($id)
    {
        $subscription = $this->subscriptionService->getSubscriptionById($id);
        return view('subscriptions::subscriptions.show', compact('subscription'));
    }

    public function create()
    {
        $plans = $this->planService->getActivePlans();
        return view('subscriptions::subscriptions.form', compact('plans'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $this->subscriptionService->validateSubscriptionData($request->all());
            $subscription = $this->subscriptionService->createSubscription($validatedData);

            // Trigger notification for subscription creation
            if ($this->notificationService && $subscription->subscribable) {
                try {
                    $this->notificationService->trigger(
                        'Subscriptions',
                        'Created',
                        $subscription->subscribable,
                        $subscription
                    );
                } catch (\Exception $e) {
                    // Log notification error but don't fail the subscription creation
                    Log::warning('Failed to send subscription created notification', [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subscription created successfully.',
                    'redirect_url' => route('subscriptions.index'),
                ]);
            }

            return redirect()->route('subscriptions.index')
                ->with('success', 'Subscription created successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function edit($id)
    {
        $subscription = $this->subscriptionService->getSubscriptionById($id);
        $plans = $this->planService->getActivePlans();
        return view('subscriptions::subscriptions.form', compact('subscription', 'plans'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $this->subscriptionService->validateSubscriptionUpdateData($request->all());
            $this->subscriptionService->updateSubscription($id, $validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subscription updated successfully.',
                    'redirect_url' => route('subscriptions.index'),
                ]);
            }

            return redirect()->route('subscriptions.index')
                ->with('success', 'Subscription updated successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function cancel(Request $request, $id)
    {
        try {
            $immediately = $request->input('immediately', false);
            $this->subscriptionService->cancelSubscription($id, $immediately);

            // Trigger notification for subscription cancellation
            if ($this->notificationService) {
                try {
                    $subscription = $this->subscriptionService->getSubscriptionById($id);
                    if ($subscription && $subscription->subscribable) {
                        $this->notificationService->trigger(
                            'Subscriptions',
                            'Canceled',
                            $subscription->subscribable,
                            $subscription
                        );
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to send subscription canceled notification', [
                        'subscription_id' => $id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $message = $immediately
                ? 'Subscription canceled immediately.'
                : 'Subscription will be canceled at period end.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return redirect()->route('subscriptions.index')
                ->with('success', $message);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function resume($id)
    {
        try {
            $this->subscriptionService->resumeSubscription($id);

            // Trigger notification for subscription resume
            if ($this->notificationService) {
                try {
                    $subscription = $this->subscriptionService->getSubscriptionById($id);
                    if ($subscription && $subscription->subscribable) {
                        $this->notificationService->trigger(
                            'Subscriptions',
                            'Resumed',
                            $subscription->subscribable,
                            $subscription
                        );
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to send subscription resumed notification', [
                        'subscription_id' => $id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subscription resumed successfully.',
                ]);
            }

            return redirect()->route('subscriptions.index')
                ->with('success', 'Subscription resumed successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->subscriptionService->deleteSubscription($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subscription deleted successfully.',
                ]);
            }

            return redirect()->route('subscriptions.index')
                ->with('success', 'Subscription deleted successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Mass destroy subscriptions.
     */
    public function massDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No subscriptions selected.',
                    ], 400);
                }

                return redirect()->back()
                    ->with('error', 'No subscriptions selected.');
            }

            $count = 0;
            foreach ($ids as $id) {
                try {
                    $this->subscriptionService->deleteSubscription($id);
                    $count++;
                } catch (\Exception $e) {
                    throw new \Exception('Failed to delete some subscriptions.');
                }
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} subscription(s) deleted successfully.",
                ]);
            }
            return redirect()->back()
                ->with('success', "{$count} subscription(s) deleted successfully.");
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Mass cancel subscriptions.
     */
    public function massCancel(Request $request)
    {
        try {
            $ids = $request->input('indices', []);
            $immediately = $request->input('immediately', false);

            if (empty($ids)) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No subscriptions selected.',
                    ], 400);
                }

                return redirect()->back()
                    ->with('error', 'No subscriptions selected.');
            }

            $count = 0;
            foreach ($ids as $id) {
                try {
                    $this->subscriptionService->cancelSubscription($id, $immediately);
                    $count++;
                } catch (\Exception $e) {
                    throw new \Exception('Failed to cancel some subscriptions.');
                }
            }

            $message = $immediately
                ? "{$count} subscription(s) canceled immediately."
                : "{$count} subscription(s) will be canceled at period end.";

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }
            return redirect()->back()
                ->with('success', $message);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Mass resume subscriptions.
     */
    public function massResume(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No subscriptions selected.',
                    ], 400);
                }

                return redirect()->back()
                    ->with('error', 'No subscriptions selected.');
            }

            $count = 0;
            foreach ($ids as $id) {
                try {
                    $this->subscriptionService->resumeSubscription($id);
                    $count++;
                } catch (\Exception $e) {
                    throw new \Exception('Failed to resume some subscriptions.');
                }
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} subscription(s) resumed successfully.",
                ]);
            }
            return redirect()->back()
                ->with('success', "{$count} subscription(s) resumed successfully.");
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Pause a subscription.
     */
    public function pause(Request $request, $id)
    {
        try {
            $reason = $request->input('reason', '');
            $this->subscriptionService->pauseSubscription($id, $reason);

            // Trigger notification for subscription pause
            if ($this->notificationService) {
                try {
                    $subscription = $this->subscriptionService->getSubscriptionById($id);
                    if ($subscription && $subscription->subscribable) {
                        $this->notificationService->trigger(
                            'Subscriptions',
                            'Paused',
                            $subscription->subscribable,
                            $subscription
                        );
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to send subscription paused notification', [
                        'subscription_id' => $id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subscription paused successfully.',
                ]);
            }

            return redirect()->route('subscriptions.show', $id)
                ->with('success', 'Subscription paused successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function sendNotificationView(Request $request, $id) {
        $subscription = $this->subscriptionService->getSubscriptionById($id);

        return view('subscriptions::components.notification-modal', ['subscription' => $subscription]);
    }

    /**
     * Send manual notification to subscriber.
     */
    public function sendNotification(Request $request, $id)
    {
        try {
            $title = $request->input('title');
            $message = $request->input('message');

            if (empty($title) || empty($message)) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Title and message are required.',
                    ], 400);
                }
                return redirect()->back()
                    ->with('error', 'Title and message are required.');
            }

            $subscription = $this->subscriptionService->getSubscriptionById($id);

            if (!$subscription || !$subscription->subscribable) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Subscriber not found.',
                    ], 404);
                }
                return redirect()->back()
                    ->with('error', 'Subscriber not found.');
            }

            // Send notification to subscriber
            $subscriber = $subscription->subscribable;

            // Using Laravel's notification system if available
            if (method_exists($subscriber, 'notify')) {
                // You can create a custom notification class for this
                // For now, we'll just return success
                // $subscriber->notify(new \App\Notifications\SubscriptionNotification($title, $message));
            }

            // Log the notification in subscription metadata
            $metadata = $subscription->metadata ?? [];
            $metadata['notifications'] = $metadata['notifications'] ?? [];
            $metadata['notifications'][] = [
                'title' => $title,
                'message' => $message,
                'sent_at' => now()->toDateTimeString(),
                'sent_by' => Auth::id(),
            ];

            $subscription->update(['metadata' => $metadata]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification sent successfully.',
                ]);
            }

            return redirect()->back()
                ->with('success', 'Notification sent successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
