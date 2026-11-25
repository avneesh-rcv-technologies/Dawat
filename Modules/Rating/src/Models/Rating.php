<?php

namespace Modules\Rating\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\User;
use Modules\Orders\Models\Order;

class Rating extends Model
{
    protected $fillable = [
        'ratable_type',
        'ratable_id',
        'user_id',
        'order_id',
        'order_item_id',
        'order_subject_key',
        'guest_name',
        'guest_email',
        'rating',
        'review',
        'ip_address',
        'is_approved',
        'status',
        'admin_status',
        'admin_approved_by',
        'admin_approved_at',
        'restaurant_status',
        'restaurant_approved_by',
        'restaurant_approved_at',
        'restaurant_reply',
        'restaurant_replied_at',
        'rejection_reason',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'status' => 'boolean',
        'rating' => 'integer',
        'admin_approved_at' => 'datetime',
        'restaurant_approved_at' => 'datetime',
        'restaurant_replied_at' => 'datetime',
    ];

    /**
     * Get the parent ratable model.
     */
    public function ratable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope to get approved ratings.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true)->where(function ($q) {
            $q->where(function ($restaurant) {
                $restaurant->where('ratable_type', \Modules\Restaurant\Models\Restaurant::class)->where('admin_status', 'approved');
            })->orWhere(function ($food) {
                $food->where('ratable_type', \Modules\FoodManagements\Models\FoodItem::class)
                    ->where('admin_status', '!=', 'rejected')
                    ->where(function ($decision) {
                        $decision->where('admin_status', 'approved')->orWhere('restaurant_status', 'approved');
                    });
            })->orWhereNotIn('ratable_type', [\Modules\Restaurant\Models\Restaurant::class, \Modules\FoodManagements\Models\FoodItem::class]);
        });
    }

    /**
     * Scope to get active ratings.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get reviewer name (user or guest).
     */
    public function getReviewerNameAttribute(): string
    {
        return $this->user ? $this->user->name : ($this->guest_name ?? 'Anonymous');
    }
}
