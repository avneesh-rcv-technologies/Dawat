<?php

namespace Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReportSavedFilter extends Model
{
    protected $fillable = [
        'report_id',
        'user_id',
        'user_type',
        'name',
        'filters',
        'is_default',
        'is_public',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_default' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * Get the report this filter belongs to.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the user who created this filter.
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }

    /**
     * Alias for user relation.
     */
    public function owner(): MorphTo
    {
        return $this->user();
    }

    /**
     * Scope to get default filters.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get public filters.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, Model|int $user, ?string $userType = null)
    {
        if ($user instanceof Model) {
            $userType = $userType ?: $user->getMorphClass();
            $userId = $user->getKey();
        } else {
            $userId = $user;
        }

        $query->where('user_id', $userId);

        if ($userType) {
            $query->where('user_type', $userType);
        }

        return $query;
    }

    /**
     * Scope to get accessible filters for a user.
     */
    public function scopeAccessibleBy($query, Model|int $user, ?string $userType = null)
    {
        if ($user instanceof Model) {
            $userType = $userType ?: $user->getMorphClass();
            $userId = $user->getKey();
        } else {
            $userId = $user;
        }

        return $query->where(function ($q) use ($userId, $userType) {
            $q->where('user_id', $userId)
              ->when($userType, fn ($sub) => $sub->where('user_type', $userType))
              ->orWhere('is_public', true);
        });
    }
}
