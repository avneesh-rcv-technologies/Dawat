<?php

namespace Modules\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subscribable_type',
        'subscribable_id',
        'plan_id',
        'subscription_product_id',
        'status',
        'start_date',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'ends_at',
        'cancel_at_period_end',
        'canceled_at',
        'gateway_subscription_id',
        'gateway_customer_type',
        'gateway_customer_id',
        'payment_gateway',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the subscribable entity (user, admin, organization, etc.).
     */
    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the gateway customer entity (user, admin, organization, etc.).
     */
    public function gatewayCustomer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the plan associated with the subscription (through subscription_product).
     * For backward compatibility, keep direct plan relationship.
     * In new code, prefer using subscriptionProduct->plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the subscription product (product-plan mapping) that created this subscription.
     * This is the primary relationship - use this to get both product and plan.
     */
    public function subscriptionProduct(): BelongsTo
    {
        return $this->belongsTo(SubscriptionProduct::class, 'subscription_product_id');
    }

    /**
     * Get the effective plan - through subscription_product if available, otherwise direct.
     * This allows transparent access regardless of whether subscription was created
     * from a product purchase or directly with a plan.
     */
    public function getEffectivePlan(): ?Plan
    {
        // If subscription_product_id exists, get plan through it
        if ($this->subscription_product_id && $this->relationLoaded('subscriptionProduct')) {
            return $this->subscriptionProduct?->plan;
        }

        if ($this->subscription_product_id) {
            return $this->subscriptionProduct()->first()?->plan;
        }
        
        // Fallback to direct plan relationship for backward compatibility
        return $this->plan;
    }

    /**
     * Get the reminders for this subscription.
     */
    public function reminders()
    {
        return $this->hasMany(SubscriptionReminder::class);
    }

    /**
     * Get all features associated with this subscription.
     */
    public function subscriptionFeatures(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'subscription_features')
            ->withPivot(['value', 'used', 'usage_reset_at', 'is_custom', 'custom_notes', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get only active features for this subscription.
     */
    public function activeFeatures(): BelongsToMany
    {
        return $this->subscriptionFeatures()->wherePivot('is_active', true);
    }

    /**
     * Assign features from plan to this subscription.
     * This should be called when a subscription is created or plan is changed.
     */
    public function assignPlanFeatures(): void
    {
        $plan = $this->getEffectivePlan();
        
        if (!$plan) {
            return;
        }

        $planFeatures = $plan->planFeatures()->get();

        foreach ($planFeatures as $feature) {
            // Check if feature already exists for this subscription
            $existingFeature = $this->subscriptionFeatures()
                ->where('feature_id', $feature->id)
                ->first();

            if ($existingFeature) {
                // Update existing feature unless it's a custom override
                if (!$existingFeature->pivot->is_custom) {
                    $this->subscriptionFeatures()->updateExistingPivot($feature->id, [
                        'value' => $feature->pivot->value,
                        'is_active' => true,
                    ]);
                }
            } else {
                // Assign new feature
                $this->subscriptionFeatures()->attach($feature->id, [
                    'value' => $feature->pivot->value,
                    'used' => 0,
                    'usage_reset_at' => $this->calculateUsageResetDate($plan->billing_cycle),
                    'is_custom' => false,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Calculate when usage should reset based on billing cycle.
     */
    protected function calculateUsageResetDate(string $billingCycle): ?Carbon
    {
        return match($billingCycle) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'yearly' => now()->addYear(),
            default => $this->current_period_end,
        };
    }

    /**
     * Check if subscription has a specific feature by name.
     */
    public function hasSubscriptionFeature(string $featureName): bool
    {
        return $this->activeFeatures()
            ->where('features.name', $featureName)
            ->exists();
    }

    /**
     * Get the value of a specific feature for this subscription.
     */
    public function getSubscriptionFeatureValue(string $featureName): ?string
    {
        $feature = $this->activeFeatures()
            ->where('features.name', $featureName)
            ->first();

        return $feature ? $feature->pivot->value : null;
    }

    /**
     * Check if subscription feature is enabled (for boolean features).
     */
    public function isSubscriptionFeatureEnabled(string $featureName): bool
    {
        $value = $this->getSubscriptionFeatureValue($featureName);
        return $value && in_array(strtolower($value), ['true', '1', 'yes', 'enabled']);
    }

    /**
     * Get numeric feature limit for subscription.
     */
    public function getSubscriptionFeatureLimit(string $featureName): ?float
    {
        $value = $this->getSubscriptionFeatureValue($featureName);
        
        if (!$value) {
            return null;
        }

        // Check if unlimited
        if (in_array(strtolower($value), ['unlimited', '-1', 'null'])) {
            return -1; // -1 represents unlimited
        }

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Get current usage for a limit-based feature.
     */
    public function getFeatureUsage(string $featureName): float
    {
        $feature = $this->subscriptionFeatures()
            ->where('features.name', $featureName)
            ->first();

        return $feature ? (float) $feature->pivot->used : 0;
    }

    /**
     * Increment feature usage.
     */
    public function incrementFeatureUsage(string $featureName, float $amount = 1): bool
    {
        $feature = $this->subscriptionFeatures()
            ->where('features.name', $featureName)
            ->first();

        if (!$feature) {
            return false;
        }

        $newUsage = (float) $feature->pivot->used + $amount;
        
        $this->subscriptionFeatures()->updateExistingPivot($feature->id, [
            'used' => $newUsage,
        ]);

        return true;
    }

    /**
     * Decrement feature usage.
     */
    public function decrementFeatureUsage(string $featureName, float $amount = 1): bool
    {
        $feature = $this->subscriptionFeatures()
            ->where('features.name', $featureName)
            ->first();

        if (!$feature) {
            return false;
        }

        $newUsage = max(0, (float) $feature->pivot->used - $amount);
        
        $this->subscriptionFeatures()->updateExistingPivot($feature->id, [
            'used' => $newUsage,
        ]);

        return true;
    }

    /**
     * Reset feature usage to zero.
     */
    public function resetFeatureUsage(string $featureName): bool
    {
        $feature = $this->subscriptionFeatures()
            ->where('features.name', $featureName)
            ->first();

        if (!$feature) {
            return false;
        }

        $plan = $this->getEffectivePlan();
        
        $this->subscriptionFeatures()->updateExistingPivot($feature->id, [
            'used' => 0,
            'usage_reset_at' => $plan ? $this->calculateUsageResetDate($plan->billing_cycle) : null,
        ]);

        return true;
    }

    /**
     * Check if feature usage is within limit.
     */
    public function canUseFeature(string $featureName, float $amount = 1): bool
    {
        $limit = $this->getSubscriptionFeatureLimit($featureName);
        
        // Unlimited
        if ($limit === -1) {
            return true;
        }

        // No limit set
        if ($limit === null) {
            return false;
        }

        $currentUsage = $this->getFeatureUsage($featureName);
        
        return ($currentUsage + $amount) <= $limit;
    }

    /**
     * Get remaining quota for a feature.
     */
    public function getFeatureRemaining(string $featureName): ?float
    {
        $limit = $this->getSubscriptionFeatureLimit($featureName);
        
        // Unlimited
        if ($limit === -1) {
            return -1;
        }

        // No limit set
        if ($limit === null) {
            return null;
        }

        $currentUsage = $this->getFeatureUsage($featureName);
        
        return max(0, $limit - $currentUsage);
    }

    /**
     * Override a feature value for this subscription (custom configuration).
     */
    public function overrideFeature(string $featureName, string $value, ?string $notes = null): bool
    {
        $feature = Feature::where('name', $featureName)->first();
        
        if (!$feature) {
            return false;
        }

        $existingFeature = $this->subscriptionFeatures()
            ->where('feature_id', $feature->id)
            ->first();

        if ($existingFeature) {
            $this->subscriptionFeatures()->updateExistingPivot($feature->id, [
                'value' => $value,
                'is_custom' => true,
                'custom_notes' => $notes,
            ]);
        } else {
            $plan = $this->getEffectivePlan();
            $this->subscriptionFeatures()->attach($feature->id, [
                'value' => $value,
                'used' => 0,
                'usage_reset_at' => $plan ? $this->calculateUsageResetDate($plan->billing_cycle) : null,
                'is_custom' => true,
                'custom_notes' => $notes,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return true;
    }

    /**
     * Scope to get active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get trialing subscriptions.
     */
    public function scopeTrialing($query)
    {
        return $query->where('status', 'trialing');
    }

    /**
     * Scope to get canceled subscriptions.
     */
    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    /**
     * Scope to get expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope to get subscriptions ending soon.
     */
    public function scopeEndingSoon($query, int $days = 7)
    {
        return $query->where('current_period_end', '<=', now()->addDays($days))
                     ->where('current_period_end', '>=', now())
                     ->whereIn('status', ['active', 'trialing']);
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if subscription is on trial.
     */
    public function onTrial(): bool
    {
        return $this->status === 'trialing' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has ended.
     */
    public function hasExpiredTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Check if subscription is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled' || $this->canceled_at !== null;
    }

    /**
     * Check if subscription has ended.
     */
    public function hasEnded(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Check if subscription is past due.
     */
    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    /**
     * Check if subscription is valid (active or on trial).
     */
    public function isValid(): bool
    {
        return $this->isActive() || $this->onTrial();
    }

    /**
     * Get days remaining in trial.
     */
    public function trialDaysRemaining(): int
    {
        if (!$this->onTrial()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Get days remaining in current period.
     */
    public function daysRemaining(): int
    {
        if (!$this->current_period_end) {
            return 0;
        }

        return max(0, now()->diffInDays($this->current_period_end, false));
    }

    /**
     * Cancel subscription immediately.
     */
    public function cancelNow(): bool
    {
        $this->update([
            'status' => 'canceled',
            'canceled_at' => now(),
            'ends_at' => now(),
            'cancel_at_period_end' => false,
        ]);

        return true;
    }

    /**
     * Cancel subscription at period end.
     */
    public function cancelAtPeriodEnd(): bool
    {
        $this->update([
            'cancel_at_period_end' => true,
            'canceled_at' => now(),
            'ends_at' => $this->current_period_end,
        ]);

        return true;
    }

    /**
     * Resume a canceled subscription.
     */
    public function resume(): bool
    {
        if (!$this->isCanceled() || $this->hasEnded()) {
            return false;
        }

        $this->update([
            'status' => 'active',
            'cancel_at_period_end' => false,
            'canceled_at' => null,
            'ends_at' => null,
        ]);

        return true;
    }

    /**
     * Swap to a different plan.
     */
    public function swapPlan(int $planId): bool
    {
        $this->update([
            'plan_id' => $planId,
        ]);

        return true;
    }

    /**
     * Renew subscription for next period.
     */
    public function renew(): bool
    {
        $plan = $this->plan;
        
        $periodStart = $this->current_period_end ?? now();
        $periodEnd = $this->calculatePeriodEnd($periodStart, $plan->billing_cycle);

        $this->update([
            'status' => 'active',
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
        ]);

        return true;
    }

    /**
     * Calculate period end based on billing cycle.
     */
    protected function calculatePeriodEnd(Carbon $start, string $billingCycle): Carbon
    {
        return match($billingCycle) {
            'daily' => $start->copy()->addDay(),
            'weekly' => $start->copy()->addWeek(),
            'monthly' => $start->copy()->addMonth(),
            'quarterly' => $start->copy()->addMonths(3),
            'yearly' => $start->copy()->addYear(),
            default => $start->copy()->addMonth(),
        };
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'trialing' => 'blue',
            'past_due' => 'yellow',
            'paused' => 'gray',
            'canceled' => 'red',
            'expired' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }
}
