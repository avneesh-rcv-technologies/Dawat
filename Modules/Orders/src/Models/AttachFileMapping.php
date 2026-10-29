<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Acl\Models\Admin;
use Modules\Customers\Models\User;
use Modules\Filemanager\Models\FileLink;

class AttachFileMapping extends Model
{
    protected $table = 'attach_file_mappings';

    protected $fillable = [
        'admin_id',
        'user_id',
        'attachment_id',
        'order_id',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the admin who processed the attachment
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the user who owns the attachment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the file link for the attachment
     */
    public function attachment()
    {
        return $this->belongsTo(FileLink::class, 'attachment_id');
    }

    /**
     * Get the order this attachment is associated with
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope a query to only include accepted attachments
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include rejected attachments
     */
    public function scopeRejected($query)
    {
        return $query->where('status', false);
    }

    /**
     * Get the status as a human-readable string
     */
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Accepted' : 'Rejected';
    }
}
