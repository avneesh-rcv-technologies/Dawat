<?php

namespace Modules\Orders\Observers;

use Modules\Payments\Models\Payments;
use Modules\Orders\Models\Transaction;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Orders\Enums\TransactionStatus;
use Modules\Payments\Events\PaymentStatusUpdated;

class TransactionObserver
{
    /**
     * Handle the Transaction "updating" event.
     */
    public function updating(Transaction $transaction): void
    {
        if ($transaction->isDirty('status') && !$transaction->isDirty('notes')) {
            $transaction->notes = $transaction->display_notes;
        }
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        // Update related payment status when transaction status changes
        if ($transaction->wasChanged('status')) {
            $this->updatePaymentStatus($transaction);
        }
    }

    /**
     * Handle the Transaction "creating" event.
     */
    public function creating(Transaction $transaction): void
    {
        // This method can be used for validation or additional logic before transaction creation
    }

    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        // This method can be used for additional logic after transaction creation
    }

    /**
     * Handle the Transaction "deleting" event.
     */
    public function deleting(Transaction $transaction): void
    {
        // This method can be used for validation or additional logic before transaction deletion
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        // This method can be used for additional logic after transaction deletion
    }

    /**
     * Update payment status based on transaction status
     */
    private function updatePaymentStatus(Transaction $transaction): void
    {
        // Only update payment status for payment type transactions
        if ($transaction->type !== Transaction::TYPE_PAYMENT) {
            return;
        }

        $payment = $transaction->payment;
        if (!$payment) {
            return;
        }

        $newPaymentStatus = $this->mapTransactionStatusToPaymentStatus($transaction->status);

        if ($newPaymentStatus && $payment->status !== $newPaymentStatus) {
            // Dispatch event for payment module to handle
            PaymentStatusUpdated::dispatch($payment, $payment->status, $newPaymentStatus);
        }
    }

    /**
     * Map transaction status to payment status
     */
    private function mapTransactionStatusToPaymentStatus(string $transactionStatus): ?string
    {
        return match($transactionStatus) {

            TransactionStatus::COMPLETED->value => PaymentStatus::COMPLETED->value,
            TransactionStatus::PENDING->value => PaymentStatus::PENDING->value,
            TransactionStatus::FAILED->value => PaymentStatus::FAILED->value,
            TransactionStatus::VOIDED->value => PaymentStatus::CANCELLED->value,
            TransactionStatus::DECLINED->value => PaymentStatus::FAILED->value,
            default => null,
        };
    }
}
