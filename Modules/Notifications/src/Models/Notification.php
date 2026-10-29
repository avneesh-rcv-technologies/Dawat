<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $table = 'notifications';

    protected $fillable = [
        'type',
        'notifiable_id',
        'notifiable_type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function markAsRead(): void
    {
        $this->read_at = now();
        $this->save();
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function notifiable()
    {
        return $this->morphTo();
    }
}
