<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Acl\Models\Admin;

class RestaurantOnboardingStep extends Model
{
    protected $table = 'restaurant_onboarding_steps';

    protected $fillable = [
        'restaurant_id',
        'step_key',
        'status',
        'rejection_reason',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(fn (self $step) => app(\Modules\Restaurant\Services\OnboardingHistoryService::class)->stepSaved($step, true));
        static::updated(fn (self $step) => app(\Modules\Restaurant\Services\OnboardingHistoryService::class)->stepSaved($step, false));
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }
}
