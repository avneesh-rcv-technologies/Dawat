<?php

namespace Modules\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'type',
        'unit',
        'metadata',
        'default_value',
        'group',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get all plans that have this feature.
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features')
            ->withPivot(['value', 'notes', 'is_highlighted', 'sort_order'])
            ->withTimestamps();
    }

    /**
     * Get all subscriptions that have this feature.
     */
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Subscription::class, 'subscription_features')
            ->withPivot(['value', 'used', 'usage_reset_at', 'is_custom', 'custom_notes', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Scope to get only active features.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get features ordered by group and sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('group')->orderBy('sort_order');
    }

    /**
     * Scope to get features by group.
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to get features by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if this is a boolean feature.
     */
    public function isBoolean(): bool
    {
        return $this->type === 'boolean';
    }

    /**
     * Check if this is a limit-based feature.
     */
    public function isLimit(): bool
    {
        return $this->type === 'limit';
    }

    /**
     * Check if this is a package/tier feature.
     */
    public function isPackage(): bool
    {
        return $this->type === 'package';
    }

    /**
     * Get formatted display value.
     */
    public function getFormattedValue(string $value): string
    {
        if ($this->isBoolean()) {
            return $value === 'true' || $value === '1' ? 'Yes' : 'No';
        }

        if ($this->isLimit()) {
            $formatted = number_format((float) $value);
            return $this->unit ? "{$formatted} {$this->unit}" : $formatted;
        }

        return $value; // Package type returns as-is
    }

    /**
     * Check if a value is unlimited.
     */
    public function isUnlimited(string $value): bool
    {
        return in_array(strtolower($value), ['unlimited', '-1', 'null', '']);
    }

    /**
     * Get min value from metadata.
     */
    public function getMinValue(): ?float
    {
        return $this->metadata['min'] ?? null;
    }

    /**
     * Get max value from metadata.
     */
    public function getMaxValue(): ?float
    {
        return $this->metadata['max'] ?? null;
    }

    /**
     * Get step value from metadata.
     */
    public function getStepValue(): ?float
    {
        return $this->metadata['step'] ?? null;
    }

    /**
     * Get available package options from metadata.
     */
    public function getPackageOptions(): array
    {
        return $this->metadata['options'] ?? [];
    }
}
