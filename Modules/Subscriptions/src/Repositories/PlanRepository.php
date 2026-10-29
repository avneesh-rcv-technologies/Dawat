<?php

namespace Modules\Subscriptions\Repositories;

use Modules\Subscriptions\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

class PlanRepository
{
    /**
     * Get all plans.
     */
    public function all(): Collection
    {
        return Plan::all();
    }

    /**
     * Get active plans ordered.
     */
    public function getActivePlans(): Collection
    {
        return Plan::active()->ordered()->get();
    }

    /**
     * Find plan by ID.
     */
    public function find(int $id): ?Plan
    {
        return Plan::find($id);
    }

    /**
     * Find plan by ID or fail.
     */
    public function findOrFail(int $id): Plan
    {
        return Plan::findOrFail($id);
    }

    /**
     * Create a new plan.
     */
    public function create(array $data): Plan
    {
        return Plan::create($data);
    }

    /**
     * Update a plan.
     */
    public function update(int $id, array $data): bool
    {
        $plan = $this->findOrFail($id);
        return $plan->update($data);
    }

    /**
     * Delete a plan.
     */
    public function delete(int $id): bool
    {
        $plan = $this->findOrFail($id);
        return $plan->delete();
    }

    /**
     * Get plan with active subscriptions count.
     */
    public function getWithActiveSubscriptionsCount(int $id): Plan
    {
        return Plan::withCount('activeSubscriptions')->findOrFail($id);
    }

    /**
     * Check if plan has active subscriptions.
     */
    public function hasActiveSubscriptions(int $id): bool
    {
        $plan = $this->findOrFail($id);
        return $plan->activeSubscriptions()->count() > 0;
    }
}
