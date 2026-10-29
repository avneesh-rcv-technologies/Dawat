<?php

namespace Modules\Payments\Models;

use Modules\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['code', 'class_name', 'name', 'template', 'processor', 'is_active'];

    public function configurations()
    {
        return $this->hasMany(PaymentConfiguration::class);
    }

    public function configuration()
    {
        return $this->belongsTo(PaymentConfiguration::class,'id', 'payment_method_id');
    }
}
