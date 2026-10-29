<?php

namespace Modules\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSocialAccount extends Model
{
    protected $fillable = [
        'customer_id',
        'provider',
        'provider_id',
        'provider_email',
        'provider_token',
        'provider_refresh_token',
        'token_expires_at',
        'provider_data',
        'last_login_at',
    ];

    protected $casts = [
        'provider_data' => 'array',
        'token_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    protected $hidden = [
        'provider_token',
        'provider_refresh_token',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Scopes
    public function scopeForProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeForProviderId($query, string $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    // Methods
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }
}
