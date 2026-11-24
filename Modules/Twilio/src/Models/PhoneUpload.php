<?php

namespace Modules\Twilio\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Modules\Acl\Models\Admin;

class PhoneUpload extends Model
{

    protected $table = 'twilio_phone_uploads';
    protected $fillable = [
        'filename',
        'uploaded_by',
        'row_count',
        'status',
        'data',
        'error',
        'retry_count',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class, 'upload_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'uploaded_by');
    }
}
