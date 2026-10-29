<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingConfiguration extends Model
{
    protected $fillable = [
        'shipping_method_id',
        'configuration',
    ];

    protected $casts = [
        'configuration' => 'array',
    ];

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }
}