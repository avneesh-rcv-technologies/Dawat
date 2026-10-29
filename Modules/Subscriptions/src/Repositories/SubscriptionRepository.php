<?php

namespace Modules\Subscriptions\Repositories;

use Modules\Subscriptions\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionRepository
{
    /**
     * Get all subscriptions.
     */
    public function all(): Collection
    {
        return Subscription::all();
    }

    /**
     * Find subscription by ID.
     */
    public function find(int $id): ?Subscription
    {
        return Subscription::find($id);
    }

    /**
     * Find subscription by ID or fail.
     */
    public function findOrFail(int $id): Subscription
    {
        return Subscription::findOrFail($id);
    }

    /**
     * Find subscription with relationships.
     */
    public function findWithRelations(int $id, array $relations = ['plan', 'subscriptionProduct.plan', 'subscriptionProduct.product', 'subscribable', 'gatewayCustomer']): Subscription
    {
        return Subscription::with($relations)->findOrFail($id);
    }

    /**
     * Create a new subscription.
     */
    public function create(array $data): Subscription
    {
        return Subscription::create($data);
    }

    /**
     * Update a subscription.
     */
    public function update(int $id, array $data): bool
    {
        $subscription = $this->findOrFail($id);
        return $subscription->update($data);
    }

    /**
     * Delete a subscription.
     */
    public function delete(int $id): bool
    {
        $subscription = $this->findOrFail($id);
        return $subscription->delete();
    }

    /**
     * Get active subscriptions.
     */
    public function getActive(): Collection
    {
        return Subscription::active()->get();
    }

    /**
     * Get subscriptions by status.
     */
    public function getByStatus(string $status): Collection
    {
        return Subscription::where('status', $status)->get();
    }

    /**
     * Get subscriptions ending soon.
     */
    public function getEndingSoon(int $days = 7): Collection
    {
        return Subscription::endingSoon($days)->get();
    }

    /**
     * Cancel subscription immediately.
     */
    public function cancelNow(int $id): bool
    {
        $subscription = $this->findOrFail($id);
        return $subscription->cancelNow();
    }

    /**
     * Cancel subscription at period end.
     */
    public function cancelAtPeriodEnd(int $id): bool
    {
        $subscription = $this->findOrFail($id);
        return $subscription->cancelAtPeriodEnd();
    }

    /**
     * Resume a canceled subscription.
     */
    public function resume(int $id): bool
    {
        $subscription = $this->findOrFail($id);
        return $subscription->resume();
    }

    /**
     * Swap subscription plan.
     */
    public function swapPlan(int $id, int $planId): bool
    {
        $subscription = $this->findOrFail($id);
        return $subscription->swapPlan($planId);
    }

    /**
     * Swap subscription product (product-plan combination).
     */
    public function swapSubscriptionProduct(int $id, int $subscriptionProductId): bool
    {
        $subscription = $this->findOrFail($id);
        
        // Update the subscription to use the new subscription product
        $subscription->subscription_product_id = $subscriptionProductId;
        
        // Also update plan_id from the new subscription product for backward compatibility
        $subscriptionProduct = \Modules\Subscriptions\Models\SubscriptionProduct::find($subscriptionProductId);
        if ($subscriptionProduct) {
            $subscription->plan_id = $subscriptionProduct->plan_id;
        }
        
        return $subscription->save();
    }

    /**
     * Renew subscription.
     */
    public function renew(int $id): bool
    {
        $subscription = $this->findOrFail($id);
        return $subscription->renew();
    }
}
