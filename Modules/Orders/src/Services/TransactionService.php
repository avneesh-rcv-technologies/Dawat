<?php

namespace Modules\Orders\Services;

use Illuminate\Support\Str;
use Modules\Orders\Models\Order;
use Illuminate\Support\Facades\DB;
use Modules\Payments\Models\Payment;
use Modules\Payments\Models\Payments;
use Modules\Orders\Models\Transaction;
use Modules\Orders\Enums\TransactionType;
use Modules\Orders\Enums\TransactionStatus;
use Modules\Orders\Events\TransactionStatusUpdated;

class TransactionService
{
    /**
     * Record a new transaction
     *
     * @param Order $order
     * @param string $type
     * @param float $amount
     * @param string $status
     * @param Payment|null $payment
     * @param array $metadata
     * @param string|null $notes
     * @return Transaction
     */
    public function record(
        Order $order,
        string $type,
        float $amount,
        string $status = TransactionStatus::PENDING->value,
        ?Payments $payment = null,
        array $metadata = [],
        ?string $notes = null
    ) {
        return DB::transaction(function () use ($order, $type, $amount, $status, $payment, $metadata, $notes) {
            // Calculate running balance
            $balance = $this->calculateNewBalance($order, $amount, $type);

            $transaction = Transaction::updateOrCreate([
                'order_id' => $order->id,
                'payment_id' => $payment?->id,
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => $type,
                'amount' => $amount,
                'balance' => $balance,
                'currency' => $order->currency,
                'status' => $status,
                'processed_at' => $status === TransactionStatus::COMPLETED->value ? now() : null,
                'gateway_reference' => $metadata['gateway_response'] ?? null,
                'metadata' => $metadata,
                'notes' => $notes,
            ]);

            // Update order balance if needed
            $this->updateOrderBalance($order, $balance);

            return $transaction;
        });
    }

    public function updateTransaction(Transaction $transaction, array $data)
    {
        return DB::transaction(function () use ($transaction, $data) {
            $transaction->update([
                'status' => $data['status'] ?? $transaction->status,
                'amount' => $data['amount'] ?? $transaction->amount,
                'balance' => $data['balance'] ?? $transaction->balance,
                'notes' => $data['notes'] ?? $transaction->notes,
                'gateway_reference' => $data['gateway_response'] ?? $transaction->gateway_reference,
                'metadata' => $data['metadata'] ?? $transaction->metadata,
                'processed_at' => isset($data['status']) && $data['status'] === TransactionStatus::COMPLETED->value
                    ? now()
                    : $transaction->processed_at,
            ]);

            if (isset($data['balance'])) {
                $this->updateOrderBalance($transaction->order, $data['balance']);
            }

            return $transaction->fresh();
        });
    }

    /**
     * Update transaction status
     *
     * @param Transaction $transaction
     * @param string $status
     * @param string|null $notes
     * @return Transaction
     */
    public function updateStatus(
        Transaction $transaction,
        string $status,
        ?string $notes = null
    ): Transaction {
        if (!TransactionStatus::tryFrom($status)) {
            throw new \InvalidArgumentException('Invalid transaction status.');
        }

        return DB::transaction(function () use ($transaction, $status, $notes) {
            $transaction = $transaction->newQuery()->whereKey($transaction->getKey())->lockForUpdate()->firstOrFail();
            $oldStatus = $transaction->status;
            if ($oldStatus === $status && !$notes) {
                return $transaction;
            }

            $updates = ['status' => $status];
            if ($status === TransactionStatus::COMPLETED->value && $oldStatus !== $status) {
                $updates['processed_at'] = now();
            }

            if ($notes) {
                $updates['notes'] = $transaction->notes
                    ? $transaction->notes . "\n" . $notes
                    : $notes;
            }

            $transaction->update($updates);
            if ($oldStatus !== $status) {
                TransactionStatusUpdated::dispatch($transaction, $oldStatus, $status);
            }

            if ($oldStatus !== $status && $transaction->order) {
                $this->updateOrderBalance($transaction->order);
            }

            return $transaction->fresh();
        });
    }

    /**
     * Refund a transaction
     *
     * @param Transaction $originalTransaction
     * @param float $amount
     * @param string $reason
     * @param array $metadata
     * @return Transaction
     */
    public function refund(
        Transaction $originalTransaction,
        float $amount,
        string $reason,
        array $metadata = []
    ): Transaction {
        if ($amount > $originalTransaction->amount) {
            throw new \InvalidArgumentException("Refund amount cannot exceed original transaction amount");
        }

        return $this->record(
            $originalTransaction->order,
            string: TransactionType::REFUND->value,
            amount: $amount,
            string: TransactionStatus::COMPLETED->value,
            payment: $originalTransaction->payment,
            metadata: array_merge($metadata, [
                'original_transaction_id' => $originalTransaction->id,
                'refund_reason' => $reason
            ]),
            notes: "Refund of {$amount} for transaction #{$originalTransaction->transaction_number}. Reason: {$reason}"
        );
    }

    /**
     * Generate unique transaction number
     */
    protected function generateTransactionNumber(): string
    {
        do {
            $number = 'TXN-' . date('Ymd') . '-' . strtoupper(Str::random(8));
        } while (Transaction::where('transaction_number', $number)->exists());

        return $number;
    }

    /**
     * Calculate new balance after transaction
     */
    protected function calculateNewBalance(Order $order, float $amount, string $type): float
    {
        $currentBalance = $order->transactions()
            ->whereIn('status', [TransactionStatus::COMPLETED, TransactionStatus::PENDING])
            ->latest()
            ->value('balance') ?? 0;

        return match ($type) {
            TransactionType::PAYMENT->value, TransactionType::CAPTURE->value => $currentBalance - $amount,
            TransactionType::REFUND->value, TransactionType::ADJUSTMENT->value => $currentBalance + $amount,
            default => $currentBalance
        };
    }

    /**
     * Update order balance based on transactions
     */
    protected function updateOrderBalance(Order $order, ?float $newBalance = null): void
    {
        if ($newBalance === null) {
            $newBalance = $order->transactions()
                ->where('status', TransactionStatus::COMPLETED)
                ->latest()
                ->value('balance') ?? $order->total;
        }

        $order->update(['balance_due' => max(0, $newBalance)]);
    }

    /**
     * Get order transaction history
     */
    public function getOrderTransactions(Order $order, array $filters = [])
    {
        return $order->transactions()
            ->when($filters['type'] ?? null, fn($q, $type) => $q->where('type', $type))
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get transaction by reference
     */
    public function findByReference(string $reference): ?Transaction
    {
        return Transaction::where('transaction_number', $reference)
            ->orWhere('gateway_reference', $reference)
            ->first();
    }
}
