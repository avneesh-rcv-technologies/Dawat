<?php

namespace Modules\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'features',
        'trial_days',
        'max_users',
        'max_projects',
        'is_active',
        'is_popular',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'trial_days' => 'integer',
        'max_users' => 'integer',
        'max_projects' => 'integer',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all subscriptions for this plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get active subscriptions for this plan.
     */
    public function activeSubscriptions()
    {
        return $this->subscriptions()->where('status', 'active');
    }

    /**
     * Get all subscription products using this plan.
     */
    public function subscriptionProducts()
    {
        return $this->hasMany(SubscriptionProduct::class, 'plan_id');
    }

    /**
     * Get count of subscription products using this plan.
     */
    public function getSubscriptionProductsCountAttribute(): int
    {
        return $this->subscriptionProducts()->count();
    }

    /**
     * Get all features associated with this plan.
     */
    public function planFeatures(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot(['value', 'notes', 'is_highlighted', 'sort_order'])
            ->withTimestamps()
            ->orderBy('plan_features.sort_order')
            ->orderBy('features.sort_order');
    }

    /**
     * Get only active features for this plan.
     */
    public function activeFeatures(): BelongsToMany
    {
        return $this->planFeatures()->where('features.is_active', true);
    }

    /**
     * Get features grouped by their group name.
     */
    public function getFeaturesGrouped(): array
    {
        $features = $this->planFeatures()->get();
        $grouped = [];

        foreach ($features as $feature) {
            $group = $feature->group ?? 'Other';
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $feature;
        }

        return $grouped;
    }

    /**
     * Check if plan has a specific feature by name.
     */
    public function hasPlanFeature(string $featureName): bool
    {
        return $this->planFeatures()
            ->where('features.name', $featureName)
            ->exists();
    }

    /**
     * Get the value of a specific feature for this plan.
     */
    public function getFeatureValue(string $featureName): ?string
    {
        $feature = $this->planFeatures()
            ->where('features.name', $featureName)
            ->first();

        return $feature ? $feature->pivot->value : null;
    }

    /**
     * Check if plan feature is enabled (for boolean features).
     */
    public function isFeatureEnabled(string $featureName): bool
    {
        $value = $this->getFeatureValue($featureName);
        return $value && in_array(strtolower($value), ['true', '1', 'yes', 'enabled']);
    }

    /**
     * Get numeric feature limit.
     */
    public function getFeatureLimit(string $featureName): ?float
    {
        $value = $this->getFeatureValue($featureName);
        
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
     * Check if a feature is unlimited for this plan.
     */
    public function hasUnlimitedFeature(string $featureName): bool
    {
        return $this->getFeatureLimit($featureName) === -1;
    }

    /**
     * Scope to get only active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get plans ordered by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * Scope to get popular plans.
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Check if plan has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get billing cycle label.
     */
    public function getBillingCycleLabelAttribute(): string
    {
        return match($this->billing_cycle) {
            'daily' => 'per day',
            'weekly' => 'per week',
            'monthly' => 'per month',
            'quarterly' => 'per quarter',
            'yearly' => 'per year',
            'one-time' => 'one-time payment',
            default => $this->billing_cycle,
        };
    }

    /**
     * Check if plan is free.
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    /**
     * Check if plan has trial.
     */
    public function hasTrial(): bool
    {
        return $this->trial_days > 0;
    }
}
