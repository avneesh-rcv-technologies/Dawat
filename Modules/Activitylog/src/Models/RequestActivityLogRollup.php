<?php

namespace Modules\Activitylog\Models;

use Illuminate\Database\Eloquent\Model;

class RequestActivityLogRollup extends Model
{
    protected $table = 'request_activity_log_rollups';

    protected $fillable = [
        'bucket_date',
        'bucket_hour',
        'total_requests',
        'blocked_requests',
        'error_requests',
        'unique_ips',
        'total_duration_ms',
    ];

    protected $casts = [
        'bucket_date' => 'date',
        'bucket_hour' => 'integer',
        'total_requests' => 'integer',
        'blocked_requests' => 'integer',
        'error_requests' => 'integer',
        'unique_ips' => 'integer',
        'total_duration_ms' => 'integer',
    ];
}
