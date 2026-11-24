<?php

namespace Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Acl\Models\Admin;

class RefundStatusHistory extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['before' => 'array', 'after' => 'array', 'created_at' => 'datetime'];

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }

    public function actor()
    {
        return $this->belongsTo(Admin::class, 'changed_by');
    }
}
