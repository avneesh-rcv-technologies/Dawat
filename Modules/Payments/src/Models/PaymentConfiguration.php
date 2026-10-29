<?php
namespace Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Payments\Models\PaymentMethod;

class PaymentConfiguration extends Model
{
    protected $fillable = ['payment_method_id', 'merchant_name', 'amount', 'is_active', 'config'];

    protected $casts = [
        'config' => 'array',
    ];

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
