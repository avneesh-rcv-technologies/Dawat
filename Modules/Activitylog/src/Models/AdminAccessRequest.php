<?php

namespace Modules\Activitylog\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAccessRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REVOKED = 'revoked';

    protected $table = 'admin_access_requests';

    protected $fillable = [
        'ip_address',
        'requested_route',
        'reason',
        'status',
        'requested_by_id',
        'requested_by_type',
        'reviewed_by_id',
        'reviewed_by_type',
        'review_note',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];
}
