<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class RestaurantEmailOtp extends Model
{
    protected $table = 'restaurant_email_otps';

    protected $fillable = [
        'admin_id',
        'email',
        'type',
        'otp',
        'expires_at',
        'attempts',
        'verified_at',
        'last_sent_at',
        'resend_count',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(
            \Modules\Acl\Models\Admin::class,
            'admin_id'
        );
    }

    /**
     * Encrypt OTP before storing.
     */
    public function setOtpAttribute($value): void
    {
        $this->attributes['otp'] = $value === null
            ? null
            : Crypt::encryptString((string) $value);
    }

    /**
     * Decrypt OTP when retrieving.
     */
    public function getOtpAttribute($value)
    {
        if (!$value) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public function markAsVerified(): void
    {
        $this->verified_at = now();
        $this->save();
    }
}