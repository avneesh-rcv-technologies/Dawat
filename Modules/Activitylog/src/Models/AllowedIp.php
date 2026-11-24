<?php

namespace Modules\Activitylog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AllowedIp extends Model
{
    protected $table = 'allowed_ips';

    protected $fillable = [
        'ip_address',
        'note',
        'is_active',
        'expires_at',
        'approved_by_id',
        'approved_by_type',
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

    public static function isAllowed(string $ipAddress): bool
    {
        return static::query()
            ->active()
            ->where('ip_address', $ipAddress)
            ->exists();
    }
}
