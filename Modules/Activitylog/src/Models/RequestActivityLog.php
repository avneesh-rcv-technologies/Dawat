<?php

namespace Modules\Activitylog\Models;

use Illuminate\Database\Eloquent\Model;

class RequestActivityLog extends Model
{
    protected $table = 'request_activity_logs';

    protected $fillable = [
        'ip_address',
        'guard',
        'causer_type',
        'causer_id',
        'method',
        'url',
        'path',
        'route_name',
        'route_action',
        'query_params',
        'request_body',
        'headers',
        'user_agent',
        'referer',
        'request_middleware',
        'web_middleware_map',
        'response_status',
        'response_headers',
        'response_body',
        'duration_ms',
        'is_blocked',
        'blocked_reason',
    ];

    protected $casts = [
        'query_params' => 'array',
        'request_body' => 'array',
        'headers' => 'array',
        'request_middleware' => 'array',
        'web_middleware_map' => 'array',
        'response_headers' => 'array',
        'is_blocked' => 'boolean',
    ];
}
