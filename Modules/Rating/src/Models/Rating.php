<?php

namespace Modules\Rating\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\User;

class Rating extends Model
{
    protected $fillable = [
        'ratable_type',
        'ratable_id',
        'user_id',
        'guest_name',
        'guest_email',
        'rating',
        'review',
        'ip_address',
        'is_approved',
        'status',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'status' => 'boolean',
        'rating' => 'integer',
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

    /**
     * Scope to get approved ratings.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
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
