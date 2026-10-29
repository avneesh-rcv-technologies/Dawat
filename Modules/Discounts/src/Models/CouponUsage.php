<?php

namespace Modules\Discounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CouponUsage extends Model
{
    protected $fillable = [
        'coupon_id',
        'usable_type',
        'usable_id',
        'order_id',
        'discount_amount',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'discount_amount' => 'float',
    ];

    /**
     * Get the coupon that owns this usage record
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the usable entity (User, Admin, Organization, etc.)
     */
    public function usable(): MorphTo
    {
        return $this->morphTo();
    }
}
