<?php

namespace Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Acl\Models\Admin;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Transaction;

class Refund extends Model
{
    public const STATUSES = ['pending', 'processing', 'succeeded', 'failed', 'rejected'];

    protected $fillable = [
        'order_id', 'payment_id', 'transaction_id', 'refund_amount', 'refund_reason',
        'refund_source', 'refund_status', 'stripe_refund_id', 'admin_notes', 'processed_by', 'processed_at',
    ];

    protected $casts = ['refund_amount' => 'decimal:2', 'processed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (self $refund) {
            do {
                $number = 'RFN-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
            } while (self::where('refund_number', $number)->exists());
            $refund->refund_number = $number;
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payments::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function processor()
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    public function histories()
    {
        return $this->hasMany(RefundStatusHistory::class)->orderBy('id');
    }

    /** Public refund fields; internal processing notes and actor details stay private. */
    public function customerData(): array
    {
        return $this->only(['id', 'refund_number', 'order_id', 'payment_id', 'refund_amount', 'refund_reason',
            'refund_source', 'refund_status', 'stripe_refund_id', 'processed_at', 'created_at', 'updated_at']);
    }

    /** Avoid serializing payment gateway configuration or unrelated order/customer data. */
    public function managementData(): array
    {
        return $this->only($this->fillable) + [
            'id' => $this->id, 'refund_number' => $this->refund_number, 'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
            'order' => $this->order?->only(['id', 'order_number', 'status', 'payment_status', 'vendor_id']),
            'payment' => $this->payment?->only(['id', 'amount', 'currency', 'status', 'gateway_reference']),
            'transaction' => $this->relationLoaded('transaction')
                ? $this->transaction?->only(['id', 'transaction_number', 'amount', 'status', 'gateway_reference']) : null,
            'histories' => $this->relationLoaded('histories') ? $this->histories->toArray() : [],
            'amount_range' => app(\Modules\Payments\Services\RefundService::class)->amountRange($this),
            'commission_settlement' => $this->order?->commission?->settlementData(),
            'commission_preview' => app(\Modules\Payments\Services\RefundService::class)->settlementPreview($this),
        ];
    }
}
