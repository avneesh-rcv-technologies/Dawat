<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shipping\Models\ShippingConfiguration;

class ShippingMethod extends Model
{
    protected $fillable = [
        'code', 
        'class_name', 
        'name', 
        'template', 
        'processor', 
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function configurations()
    {
        return $this->hasMany(ShippingConfiguration::class);
    }

    public function configuration()
    {
        return $this->hasOne(ShippingConfiguration::class);
    }

    public function shippings()
    {
        return $this->hasMany(Shipping::class, 'shipping_method_code', 'code');
    }
}