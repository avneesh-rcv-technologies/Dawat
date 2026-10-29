<?php

namespace Modules\Subscriptions\Services;

use Modules\Subscriptions\Repositories\PlanRepository;
use Modules\Subscriptions\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

class PlanService
{
    protected $planRepository;

    public function __construct(PlanRepository $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    /**
     * Get all active plans.
     */
    public function getActivePlans(): Collection
    {
        return $this->planRepository->getActivePlans();
    }

    /**
     * Get plan by ID.
     */
    public function getPlanById(int $id): Plan
    {
        return $this->planRepository->findOrFail($id);
    }

    /**
     * Create a new plan.
     */
    public function createPlan(array $data): Plan
    {
        // Set defaults
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_popular'] = $data['is_popular'] ?? false;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['trial_days'] = $data['trial_days'] ?? 0;

        return $this->planRepository->create($data);
    }

    /**
     * Update a plan.
     */
    public function updatePlan(int $id, array $data): bool
    {
        return $this->planRepository->update($id, $data);
    }

    /**
     * Delete a plan.
     */
    public function deletePlan(int $id): bool
    {
        // Check if plan has active subscriptions
        if ($this->planRepository->hasActiveSubscriptions($id)) {
            throw new \Exception('Cannot delete plan with active subscriptions.');
        }

        return $this->planRepository->delete($id);
    }

    /**
     * Validate plan data.
     */
    public function validatePlanData(array $data): array
    {
        return validator($data, [
            'name' => 'required|string|max:128',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:daily,weekly,monthly,quarterly,yearly,one-time',
            'features' => 'nullable|array',
            'trial_days' => 'nullable|integer|min:0',
            'max_users' => 'nullable|integer|min:0',
            'max_projects' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ])->validate();
    }

    /**
     * Get plan statistics.
     */
    public function getPlanStatistics(): array
    {
        return [
            'total' => Plan::count(),
            'active' => Plan::where('is_active', true)->count(),
            'inactive' => Plan::where('is_active', false)->count(),
            'popular' => Plan::where('is_popular', true)->count(),
            'free' => Plan::where('price', 0)->count(),
            'paid' => Plan::where('price', '>', 0)->count(),
            'with_trial' => Plan::where('trial_days', '>', 0)->count(),
        ];
    }
}
