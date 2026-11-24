<?php

namespace Modules\Payments\Observers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\OrderCommissionService;
use Modules\Orders\Services\OrderService;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Events\PaymentStatusUpdated;
use Modules\Payments\Models\Payments;
use Modules\Payments\Services\PaymentSuccessNotificationService;

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
        $this->scheduleSuccessNotification($payment);
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
        $this->scheduleSuccessNotification($payment);
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

    private function scheduleSuccessNotification(Payments $payment): void
    {
        if (in_array($payment->status, ['completed', 'paid', 'captured'], true)
            && in_array($payment->payment_type, [null, 'order_payment'], true)) {
            DB::afterCommit(function () use ($payment) {
                try {
                    app(OrderCommissionService::class)->recordPaidOrder((int) $payment->order_id);
                } catch (\Throwable $exception) {
                    Log::error('Payment commission could not be recorded.', [
                        'order_id' => $payment->order_id, 'exception_class' => get_class($exception),
                    ]);
                }
            });
        }
        try {
            app(PaymentSuccessNotificationService::class)->schedule($payment);
        } catch (\Throwable $exception) {
            Log::warning('Payment receipt could not be scheduled.', [
                'payment_id' => $payment->id, 'exception_class' => get_class($exception),
            ]);
        }
    }

    /**
     * Handle PaymentStatusUpdated events
     */
    public function handlePaymentStatusUpdated(PaymentStatusUpdated $event): void
    {
        $event->payment->update(['status' => $event->newStatus]);

        $order = $event->payment->order;
        if (! $order) {
            return;
        }

        if (in_array($event->newStatus, ['failed', 'cancelled', 'refunded'], true)) {
            app(OrderService::class)
                ->markOrderPaymentFailed($order, 'Payment status updated to '.$event->newStatus);

            return;
        }

        $order->update([
            'payment_status' => in_array($event->newStatus, [PaymentStatus::COMPLETED->value, PaymentStatus::CAPTURED->value, PaymentStatus::PAID->value], true)
                ? Order::PAYMENT_PAID : Order::PAYMENT_PENDING,
        ]);
    }
}
