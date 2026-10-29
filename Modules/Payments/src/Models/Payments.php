<?php
namespace Modules\Payments\Models;

use Modules\Orders\Models\Order;
use Modules\Orders\Models\Transaction;
use Modules\Restaurant\Models\Restaurant;
use Illuminate\Database\Eloquent\Model;
use Modules\Payments\Models\PaymentMethod;

class Payments extends Model
{
    protected $table = 'payments';
    
    protected $fillable = [
        'order_id',
        'vendor_type',
        'vendor_id',
        'payment_method_id',        
        'transaction_id',
        'amount',
        'currency',
        'status',
        'payment_type',
        'details',
        'gateway_reference',
        'gateway_fee',
        'failure_reason',
        'processed_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'details' => 'array',
        'processed_at' => 'datetime'
    ];

    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    // Payment Type Constants
    const TYPE_ORDER_PAYMENT = 'order_payment';
    const TYPE_REFUND = 'refund';
    const TYPE_PARTIAL_REFUND = 'partial_refund';

    /* ================= Relationships ================= */

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Polymorphic relationship to vendor (can be Restaurant, Store, etc.)
     * Usage examples:
     * - For Modules\Restaurant\Models\Restaurant: $payment->vendor_type = 'Modules\Restaurant\Models\Restaurant'; $payment->vendor_id = $restaurantId;
     */
    public function vendor()
    {
        return $this->morphTo();
    }

    /**
     * Legacy relationship for backward compatibility
     * @deprecated Use vendor() instead
     */
    public function restaurant()
    {
        return $this->vendor();
    }

    public function method()
    {
        return $this->belongsTo(PaymentConfiguration::class, 'payment_method_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'payment_id');
    }

    /* ================= Scopes ================= */

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Legacy scope for backward compatibility
     * @deprecated Use scopeByVendor() instead
     */
    public function scopeByRestaurant($query, $restaurantId)
    {
        return $this->scopeByVendor($query, $restaurantId);
    }

    public function scopeOrderPayments($query)
    {
        return $query->where('payment_type', self::TYPE_ORDER_PAYMENT);
    }

    public function scopeRefunds($query)
    {
        return $query->whereIn('payment_type', [self::TYPE_REFUND, self::TYPE_PARTIAL_REFUND]);
    }

    /* ================= Helpers ================= */

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isRefunded(): bool
    {
        return in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    public function canBeRefunded(): bool
    {
        return $this->status === self::STATUS_COMPLETED && 
               $this->payment_type === self::TYPE_ORDER_PAYMENT;
    }

    public function getFormattedStatusAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getFormattedPaymentTypeAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->payment_type));
    }

    public function getNetAmountAttribute(): float
    {
        return $this->amount - $this->gateway_fee;
    }

    /* ================= Payment Actions ================= */

    public function markAsCompleted(string $gatewayTransactionId, array $response = [])
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'transaction_id' => $gatewayTransactionId,
            'processed_at' => now(),
            'details' => array_merge($this->details ?? [], ['gateway_response' => $response])
        ]);

        // Update order payment status
        $this->order->update(['payment_status' => 'paid']);
    }

    public function markAsFailed(string $reason, array $response = [])
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $reason,
            'processed_at' => now(),
            'details' => array_merge($this->details ?? [], [
                'gateway_response' => $response,
                'failure_reason' => $reason
            ])
        ]);

        // Update order payment status
        $this->order->update(['payment_status' => 'failed']);
    }

    public function markAsProcessing()
    {
        $this->update([
            'status' => self::STATUS_PROCESSING
        ]);
    }

    public function processRefund(float $refundAmount, string $reason = null): self
    {
        return self::create([
            'order_id' => $this->order_id,
            'vendor_type' => $this->vendor_type,
            'vendor_id' => $this->vendor_id,
            'payment_method_id' => $this->payment_method_id,
            'amount' => -$refundAmount, // Negative amount for refunds
            'currency' => $this->currency,
            'status' => self::STATUS_COMPLETED,
            'payment_type' => $refundAmount >= $this->amount ? self::TYPE_REFUND : self::TYPE_PARTIAL_REFUND,
            'details' => [
                'original_payment_id' => $this->id,
                'refund_reason' => $reason
            ]
        ]);
    }
}