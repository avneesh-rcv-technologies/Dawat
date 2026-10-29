<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Notifications\Models\NotificationMapping;

class NotificationChannel extends Model
{
    protected $fillable = [
        'name',
        'channel_class',
        'status',
        'config'
    ];

    protected $casts = [
        'status' => 'boolean',
        'config' => 'array'
    ];

    public function mappings(): HasMany
    {
        return $this->hasMany(NotificationMapping::class, 'event_id');
    }
}