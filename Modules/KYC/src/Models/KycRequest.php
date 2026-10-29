<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Acl\Models\Admin;

class KycRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'kyc_type_id',
        'requested_by',
        'title',
        'message',
        'due_date'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public $timestamps = false;

    /**
     * Get the KYC type for this request
     */
    public function kycType(): BelongsTo
    {
        return $this->belongsTo(KycType::class, 'kyc_type_id');
    }

    /**
     * Get the admin who created this request
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'requested_by');
    }

    /**
     * Check if request is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Scope for upcoming requests
     */
    public function scopeUpcoming($query)
    {
        return $query->whereDate('due_date', '>=', now());
    }

    /**
     * Scope for overdue requests
     */
    public function scopeOverdue($query)
    {
        return $query->whereDate('due_date', '<', now());
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\KYC\Database\Factories\KycRequestFactory::new();
    }
}
