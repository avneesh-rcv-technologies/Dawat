<?php

namespace Modules\Activitylog\Models;

use Illuminate\Database\Eloquent\Model;

class AccessRequestOtp extends Model
{
    protected $table = 'access_request_otps';

    protected $fillable = [
        'admin_access_request_id',
        'ip_address',
        'request_token',
        'otp_hash',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];
}
