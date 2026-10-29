<?php

namespace Modules\Discounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Discounts\Enums\DiscountType;
use Modules\Discounts\Enums\DiscountApplyTo;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by_id',
        'created_by_type',
        'type',
        'amount',
        'apply_to',
        'is_active',
        'starts_at',
        'expires_at',
        'user_groups'
    ];

    protected $casts = [
        'amount' => 'float',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'user_groups' => 'array',
        'type' => DiscountType::class,
        'apply_to' => DiscountApplyTo::class,
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(DiscountRule::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     * Get all coupon usages for this discount via the coupons relationship
     */
    public function couponUsages(): HasManyThrough
    {
        return $this->hasManyThrough(CouponUsage::class, Coupon::class);
    }

    /**
     * Get the entity that created this discount (Admin, User, etc.)
     */
    public function createdBy(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getIsValidAttribute(): bool
    {
        $now = now();
        
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }
        
        if ($this->expires_at && $this->expires_at->lt($now)) {
            return false;
        }
        
        return $this->is_active;
    }
}
