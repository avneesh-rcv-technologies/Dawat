<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Acl\Models\Admin;
use Modules\Orders\Support\CommissionRefundAmounts;
use Modules\Restaurant\Models\Restaurant;

class OrderCommission extends Model
{
    public const STATUSES = ['pending', 'on_hold', 'processed', 'cancelled', 'failed'];

    private const TRANSITIONS = [
        'pending' => ['pending', 'on_hold', 'processed', 'cancelled', 'failed'],
        'on_hold' => ['on_hold', 'processed', 'cancelled', 'failed'],
        'processed' => ['processed', 'on_hold'],
        'cancelled' => ['cancelled'],
        'failed' => ['failed'],
    ];

    public function availableStatuses(): array
    {
        return self::TRANSITIONS[$this->status] ?? ['pending'];
    }

    public function isStatusLocked(): bool
    {
        return in_array($this->status, ['cancelled', 'failed'], true);
    }

    protected $attributes = ['status' => 'pending'];

    protected $fillable = ['order_id', 'restaurant_id', 'policy_reference', 'policy_snapshot',
        'order_amount', 'commission_percentage', 'platform_commission_amount', 'restaurant_amount',
        'currency', 'status', 'admin_notes', 'processed_by', 'processed_at', 'calculated_at',
        'refunded_amount', 'commission_reversed_amount', 'restaurant_refund_deduction', 'final_payable_amount', 'paid_amount'];

    protected $casts = ['policy_snapshot' => 'array', 'order_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2', 'platform_commission_amount' => 'decimal:2',
        'restaurant_amount' => 'decimal:2', 'calculated_at' => 'datetime', 'processed_at' => 'datetime',
        'refunded_amount' => 'decimal:2', 'commission_reversed_amount' => 'decimal:2',
        'restaurant_refund_deduction' => 'decimal:2', 'final_payable_amount' => 'decimal:2', 'paid_amount' => 'decimal:2'];

    public function getEffectiveCommissionAttribute(): string
    {
        return CommissionRefundAmounts::money(CommissionRefundAmounts::cents($this->platform_commission_amount)
            - CommissionRefundAmounts::cents($this->commission_reversed_amount));
    }

    public function settlementData(): array
    {
        return $this->only(['order_amount', 'commission_percentage', 'platform_commission_amount', 'restaurant_amount',
            'refunded_amount', 'commission_reversed_amount', 'restaurant_refund_deduction', 'final_payable_amount',
            'paid_amount', 'status', 'currency']) + ['effective_commission' => $this->effective_commission,
                'recovery_due_amount' => CommissionRefundAmounts::money(max(0, CommissionRefundAmounts::cents($this->paid_amount)
                    - CommissionRefundAmounts::cents($this->final_payable_amount)))];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function processor()
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    public function histories()
    {
        return $this->hasMany(OrderCommissionStatusHistory::class)->orderBy('id');
    }
}
