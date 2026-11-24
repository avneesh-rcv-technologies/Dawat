<?php

namespace Modules\Orders\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Notifications\Services\NotificationService;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderCommission;
use Modules\Orders\Models\OrderCommissionStatusHistory;
use Modules\Orders\Support\CommissionRefundAmounts;
use Modules\Payments\Models\Payments;
use Modules\Payments\Models\Refund;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Support\PlatformCommission;

class OrderCommissionService
{
    public function schedule(Order $order): void
    {
        if (! in_array($order->payment_status, ['paid', 'completed', 'captured'], true)) {
            return;
        }
        $id = $order->id;
        DB::afterCommit(function () use ($id) {
            try {
                $this->recordPaidOrder($id);
            } catch (\Throwable $exception) {
                // A bookkeeping/channel outage must not undo a captured customer payment.
                Log::error('Paid order commission could not be recorded.', ['order_id' => $id, 'exception_class' => get_class($exception)]);
            }
        });
    }

    public function recordPaidOrder(int $orderId): ?OrderCommission
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::whereKey($orderId)->lockForUpdate()->firstOrFail();
            if (! in_array($order->payment_status, ['paid', 'completed', 'captured'], true)
                || in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_FAILED, Order::STATUS_REFUNDED], true)
                || $order->vendor_type !== (new Restaurant)->getMorphClass()) {
                return null;
            }
            $existing = OrderCommission::where('order_id', $order->id)->lockForUpdate()->first();
            // Repeated payment callbacks must preserve the original agreement, amounts and processing audit.
            if ($existing) {
                return $existing;
            }
            $restaurant = Restaurant::whereKey($order->vendor_id)->lockForUpdate()->firstOrFail();
            if (! $restaurant->platform_commission_accepted || $restaurant->platform_commission_percentage === null) {
                return null;
            }
            $rate = (float) $restaurant->platform_commission_percentage;
            if ($rate < 0 || $rate > 100) {
                throw ValidationException::withMessages(['commission_percentage' => 'The accepted commission rate must be between 0 and 100.']);
            }
            $amount = (int) round((float) $order->total * 100);
            if ($amount <= 0) {
                return null;
            }
            $captured = Payments::where('order_id', $order->id)->where('payment_type', 'order_payment')
                ->where('currency', $order->currency)
                ->whereIn('status', ['completed', 'paid', 'captured'])->where('amount', '>', 0)->sum('amount');
            if ((int) round((float) $captured * 100) < $amount) {
                return null;
            }
            $fee = intdiv($amount * (int) round($rate * 100) + 5000, 10000);
            $commission = OrderCommission::updateOrCreate(['order_id' => $order->id], [
                'restaurant_id' => $restaurant->id,
                'policy_reference' => PlatformCommission::SETTING_KEY,
                'policy_snapshot' => ['basis' => 'order_total', 'accepted_percentage' => number_format($rate, 2, '.', ''),
                    'accepted_at' => $restaurant->platform_commission_accepted_at?->toIso8601String()],
                'order_amount' => $amount / 100, 'commission_percentage' => $rate,
                'platform_commission_amount' => $fee / 100, 'restaurant_amount' => ($amount - $fee) / 100,
                'final_payable_amount' => ($amount - $fee) / 100,
                'currency' => $order->currency, 'status' => 'pending', 'processed_at' => null, 'calculated_at' => now(),
            ]);
            $this->history($commission, null, null, 'Commission calculated from the accepted rate when payment completed.');
            $this->notifyAfterCommit($commission, 'created');

            return $commission;
        });
    }

    public function holdCancelledOrder(int $orderId): ?OrderCommission
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::whereKey($orderId)->lockForUpdate()->firstOrFail();
            if ($order->status !== Order::STATUS_CANCELLED) {
                return null;
            }
            return $this->syncRefunds($orderId);
        });
    }

    public function syncRefunds(int $orderId, ?Admin $actor = null, ?int $refundId = null): ?OrderCommission
    {
        return DB::transaction(function () use ($orderId, $actor, $refundId) {
            $order = Order::whereKey($orderId)->lockForUpdate()->firstOrFail();
            $commission = OrderCommission::where('order_id', $orderId)->lockForUpdate()->first();
            if (! $commission) {
                return null;
            }
            // Initialize legacy balances automatically during cancellation, refund updates or processing.
            if (CommissionRefundAmounts::cents($commission->final_payable_amount) === 0
                && CommissionRefundAmounts::cents($commission->refunded_amount) === 0
                && CommissionRefundAmounts::cents($commission->paid_amount) === 0
                && ($commission->processed_at !== null || $commission->status === 'processed')) {
                $commission->paid_amount = $commission->restaurant_amount;
            }
            $successful = Refund::where('order_id', $orderId)->where('refund_status', 'succeeded')
                ->whereHas('payment', fn ($q) => $q->where('currency', $commission->currency))->sum('refund_amount');
            $legacy = Payments::where('order_id', $orderId)->where('currency', $commission->currency)
                ->whereIn('payment_type', ['refund', 'partial_refund'])->where('status', 'completed')->sum('amount');
            $total = CommissionRefundAmounts::cents($successful) + abs(CommissionRefundAmounts::cents($legacy));
            $amounts = CommissionRefundAmounts::calculate($commission->order_amount, $commission->commission_percentage,
                $commission->platform_commission_amount, $commission->restaurant_amount, CommissionRefundAmounts::money($total));
            $active = Refund::where('order_id', $orderId)->whereIn('refund_status', ['pending', 'processing'])->exists();
            $old = $commission->status;
            $status = $old;
            if ($active || ($total === 0 && $order->status === Order::STATUS_CANCELLED)) {
                $status = 'on_hold';
            } elseif ($total > 0) {
                $status = CommissionRefundAmounts::cents($commission->paid_amount) > 0 ? 'processed'
                    : (CommissionRefundAmounts::cents($amounts['final_payable_amount']) === 0 ? 'cancelled' : 'pending');
            }
            $commission->fill($amounts + ['status' => $status]);
            if ($commission->isDirty()) {
                $commission->save();
                $this->history($commission, $old, $actor, ($refundId ? 'Refund #'.$refundId.'. ' : '')
                    .'Successful refunds: '.$amounts['refunded_amount'].'; commission reversed: '.$amounts['commission_reversed_amount']
                    .'; restaurant payable: '.$amounts['final_payable_amount'].'.');
            }

            return $commission;
        });
    }

    public function updateStatus(OrderCommission $commission, array $data, Admin $actor): OrderCommission
    {
        abort_unless(app(OrderCommissionAccess::class)->canUpdate($actor), 403, 'Only authorized platform admins can update commissions.');

        return DB::transaction(function () use ($commission, $data, $actor) {
            $order = Order::whereKey($commission->order_id)->lockForUpdate()->firstOrFail();
            $commission = OrderCommission::whereKey($commission->id)->lockForUpdate()->firstOrFail();
            if ($commission->isStatusLocked()) {
                throw ValidationException::withMessages(['status' => 'Cancelled and failed commissions are read-only and cannot be updated.']);
            }
            if (CommissionRefundAmounts::cents($commission->final_payable_amount) === 0
                && CommissionRefundAmounts::cents($commission->refunded_amount) === 0) {
                $commission = $this->syncRefunds($order->id);
            }
            $status = $data['status'];
            if (! in_array($status, OrderCommission::STATUSES, true)) {
                throw ValidationException::withMessages(['status' => 'Invalid commission status.']);
            }
            if (! in_array($status, $commission->availableStatuses(), true)) {
                throw ValidationException::withMessages(['status' => 'This commission payout status transition is not allowed.']);
            }
            $hasSuccessfulRefund = CommissionRefundAmounts::cents($commission->refunded_amount) > 0;
            $hasPaid = CommissionRefundAmounts::cents($commission->paid_amount) > 0;
            $fullRefund = $hasSuccessfulRefund && CommissionRefundAmounts::cents($commission->final_payable_amount) === 0;
            $pendingRefund = $hasSuccessfulRefund && Refund::where('order_id', $order->id)->whereIn('refund_status', ['pending', 'processing'])->exists();
            if (($pendingRefund || ($order->status === Order::STATUS_CANCELLED && ! $hasSuccessfulRefund)) && $status !== 'on_hold') {
                throw ValidationException::withMessages(['status' => 'The commission for a cancelled order must stay on hold for refund review.']);
            }
            if (($status === 'cancelled' && (! $fullRefund || $hasPaid)) || ($fullRefund && ! $hasPaid && $status !== 'cancelled')) {
                throw ValidationException::withMessages(['status' => 'An unpaid fully refunded commission must be cancelled.']);
            }
            if ($hasPaid && ! in_array($status, ['processed', 'on_hold'], true)) {
                throw ValidationException::withMessages(['status' => 'A previously processed payout must retain its payment history.']);
            }
            if ($status === 'processed' && ! $hasPaid && (! in_array($order->payment_status, ['paid', 'completed', 'captured', 'partially_refunded'], true)
                || $order->status === Order::STATUS_FAILED
                || (in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_REFUNDED], true) && ! $hasSuccessfulRefund))) {
                throw ValidationException::withMessages(['status' => 'Cancelled or refunded orders require review before commission can be marked processed.']);
            }
            if ($status === 'processed' && ! $hasPaid) {
                $commission->paid_amount = $commission->final_payable_amount;
            }
            $old = $commission->status;
            $commission->fill(['status' => $status, 'admin_notes' => $data['admin_notes'] ?? $commission->admin_notes]);
            if (! $commission->isDirty()) {
                return $commission;
            }
            if (! $hasPaid) {
                $commission->fill(['processed_by' => $actor->id, 'processed_at' => $status === 'processed' ? now() : null]);
            }
            $commission->save();
            $this->history($commission, $old, $actor, $commission->admin_notes);
            if ($old !== $status) {
                $this->notifyAfterCommit($commission, 'updated');
            }

            return $commission;
        });
    }

    private function history(OrderCommission $commission, ?string $old, ?Admin $actor, ?string $notes): void
    {
        OrderCommissionStatusHistory::create(['order_commission_id' => $commission->id,
            'old_status' => $old, 'new_status' => $commission->status, 'changed_by' => $actor?->id,
            'notes' => $notes, 'created_at' => now()]);
    }

    private function notifyAfterCommit(OrderCommission $commission, string $event): void
    {
        $snapshot = clone $commission;
        DB::afterCommit(function () use ($snapshot, $event) {
            try {
                $snapshot->load('order', 'restaurant.admin');
                $owner = $snapshot->restaurant?->admin;
                // Recipient mappings send this order-specific record to its owner and the platform admin.
                $sent = app(NotificationService::class)->trigger('Orders', 'OrderCommission', $owner ?? '',
                    ['commission' => $snapshot, 'event' => $event], ['email']);
                if (! $sent) {
                    Log::warning('Order commission email could not be queued.', ['commission_id' => $snapshot->id]);
                }
            } catch (\Throwable $exception) {
                Log::warning('Order commission notification failed.', ['commission_id' => $snapshot->id, 'exception_class' => get_class($exception)]);
            }
        });
    }
}
