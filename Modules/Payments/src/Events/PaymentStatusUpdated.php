<?php

namespace Modules\Payments\Events;

use Modules\Payments\Models\Payments;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentStatusUpdated
{
    use Dispatchable, SerializesModels;

    public Payments $payment;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Payments|int|string $payment, string $oldStatus, string $newStatus)
    {
        $this->payment = $payment instanceof Payments ? $payment : Payments::findOrFail($payment);
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
