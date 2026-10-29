<?php

namespace Modules\Twilio\Models;

use Modules\Acl\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Modules\Activitylog\Traits\LogsActivity;

class PhoneNumber extends Model
{
    use LogsActivity;

    protected $table = 'twilio_phone_numbers';
    protected $fillable = [
        'upload_id',
        'admin_id',
        'phone_number',
        'status',
        'delivery_sid',
        'message',
        'sent_at',
        'response_at',
        'response',
        'retry_count',
        'send_count',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'response_at' => 'datetime',
    ];

    public function upload()
    {
        return $this->belongsTo(PhoneUpload::class, 'upload_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function messages()
    {
        return $this->hasMany(TwilioMessage::class, 'phone_number_id')->orderBy('created_at', 'desc');
    }
}
