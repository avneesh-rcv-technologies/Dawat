<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycSubmissionData extends Model
{
    protected $table = 'kyc_submission_data';
    
    protected $fillable = [
        'submission_id',
        'field_id',
        'field_key',
        'field_value',
        'verified',
        'verified_at',
        'notes'
    ];

    protected $casts = [
        'verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the submission this data belongs to
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(KycSubmission::class, 'submission_id');
    }

    /**
     * Get the field this data belongs to
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(KycField::class, 'field_id');
    }

    /**
     * Mark data as verified
     */
    public function markAsVerified(string $notes = null): void
    {
        $this->update([
            'verified' => true,
            'verified_at' => now(),
            'notes' => $notes
        ]);
    }

    /**
     * Mark data as unverified
     */
    public function markAsUnverified(string $notes = null): void
    {
        $this->update([
            'verified' => false,
            'verified_at' => null,
            'notes' => $notes
        ]);
    }
}
