<?php

namespace Modules\Subscriptions\Services;

use Modules\Subscriptions\Repositories\SubscriptionRepository;
use Modules\Subscriptions\Repositories\PlanRepository;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Models\SubscriptionProduct;
use Modules\Subscriptions\Services\ReminderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    protected $subscriptionRepository;
    protected $planRepository;
    protected $reminderService;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        PlanRepository $planRepository,
        ReminderService $reminderService
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->planRepository = $planRepository;
        $this->reminderService = $reminderService;
    }

    /**
     * Get subscription by ID.
     */
    public function getSubscriptionById($id): Subscription
    {
        return $this->subscriptionRepository->findWithRelations((int) $id);
    }

    /**
     * Get active subscriptions.
     */
    public function getActiveSubscriptions(): Collection
    {
        return $this->subscriptionRepository->getActive();
    }

    /**
     * Get subscriptions ending soon.
     */
    public function getSubscriptionsEndingSoon(int $days = 7): Collection
    {
        return $this->subscriptionRepository->getEndingSoon($days);
    }

    /**
     * Create a new subscription.
     * Automatically creates a subscription_product entry if only plan_id is provided (direct plan purchase).
     */
    public function createSubscription(array $data): Subscription
    {
        // Case 1: subscription_product_id provided - use existing mapping
        if (isset($data['subscription_product_id'])) {
            $subscriptionProduct = SubscriptionProduct::findOrFail($data['subscription_product_id']);
            $data['plan_id'] = $subscriptionProduct->plan_id;
        } 
        // Case 2: Only plan_id provided - auto-create subscription_product for direct purchase
        elseif (isset($data['plan_id'])) {
            $plan = $this->planRepository->findOrFail($data['plan_id']);
            
            // Create or get existing direct purchase subscription_product for this plan
            $subscriptionProduct = SubscriptionProduct::firstOrCreate(
                [
                    'plan_id' => $plan->id,
                    'type' => SubscriptionProduct::TYPE_DIRECT,
                    'product_type' => null,
                    'product_id' => null,
                ],
                [
                    'price' => $plan->price,
                    'is_active' => true,
                ]
            );
            
            $data['subscription_product_id'] = $subscriptionProduct->id;
        } else {
            throw new \Exception('plan_id must be provided');
        }

        // Set defaults
        $data['status'] = $data['status'] ?? 'active';
        $data['start_date'] = $data['start_date'] ?? now();

        $subscription = $this->subscriptionRepository->create($data);

        // Auto-create reminders for the new subscription
        try {
            $this->reminderService->createReminders($subscription);
        } catch (\Exception $e) {
            Log::warning('Failed to create reminders for subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
        }

        return $subscription;
    }

    /**
     * Create a subscription from a product purchase.
     */
    public function createSubscriptionFromProduct(
        int $subscriptionProductId,
        string $subscribableType,
        int $subscribableId,
        array $additionalData = []
    ): Subscription {
        $subscriptionProduct = SubscriptionProduct::with('plan')->findOrFail($subscriptionProductId);

        if (!$subscriptionProduct->is_active) {
            throw new \Exception('This product-plan mapping is not active');
        }

        $data = array_merge([
            'subscribable_type' => $subscribableType,
            'subscribable_id' => $subscribableId,
            'subscription_product_id' => $subscriptionProductId,
            'plan_id' => $subscriptionProduct->plan_id,
            'status' => 'active',
            'start_date' => now(),
            'current_period_start' => now(),
            'current_period_end' => now()->add($this->getPeriodInterval($subscriptionProduct->plan)),
        ], $additionalData);

        return $this->createSubscription($data);
    }

    /**
     * Get period interval based on plan billing cycle.
     */
    protected function getPeriodInterval($plan): \DateInterval
    {
        return match($plan->billing_cycle) {
            'daily' => new \DateInterval('P1D'),
            'weekly' => new \DateInterval('P1W'),
            'monthly' => new \DateInterval('P1M'),
            'quarterly' => new \DateInterval('P3M'),
            'yearly' => new \DateInterval('P1Y'),
            default => new \DateInterval('P1M'),
        };
    }

    /**
     * Update a subscription.
     */
    public function updateSubscription(int $id, array $data): bool
    {
        // Validate that plan exists if plan_id is provided
        if (isset($data['plan_id'])) {
            $this->planRepository->findOrFail($data['plan_id']);
        }

        return $this->subscriptionRepository->update($id, $data);
    }

    /**
     * Cancel subscription.
     */
    public function cancelSubscription(int $id, bool $immediately = false): bool
    {
        if ($immediately) {
            return $this->subscriptionRepository->cancelNow($id);
        }

        return $this->subscriptionRepository->cancelAtPeriodEnd($id);
    }

    /**
     * Resume a canceled subscription.
     */
    public function resumeSubscription(int $id): bool
    {
        $subscription = $this->subscriptionRepository->findOrFail($id);

        if (!$subscription->isCanceled() || $subscription->hasEnded()) {
            throw new \Exception('Cannot resume this subscription.');
        }

        return $this->subscriptionRepository->resume($id);
    }

    /**
     * Pause a subscription.
     */
    public function pauseSubscription(int $id, string $reason = ''): bool
    {
        $subscription = $this->subscriptionRepository->findOrFail($id);

        if ($subscription->status === 'paused') {
            throw new \Exception('Subscription is already paused.');
        }

        $metadata = $subscription->metadata ?? [];
        $metadata['pause_reason'] = $reason;
        $metadata['paused_at'] = now()->toDateTimeString();
        $metadata['paused_by'] = Auth::id();

        return $this->subscriptionRepository->update($id, [
            'status' => 'paused',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Swap subscription plan.
     */
    public function swapSubscriptionPlan(int $id, int $planId, ?int $subscriptionProductId = null): bool
    {
        // Validate that plan exists
        $this->planRepository->findOrFail($planId);

        $updateData = ['plan_id' => $planId];
        
        // If subscription_product_id provided, update it too
        if ($subscriptionProductId) {
            $subscriptionProduct = SubscriptionProduct::findOrFail($subscriptionProductId);
            if ($subscriptionProduct->plan_id !== $planId) {
                throw new \Exception('Subscription product does not match the plan');
            }
            $updateData['subscription_product_id'] = $subscriptionProductId;
        }

        return $this->subscriptionRepository->update($id, $updateData);
    }

    /**
     * Swap subscription to a different product-plan combination.
     */
    public function swapSubscriptionProduct(int $subscriptionId, int $subscriptionProductId): bool
    {
        $subscriptionProduct = SubscriptionProduct::with('plan')->findOrFail($subscriptionProductId);

        if (!$subscriptionProduct->is_active) {
            throw new \Exception('This product-plan mapping is not active');
        }

        return $this->subscriptionRepository->update($subscriptionId, [
            'subscription_product_id' => $subscriptionProductId,
            'plan_id' => $subscriptionProduct->plan_id,
        ]);
    }

    /**
     * Renew subscription.
     */
    public function renewSubscription(int $id): bool
    {
        $result = $this->subscriptionRepository->renew($id);

        // Trigger renewed notification and recreate reminders
        if ($result) {
            try {
                $subscription = $this->subscriptionRepository->findOrFail($id);

                // Send renewed notification
                if ($subscription->subscribable) {
                    app(\Modules\Notifications\Services\NotificationService::class)->trigger(
                        'Subscriptions',
                        'Renewed',
                        $subscription->subscribable,
                        $subscription
                    );
                }

                // Recreate reminders for the renewed subscription
                $this->reminderService->createReminders($subscription);
            } catch (\Exception $e) {
                Log::warning('Failed to process renewed subscription', [
                    'subscription_id' => $id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $result;
    }

    /**
     * Delete a subscription.
     */
    public function deleteSubscription(int $id): bool
    {
        return $this->subscriptionRepository->delete($id);
    }

    /**
     * Validate subscription data for creation.
     */
    public function validateSubscriptionData(array $data): array
    {
        return validator($data, [
            'subscribable_type' => 'required|string',
            'subscribable_id' => 'required|integer',
            'subscription_product_id' => 'nullable|exists:subscription_products,id',
            'plan_id' => 'required|exists:plans,id', // Plan is required - subscription_product auto-created if needed
            'status' => 'nullable|in:trialing,active,past_due,paused,canceled,expired',
            'start_date' => 'nullable|date',
            'trial_ends_at' => 'nullable|date',
            'current_period_start' => 'nullable|date',
            'current_period_end' => 'nullable|date',
        ])->validate();
    }

    /**
     * Validate subscription data for update.
     */
    public function validateSubscriptionUpdateData(array $data): array
    {
        return validator($data, [
            'subscription_product_id' => 'nullable|exists:subscription_products,id',
            'plan_id' => 'nullable|exists:plans,id',
            'status' => 'required|in:trialing,active,past_due,paused,canceled,expired',
            'start_date' => 'nullable|date',
            'trial_ends_at' => 'nullable|date',
            'current_period_start' => 'nullable|date',
            'current_period_end' => 'nullable|date',
            'notes' => 'nullable|string',
        ])->validate();
    }

    /**
     * Get subscription statistics.
     */
    public function getSubscriptionStatistics(): array
    {
        return [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'trialing' => Subscription::where('status', 'trialing')->count(),
            'paused' => Subscription::where('status', 'paused')->count(),
            'canceled' => Subscription::where('status', 'canceled')->count(),
            'past_due' => Subscription::where('status', 'past_due')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
            'cancel_at_period_end' => Subscription::where('cancel_at_period_end', true)->count(),
        ];
    }
}
