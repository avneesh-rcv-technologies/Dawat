<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Acl\Models\Admin;

class KycSubmission extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'kyc_type_id',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(fn (self $submission) => app(\Modules\Restaurant\Services\OnboardingHistoryService::class)->kycSaved($submission, true));
        static::updated(fn (self $submission) => app(\Modules\Restaurant\Services\OnboardingHistoryService::class)->kycSaved($submission, false));
    }

    /**
     * Get the KYC type for this submission
     */
    public function kycType(): BelongsTo
    {
        return $this->belongsTo(KycType::class, 'kyc_type_id');
    }

    /**
     * Get the reviewer (admin) for this submission
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    /**
     * Account (restaurant owner) this submission belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    /** @deprecated Prefer user() */
    public function owner(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Get the submission data
     */
    public function data(): HasMany
    {
        return $this->hasMany(KycSubmissionData::class, 'submission_id');
    }

    /**
     * Get the files for this submission
     */
    public function files(): HasMany
    {
        return $this->hasMany(KycFile::class, 'submission_id');
    }

    /**
     * Get the messages for this submission
     */
    public function messages(): HasMany
    {
        return $this->hasMany(KycMessage::class, 'submission_id');
    }

    /**
     * Scope for pending submissions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for under review submissions
     */
    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    /**
     * Scope for approved submissions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected submissions
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if submission is editable
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['pending', 'rejected']);
    }

    public function getRefillRequestedAttribute(): bool
    {
        if ($this->status !== 'rejected' || !$this->rejection_reason) return false;

        $message = 'Changes requested: ' . $this->rejection_reason;
        if ($this->relationLoaded('messages')) {
            $latest = $this->messages->filter(fn ($item) => str_starts_with($item->message, 'Changes requested: ')
                || $item->message === 'Refill request moved to review.')->sortByDesc('id')->first();
        } else {
            $latest = $this->messages()->where(fn ($query) => $query
                ->where('message', 'like', 'Changes requested: %')
                ->orWhere('message', 'Refill request moved to review.'))->latest('id')->first();
        }

        return $latest && $latest->message === $message && (int) $latest->sender_id === (int) $this->reviewed_by;
    }

    /**
     * Mark submission as under review
     */
    public function markAsUnderReview(int $adminId): void
    {
        $this->update([
            'status' => 'under_review',
            'reviewed_by' => $adminId,
            'reviewed_at' => now()
        ]);
    }

    /**
     * Approve submission
     */
    public function approve(int $adminId): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Reject submission
     */
    public function reject(int $adminId, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Activity Log Configuration
     */
    protected function getLogAttributes(): array
    {
        return ['status', 'reviewed_by', 'rejection_reason'];
    }

    protected function getDisplayName(): string
    {
        return "KYC Submission #{$this->id}";
    }

    protected function getActivityDescription(string $eventName): string
    {
        $userName = auth('admin')->user()->name ?? auth()->user()->name ?? 'System';

        return match ($eventName) {
            'created' => "{$userName} created KYC submission #{$this->id}",
            'updated' => "{$userName} updated KYC submission #{$this->id}",
            'deleted' => "{$userName} deleted KYC submission #{$this->id}",
            default => "{$userName} performed {$eventName} on KYC submission #{$this->id}"
        };
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\KYC\Database\Factories\KycSubmissionFactory::new();
    }
}
