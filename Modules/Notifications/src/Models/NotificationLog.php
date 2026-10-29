<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    protected $table = 'notifications_log';
    
    protected $fillable = [
        'event_code',
        'channel_name',
        'notifiable_type',
        'notifiable_id',
        'content',
        'status',
        'error_message',
        'sent_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'content' => 'array'
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}