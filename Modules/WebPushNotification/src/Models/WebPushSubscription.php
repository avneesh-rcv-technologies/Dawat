<?php

namespace Modules\WebPushNotification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WebPushSubscription extends Model
{
    protected $fillable = [
        'subscribable_type',
        'subscribable_id',
        'token',
        'device_id',
        'device',
        'browser',
        'ip_address',
        'last_active_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    /**
     * Get the parent subscribable model (Admin or Technician).
     */
    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('last_active_at', '>=', now()->subDays(30));
    }
}
