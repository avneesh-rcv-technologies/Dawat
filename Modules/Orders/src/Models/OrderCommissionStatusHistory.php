<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Acl\Models\Admin;

class OrderCommissionStatusHistory extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['created_at' => 'datetime'];

    public function commission()
    {
        return $this->belongsTo(OrderCommission::class, 'order_commission_id');
    }

    public function actor()
    {
        return $this->belongsTo(Admin::class, 'changed_by');
    }
}
