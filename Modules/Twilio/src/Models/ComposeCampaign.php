<?php

namespace Modules\Twilio\Models;

use Modules\Acl\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Modules\Activitylog\Traits\LogsActivity;

class ComposeCampaign extends Model
{
    use LogsActivity;

    protected $table = 'twilio_compose_campaigns';
    protected $fillable = [
        'admin_id',
        'name',
        'message',
        'phone_numbers',
        'status',
        'total_numbers',
        'sent_count',
        'failed_count',
        'scheduled_at',
        'completed_at',
    ];

    protected $casts = [
        'phone_numbers' => 'array',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function messages()
    {
        return $this->hasMany(TwilioMessage::class, 'campaign_id');
    }
}
