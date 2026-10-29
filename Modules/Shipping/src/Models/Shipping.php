<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shipping\Enums\ShippingStatus;

class Shipping extends Model
{
    protected $fillable = [
        'order_id',
        'shipping_method_code',
        'tracking_number',
        'status',
        'cost',
        'currency',
        'delivery_address',
        'delivery_instructions',
        'estimated_delivery_at',
        'actual_delivery_at',
        'metadata',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'delivery_address' => 'array',
        'metadata' => 'array',
        'estimated_delivery_at' => 'datetime',
        'actual_delivery_at' => 'datetime',
        'status' => ShippingStatus::class,
    ];

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_code', 'code');
    }

    public function order()
    {
        return $this->belongsTo(\Modules\Orders\Models\Order::class);
    }

    public function scopeByStatus($query, ShippingStatus $status)
    {
        return $query->where('status', $status->value);
    }

    public function scopeByTrackingNumber($query, string $trackingNumber)
    {
        return $query->where('tracking_number', $trackingNumber);
    }
}