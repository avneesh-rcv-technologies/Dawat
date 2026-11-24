<?php

namespace Modules\Activitylog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Activitylog\Support\ThreatProtectionCache;

class BlockedIp extends Model
{
    protected $table = 'blocked_ips';

    protected $fillable = [
        'ip_address',
        'reason',
        'is_active',
        'blocked_by_id',
        'blocked_by_type',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public static function isBlocked(string $ipAddress): bool
    {
        return ThreatProtectionCache::isIpBlocked($ipAddress);
    }
}
