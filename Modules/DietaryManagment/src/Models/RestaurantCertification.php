<?php

namespace Modules\DietaryManagment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Acl\Models\Admin;
use Modules\Restaurant\Models\Restaurant;

class RestaurantCertification extends Model
{
    protected $attributes = ['status' => 'submitted'];

    public const STATUSES = [
        'not_submitted', 'submitted', 'under_review', 'approved', 'rejected', 'changes_requested',
    ];

    protected $fillable = [
        'restaurant_id', 'certification_type_id', 'file_path', 'file_name', 'status',
        'submitted_at', 'admin_notes', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = ['submitted_at' => 'datetime', 'reviewed_at' => 'datetime'];

    public function documentVersion(): string
    {
        return hash('sha256', $this->file_path.'|'.$this->status.'|'.$this->updated_at?->format('Y-m-d H:i:s.u'));
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function certificationType(): BelongsTo
    {
        return $this->belongsTo(CertificationType::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }
}
