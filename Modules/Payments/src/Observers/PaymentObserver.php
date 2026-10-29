<?php

namespace Modules\Payments\Observers;

use Modules\Orders\Models\Order;
use Modules\Payments\Models\Payments;
use Modules\Payments\Events\PaymentStatusUpdated;
use Modules\Payments\Enums\PaymentStatus;

class PaymentObserver
{
    /**
     * Handle the Payment "updating" event.
     */
    public function updating(Payments $payment): void
    {
        // This method can be used for validation or additional logic before payment updates
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payments $payment): void
    {
        // This method can be used for additional logic after payment updates
    }

    /**
     * Handle the Payment "creating" event.
     */
    public function creating(Payments $payment): void
    {
        // This method can be used for validation or additional logic before payment creation
    }

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payments $payment): void
    {
        // This method can be used for additional logic after payment creation
    }

    /**
     * Handle the Payment "deleting" event.
     */
    public function deleting(Payments $payment): void
    {
        // This method can be used for validation or additional logic before payment deletion
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payments $payment): void
    {
        // This method can be used for additional logic after payment deletion
    }

    /**
     * Handle PaymentStatusUpdated events
     */
    public function handlePaymentStatusUpdated(PaymentStatusUpdated $event): void
    {
        $event->payment->update(['status' => $event->newStatus]);

        $order = $event->payment->order;
        if (!$order) {
            return;
        }

        if (in_array($event->newStatus, ['failed', 'cancelled', 'refunded'], true)) {
            app(\Modules\Orders\Services\OrderService::class)
                ->markOrderPaymentFailed($order, 'Payment status updated to ' . $event->newStatus);
            return;
        }

        $order->update([
            'payment_status' => in_array($event->newStatus, [PaymentStatus::COMPLETED->value, PaymentStatus::CAPTURED->value, PaymentStatus::PAID->value], true)
                ? Order::PAYMENT_PAID : Order::PAYMENT_PENDING,
        ]);
    }
}
