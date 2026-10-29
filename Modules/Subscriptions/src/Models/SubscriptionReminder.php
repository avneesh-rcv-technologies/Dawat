<?php

namespace Modules\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionReminder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subscription_id',
        'type',
        'status',
        'message',
        'subject',
        'channel',
        'reminder_number',
        'scheduled_at',
        'sent_at',
        'error_message',
        'retry_count',
        'last_retry_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'metadata' => 'json',
    ];

    /**
     * Statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_SKIPPED = 'skipped';

    /**
     * Types
     */
    const TYPE_EXPIRY = 'expiry';
    const TYPE_PAYMENT = 'payment';
    const TYPE_RENEWAL = 'renewal';
    const TYPE_OVERDUE = 'overdue';

    /**
     * Channels
     */
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_IN_APP = 'in-app';
    const CHANNEL_SMS = 'sms';

    /**
     * Get the subscription that owns this reminder.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Scope to get pending reminders
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get sent reminders
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Scope to get failed reminders
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to get by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get by channel
     */
    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope to get overdue reminders (scheduled_at is in the past)
     */
    public function scopeOverdue($query)
    {
        return $query->where('scheduled_at', '<=', now())
                     ->where('status', self::STATUS_PENDING);
    }

    /**
     * Mark as sent
     */
    public function markAsSent()
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
        
        return $this;
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
            'last_retry_at' => now(),
        ]);
        
        return $this;
    }

    /**
     * Mark as skipped
     */
    public function markAsSkipped()
    {
        $this->update([
            'status' => self::STATUS_SKIPPED,
        ]);
        
        return $this;
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor()
    {
        return match($this->status) {
            self::STATUS_SENT => 'green',
            self::STATUS_PENDING => 'yellow',
            self::STATUS_FAILED => 'red',
            self::STATUS_SKIPPED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get readable type
     */
    public function getReadableType()
    {
        return match($this->type) {
            self::TYPE_EXPIRY => 'Expiry Reminder',
            self::TYPE_PAYMENT => 'Payment Reminder',
            self::TYPE_RENEWAL => 'Renewal Reminder',
            self::TYPE_OVERDUE => 'Overdue Payment',
            default => ucfirst($this->type),
        };
    }
}
