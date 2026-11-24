<?php
namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Orders\Models\Order;
use Modules\Payments\Models\Payment;
use Modules\Payments\Models\Payments;
use Modules\Orders\Events\TransactionStatusUpdated;
use Modules\Orders\Enums\TransactionStatus;
use Modules\Payments\Enums\PaymentStatus;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_number',
        'order_id',
        'payment_id',
        'type',
        'amount',
        'status',
        'currency',
        'notes',
        'gateway_reference',
        'metadata',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime'
    ];

    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND = 'refund';
    const TYPE_ADJUSTMENT = 'adjustment';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REVERSED = 'reversed';

    public function getPaymentUrlAttribute(): ?string
    {
        return \Modules\Stripe\Services\OrderPaymentLink::activeUrl(
            $this->metadata, $this->type, $this->status,
            $this->order?->status, $this->order?->payment_status
        );
    }

    public function collector()
    {
        return $this->belongsTo(\Modules\Acl\Models\Admin::class, 'processed_by');
    }

    public function getCollectorLabelAttribute(): string
    {
        $metadata = $this->metadata ?? [];
        $name = $metadata['collected_by_name'] ?? $this->collector?->name;
        $email = $metadata['collected_by_email'] ?? $this->collector?->email;
        if ($name && $email) return $name.' ('.$email.')';
        return $name ?: ($email ?: 'Admin #'.($metadata['collected_by'] ?? $this->processed_by ?? 'unknown'));
    }

    public function getDisplayNotesAttribute(): ?string
    {
        $notes = $this->notes;
        if (($this->metadata['collection_type'] ?? null) === 'cash' && is_string($notes)) {
            $collectorId = $this->metadata['collected_by'] ?? $this->processed_by;
            if ($collectorId) {
                $notes = str_replace('Cash collected manually by admin #'.$collectorId.'.',
                    'Cash collected manually by '.$this->collector_label.'.', $notes);
            }
            return $notes;
        }
        if ($this->type !== self::TYPE_PAYMENT || !is_string($notes)
            || !preg_match('/^Payment (?:completed|failed|pending|authorized|processing|unpaid|refunded|cancelled|partially refunded) via ([a-zA-Z0-9_-]+)$/D', trim($notes), $matches)) {
            return $notes;
        }

        $paymentStatus = match (TransactionStatus::tryFrom($this->status ?? '')) {
            TransactionStatus::COMPLETED => PaymentStatus::COMPLETED,
            TransactionStatus::FAILED, TransactionStatus::DECLINED => PaymentStatus::FAILED,
            TransactionStatus::VOIDED => PaymentStatus::CANCELLED,
            // Pending includes unpaid, processing, and authorized payments.
            TransactionStatus::PENDING => str_starts_with(trim($notes), 'Payment failed via ')
                ? PaymentStatus::PENDING : null,
            default => null,
        };

        return $paymentStatus?->transactionNote($matches[1]) ?? $notes;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payments::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', self::TYPE_REFUND);
    }

    public function markAsCompleted(string $notes = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now(),
            'notes' => $notes ? $this->notes . "\n" . $notes : $this->notes
        ]);
    }
}
