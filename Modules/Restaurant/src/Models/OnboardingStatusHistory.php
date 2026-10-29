<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Modules\Restaurant\Enums\OnboardingStep;

class OnboardingStatusHistory extends Model
{
    protected $table = 'restaurant_onboarding_status_histories';

    protected $fillable = [
        'restaurant_id',
        'step_key',
        'action',
        'old_status',
        'new_status',
        'changed_by',
        'reason',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    public function scopeRequiredOnboardingSteps(Builder $query): Builder
    {
        $keys = array_map(fn (OnboardingStep $step) => $step->value, OnboardingStep::required());

        return $query->where(fn (Builder $history) => $history
            ->whereNull('step_key')->orWhereIn('step_key', $keys));
    }

    public function isResubmission(): bool
    {
        return $this->action === 'resubmitted'
            || (in_array($this->step_key, ['tax', 'banking'], true) && $this->action === 'submitted'
                && $this->new_status === 'submitted'
                && in_array($this->old_status, ['submitted', 'pending', 'under_review', 'approved', 'rejected'], true));
    }

    public function changer()
    {
        return $this->belongsTo(\Modules\Acl\Models\Admin::class, 'changed_by');
    }
}
