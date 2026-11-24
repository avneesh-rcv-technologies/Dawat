<?php

namespace Modules\Restaurant\Services;

use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;

class RestaurantOrderPolicy
{
    public function transitionError(Order $order, string $target, bool $isCod): ?string
    {
        $current = OrderStatus::fromCode($order->status);
        $next = OrderStatus::fromCode($target);
        if (! $current || ! $next) {
            return 'Unknown order status.';
        }
        if ($current === $next) {
            return null;
        }
        if (! $current->canTransitionTo($next, $order->order_type)) {
            return 'This status transition is not available for this order.';
        }

        $paid = $this->transactionTotal($order, 'payment');
        $refunded = $this->transactionTotal($order, 'refund');
        if ($target === Order::STATUS_REFUNDED) {
            return $paid > 0 && $refunded >= $paid && $order->payment_status === 'refunded'
                ? null : 'Complete the payment refund before marking the order refunded.';
        }
        if ($target === Order::STATUS_FAILED) {
            return $paid > $refunded || in_array($order->payment_status, ['paid', 'completed', 'captured'], true)
                ? 'A paid order cannot be marked failed. Cancel it and review its payment instead.' : null;
        }
        if ($target === Order::STATUS_CANCELLED) {
            return null; // Like the web UI, cancellation does not automatically refund payments.
        }
        if (in_array($order->payment_status, ['failed', 'refunded', 'cancelled', 'partially_refunded'], true)) {
            return 'This payment state does not allow order fulfilment.';
        }
        if (! $isCod && (float) $order->total > 0
            && (! in_array($order->payment_status, ['paid', 'completed', 'captured'], true)
                || round($paid - $refunded, 2) < (float) $order->total)) {
            return 'The online payment must be fully completed before progressing the order.';
        }

        return null;
    }

    public function deletionError(Order $order): ?string
    {
        if (! $order->canDelete()) {
            return 'Only unpaid orders with pending (W), cancelled (C), or failed (F) status can be deleted.';
        }
        if ($order->transactions->contains(fn ($transaction) => $transaction->status === 'completed')
            || $order->payments->contains(fn ($payment) => in_array($payment->status,
                ['completed', 'paid', 'captured', 'authorized', 'processing', 'refunded', 'partially_refunded'], true))) {
            return 'Orders with settled transactions or active/settled payments cannot be deleted.';
        }

        return null;
    }

    private function transactionTotal(Order $order, string $type): float
    {
        return round($order->transactions->filter(fn ($transaction) => $transaction->type === $type
            && $transaction->status === 'completed' && $transaction->currency === $order->currency)->sum('amount'), 2);
    }
}
