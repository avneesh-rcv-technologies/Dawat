<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Acl\Models\Admin;

class ComplianceRecord extends Model
{
    protected $fillable = [
        'user_id',
        'kyc_type_id',
        'submission_id',
        'valid_from',
        'expires_at',
        'validity_period',
        'status',
        'reminder_sent_at',
        'expired_notified_at',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'expires_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'expired_notified_at' => 'datetime',
    ];

    /** Compliance subject is an Admin record (restaurant owner account). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    /** @deprecated Use user() — kept for existing ComplianceService call sites. */
    public function owner(): BelongsTo
    {
        return $this->user();
    }

    public function kycType(): BelongsTo
    {
        return $this->belongsTo(KycType::class, 'kyc_type_id');
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(KycSubmission::class, 'submission_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
