<?php

namespace Modules\Discounts\Models;

use Modules\Customers\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    protected $fillable = [
        'discount_id',
        'code',
        'description',
        'starts_at',
        'expires_at',
        'usage_limit',
        'usage_limit_per_user',
        'min_order_amount',
        'times_used',
        'is_active'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'min_order_amount' => 'float'
    ];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Get all usage records for this coupon using morph relationships
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Get entities that have used this coupon, filtered by type
     * 
     * @param string|null $morphType The class name to filter by (e.g., User::class, Admin::class)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsableEntities(?string $morphType = null)
    {
        $query = $this->usages()->with('usable');

        if ($morphType) {
            $query->where('usable_type', $morphType);
        }

        return $query->get()->pluck('usable')->filter();
    }

    /**
     * Check how many times a specific entity has used this coupon
     * 
     * @param mixed $entity The entity to check (User, Admin, etc.)
     * @return int
     */
    public function getUsageCountForEntity($entity): int
    {
        if (!$entity) {
            return 0;
        }

        return $this->usages()
            ->where('usable_type', get_class($entity))
            ->where('usable_id', $entity->id)
            ->count();
    }

    public function getIsValidAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }
        
        if ($this->expires_at && $this->expires_at->lt($now)) {
            return false;
        }
        
        if ($this->usage_limit && $this->times_used >= $this->usage_limit) {
            return false;
        }
        
        return true;
    }
}
