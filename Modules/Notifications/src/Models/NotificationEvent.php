<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationEvent extends Model
{
    protected $fillable = [
        'event_code',
        'event_name',
        'event_description',
        'notification_class',
        'status'
    ];

    public function mappings(): HasMany
    {
        return $this->hasMany(NotificationMapping::class, 'event_id');
    }

    public function activeMappings(): HasMany
    {
        return $this->mappings()->whereHas('channel', function($query) {
            $query->where('status', true);
        });
    }
}