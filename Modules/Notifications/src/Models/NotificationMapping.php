<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationMapping extends Model
{
    protected $fillable = [
        'event_id',
        'channel_id',
        'template_id',
        'config',
        'recipients'
    ];

    protected $casts = [
        'config' => 'array',
        'recipients' => 'array'
    ];

    /**
     * Get recipient value dynamically from JSON
     */
    public function getRecipient(string $key): bool
    {
        // Check JSON recipients column
        if (!empty($this->recipients) && isset($this->recipients[$key])) {
            return (bool) $this->recipients[$key];
        }
        
        return false;
    }

    /**
     * Set all recipients from array
     */
    public function setRecipients(array $recipients): void
    {
        $this->recipients = $recipients;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(NotificationEvent::class, 'event_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class, 'channel_id');
    }

    public function scopeForChannel($query, $channelId)
    {
        return $query->where('channel_id', $channelId);
    }
}