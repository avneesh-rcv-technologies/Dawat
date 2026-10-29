<?php

namespace Modules\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CustomerOtp extends Model
{
    protected $fillable = [
        'phone',
        'email',
        'otp',
        'purpose',
        'type',
        'channel_type',
        'expires_at',
        'is_verified',
        'verified_at',
        'ip_address',
        'user_agent',
        'attempts'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('is_verified', false)
                    ->where('expires_at', '>', Carbon::now())
                    ->where('attempts', '<', 3);
    }

    public function scopeForPhone($query, string $phone)
    {
        return $query->where('phone', $phone);
    }

    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', strtolower(trim($email)));
    }

    public function scopeForIdentifier($query, string $identifier, ?string $channel = null)
    {
        $channel = $channel ?: (str_contains($identifier, '@') ? 'email' : 'phone');

        if ($channel === 'email') {
            return $query->where('email', strtolower(trim($identifier)));
        }

        return $query->where('phone', $identifier);
    }

    public function scopeForPurpose($query, string $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    public function scopeForChannelType($query, string $channelType)
    {
        return $query->where('channel_type', strtolower(trim($channelType)));
    }

    // Methods
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->is_verified 
            && !$this->isExpired() 
            && $this->attempts < 3;
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    public function markAsVerified(): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => Carbon::now()
        ]);
    }
}
