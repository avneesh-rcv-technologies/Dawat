<?php

namespace Modules\Orders\Events;

use Modules\Orders\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionStatusUpdated
{
    use Dispatchable, SerializesModels;

    public Transaction $transaction;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Transaction $transaction, string $oldStatus, string $newStatus)
    {
        $this->transaction = $transaction;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
