<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycMessage extends Model
{
    protected $fillable = [
        'submission_id',
        'sender_type',
        'sender_id',
        'message',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Only created_at, no updated_at
    const UPDATED_AT = null;

    /**
     * Get the submission this message belongs to
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(KycSubmission::class, 'submission_id');
    }

    /**
     * Get the sender (admin)
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(\Modules\Acl\Models\Admin::class, 'sender_id');
    }
    
    /**
     * Get sender name attribute
     */
    public function getSenderNameAttribute(): string
    {
        if ($this->sender_type === 'admin' && $this->sender) {
            return $this->sender->name;
        }
        return 'System';
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for admin messages
     */
    public function scopeFromAdmin($query)
    {
        return $query->where('sender_type', 'admin');
    }

    /**
     * Scope for user messages
     */
    public function scopeFromUser($query)
    {
        return $query->where('sender_type', 'user');
    }
}
