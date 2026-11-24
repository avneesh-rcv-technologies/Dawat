<?php

namespace Modules\Payments\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Notifications\Services\NotificationService;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Transaction;
use Modules\Orders\Services\OrderCacheService;
use Modules\Orders\Services\OrderCommissionService;
use Modules\Orders\Services\TransactionService;
use Modules\Orders\Support\CommissionRefundAmounts;
use Modules\Payments\Models\Payments;
use Modules\Payments\Models\Refund;
use Modules\Payments\Models\RefundStatusHistory;

class RefundService
{
    private const TRANSITIONS = [
        'pending' => ['pending', 'processing', 'succeeded', 'failed', 'rejected'],
        'processing' => ['succeeded', 'failed', 'rejected'],
        'failed' => ['failed', 'processing', 'succeeded', 'rejected'],
        'rejected' => ['rejected', 'pending'],
        'succeeded' => ['succeeded'],
    ];

    private function cents($amount): int
    {
        return CommissionRefundAmounts::cents($amount);
    }

    public function availableStatuses(Refund $refund): array
    {
        return self::TRANSITIONS[$refund->refund_status] ?? [];
    }

    /** Called inside cancellation, also safe to retry independently. No gateway calls. */
    public function requestForCancellation(Order $order, string $source, ?Admin $actor = null): void
    {
        if (! in_array($source, ['customer_cancellation', 'restaurant_cancellation', 'admin'], true)) {
            throw new \InvalidArgumentException('Invalid refund source.');
        }
        DB::transaction(function () use ($order, $source, $actor) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->status !== Order::STATUS_CANCELLED) {
                throw ValidationException::withMessages(['order' => 'Refund requests require a cancelled order.']);
            }
            $payments = Payments::where('order_id', $order->id)->where('payment_type', 'order_payment')
                ->whereIn('status', ['completed', 'paid', 'captured', 'partially_refunded'])
                ->where('amount', '>', 0)->orderBy('id')->lockForUpdate()->get();
            foreach ($payments as $payment) {
                if (Refund::where('payment_id', $payment->id)->where('order_id', $order->id)->exists()) {
                    continue;
                }
                $remaining = $this->cents($payment->amount) - $this->legacyRefundedCents($payment);
                if ($remaining <= 0) {
                    continue;
                }
                $refund = Refund::create([
                    'order_id' => $order->id, 'payment_id' => $payment->id,
                    'refund_amount' => $remaining / 100, 'refund_reason' => $order->cancellation_reason,
                    'refund_source' => $source, 'refund_status' => 'pending',
                ]);
                $this->syncTransaction($refund, $payment, $order, $actor);
                $this->history($refund, null, $order, $payment, $actor);
                $this->afterCommit($refund, true);
            }
        });
    }

    public function update(Refund $refund, array $data, Admin $actor): Refund
    {
        abort_unless(app(RefundAccess::class)->canUpdate($actor), 403, 'Only authorized platform admins can update refunds.');

        return DB::transaction(function () use ($refund, $data, $actor) {
            // Always lock in the same order as cancellation and payment collection.
            $order = Order::whereKey($refund->order_id)->lockForUpdate()->firstOrFail();
            $payment = Payments::whereKey($refund->payment_id)->lockForUpdate()->firstOrFail();
            $refund = Refund::whereKey($refund->id)->lockForUpdate()->firstOrFail();
            // Apply PATCH defaults to the locked row, preserving fields omitted by the caller.
            $data = array_replace($refund->only([
                'refund_status', 'refund_amount', 'stripe_refund_id', 'admin_notes',
            ]), $data);
            if ((int) $payment->order_id !== (int) $order->id || $payment->payment_type !== 'order_payment'
                || $payment->currency !== $order->currency || ! in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_REFUNDED], true)) {
                throw ValidationException::withMessages(['refund_status' => 'This refund does not belong to a cancelled order payment.']);
            }
            $next = $data['refund_status'];
            if (! in_array($next, self::TRANSITIONS[$refund->refund_status] ?? [], true)) {
                throw ValidationException::withMessages(['refund_status' => 'This refund status transition is not allowed.']);
            }
            $amount = $this->cents($data['refund_amount']);
            if ($amount <= 0) {
                throw ValidationException::withMessages(['refund_amount' => 'Enter an amount greater than zero.']);
            }
            $reference = trim((string) ($data['stripe_refund_id'] ?? '')) ?: null;
            $notes = $data['admin_notes'] ?? null;
            $fields = ['refund_amount' => number_format($amount / 100, 2, '.', ''),
                'refund_status' => $next, 'stripe_refund_id' => $reference, 'admin_notes' => $notes];
            $refund->fill($fields);
            if (! $refund->isDirty()) {
                $commission = $order->commission()->first();
                if ($commission && $this->cents($commission->final_payable_amount) === 0
                    && $this->cents($commission->refunded_amount) === 0) {
                    app(OrderCommissionService::class)->syncRefunds($order->id, $actor, $refund->id);
                }

                return $refund;
            }
            if ($refund->getOriginal('refund_status') === 'succeeded') {
                throw ValidationException::withMessages(['refund_status' => 'A succeeded refund is final and cannot be edited.']);
            }
            $otherSucceeded = Refund::where('payment_id', $payment->id)->whereKeyNot($refund->id)
                ->where('refund_status', 'succeeded')->sum('refund_amount');
            $remaining = $this->cents($payment->amount) - $this->legacyRefundedCents($payment) - $this->cents($otherSucceeded);
            if ($amount > $remaining) {
                throw ValidationException::withMessages(['refund_amount' => 'Refund amount exceeds the remaining captured payment.']);
            }
            if (in_array($next, ['processing', 'succeeded'], true) && ! $reference) {
                throw ValidationException::withMessages(['stripe_refund_id' => 'Enter the gateway refund ID or manual refund reference.']);
            }
            if ($reference && Refund::where('stripe_refund_id', $reference)->whereKeyNot($refund->id)->exists()) {
                throw ValidationException::withMessages(['stripe_refund_id' => 'This refund reference is already recorded.']);
            }
            $before = $this->snapshot($refund->fresh(), $order, $payment);
            $oldStatus = $refund->getOriginal('refund_status');
            $refund->fill(['processed_by' => $actor->id,
                'processed_at' => in_array($next, ['succeeded', 'failed', 'rejected'], true) ? now() : null])->save();
            $this->syncTransaction($refund, $payment, $order, $actor);
            if ($next === 'succeeded') {
                $this->syncPaidTotals($order, $payment);
            }
            app(OrderCommissionService::class)->syncRefunds($order->id, $actor, $refund->id);
            $this->history($refund, $before, $order, $payment, $actor);
            $this->afterCommit($refund, $oldStatus !== $next);

            return $refund->refresh();
        });
    }

    public function amountRange(Refund $refund): array
    {
        $payment = $refund->payment;
        $otherSucceeded = Refund::where('payment_id', $payment->id)->whereKeyNot($refund->id)
            ->where('refund_status', 'succeeded')->sum('refund_amount');
        $remaining = max(0, $this->cents($payment->amount) - $this->legacyRefundedCents($payment) - $this->cents($otherSucceeded));

        return ['min' => '0.01', 'max' => CommissionRefundAmounts::money($remaining), 'step' => '0.01', 'currency' => $payment->currency];
    }

    public function settlementPreview(Refund $refund): ?array
    {
        $commission = $refund->order?->commission;
        if (! $commission) {
            return null;
        }
        $total = CommissionRefundAmounts::cents($commission->refunded_amount)
            + ($refund->refund_status === 'succeeded' ? 0 : $this->cents($refund->refund_amount));
        $amounts = CommissionRefundAmounts::calculate($commission->order_amount, $commission->commission_percentage,
            $commission->platform_commission_amount, $commission->restaurant_amount, CommissionRefundAmounts::money($total));

        return $amounts + [
            'effective_commission' => CommissionRefundAmounts::money($this->cents($commission->platform_commission_amount)
                - $this->cents($amounts['commission_reversed_amount'])),
            'customer_net_paid' => CommissionRefundAmounts::money(max(0, $this->cents($commission->order_amount) - $total)),
            'recovery_due_amount' => CommissionRefundAmounts::money(max(0, $this->cents($commission->paid_amount) - $this->cents($amounts['final_payable_amount']))),
            'refund_type' => $total >= $this->cents($commission->order_amount) ? 'full' : 'partial',
            'applies_when' => 'succeeded',
        ];
    }

    private function legacyRefundedCents(Payments $payment): int
    {
        // Respect refund payment rows produced by the previous processRefund() implementation.
        return abs($this->cents(Payments::where('order_id', $payment->order_id)
            ->whereIn('payment_type', ['refund', 'partial_refund'])->where('status', 'completed')
            ->where('details->original_payment_id', $payment->id)->sum('amount')));
    }

    private function syncTransaction(Refund $refund, Payments $payment, Order $order, ?Admin $actor): void
    {
        $transaction = $refund->transaction_id
            ? Transaction::whereKey($refund->transaction_id)->lockForUpdate()->firstOrFail()
            : new Transaction(['transaction_number' => app(TransactionService::class)->generateTransactionNumber()]);
        $status = match ($refund->refund_status) {
            'succeeded' => 'completed', 'failed' => 'failed', 'rejected' => 'declined', default => 'pending',
        };
        $transaction->fill([
            'order_id' => $order->id, 'payment_id' => $payment->id, 'type' => 'refund',
            'amount' => $refund->refund_amount, 'currency' => $payment->currency, 'status' => $status,
            'gateway_reference' => $refund->stripe_refund_id,
            'processed_at' => $refund->processed_at,
            'notes' => 'Manual refund '.$refund->refund_number.' — '.$refund->refund_status,
            'metadata' => array_replace($transaction->metadata ?? [], ['refund_id' => $refund->id, 'refund_number' => $refund->refund_number, 'refund_status' => $refund->refund_status,
                'refund_source' => $refund->refund_source, 'refund_reason' => $refund->refund_reason,
                'admin_notes' => $refund->admin_notes, 'processed_by' => $actor?->id]),
        ])->forceFill(['processed_by' => $actor?->id])->save();
        if (! $refund->transaction_id) {
            $refund->update(['transaction_id' => $transaction->id]);
        }
    }

    private function syncPaidTotals(Order $order, Payments $payment): void
    {
        $total = $this->cents(Refund::where('payment_id', $payment->id)->where('refund_status', 'succeeded')->sum('refund_amount'))
            + $this->legacyRefundedCents($payment);
        $payment->update([
            'status' => $total >= $this->cents($payment->amount) ? 'refunded' : 'partially_refunded',
            'details' => array_replace($payment->details ?? [], ['refunded_amount' => number_format($total / 100, 2, '.', '')]),
        ]);
        $captured = Payments::where('order_id', $order->id)->where('payment_type', 'order_payment')
            ->whereIn('status', ['completed', 'paid', 'captured', 'refunded', 'partially_refunded'])->sum('amount');
        $refunded = $this->cents(Refund::where('order_id', $order->id)->where('refund_status', 'succeeded')->sum('refund_amount'));
        $legacy = abs($this->cents(Payments::where('order_id', $order->id)->whereIn('payment_type', ['refund', 'partial_refund'])
            ->where('status', 'completed')->sum('amount')));
        $full = $refunded + $legacy >= $this->cents($captured);
        // Refunds change payment accounting; the order's cancellation remains its lifecycle state.
        $order->update(['payment_status' => $full ? 'refunded' : 'partially_refunded']);
    }

    private function snapshot(Refund $refund, Order $order, Payments $payment): array
    {
        return $refund->only(['refund_status', 'refund_amount', 'stripe_refund_id', 'admin_notes', 'processed_by', 'processed_at'])
            + ['order_status' => $order->status, 'order_payment_status' => $order->payment_status, 'payment_status' => $payment->status,
                'commission_settlement' => $order->commission()->first()?->settlementData()];
    }

    private function history(Refund $refund, ?array $before, Order $order, Payments $payment, ?Admin $actor): void
    {
        RefundStatusHistory::create(['refund_id' => $refund->id, 'changed_by' => $actor?->id,
            'old_status' => $before['refund_status'] ?? null, 'new_status' => $refund->refund_status,
            'before' => $before, 'after' => $this->snapshot($refund, $order, $payment), 'created_at' => now()]);
    }

    private function afterCommit(Refund $refund, bool $notify): void
    {
        // Capture this transition, even when a later update runs before the callback.
        $snapshot = clone $refund;
        DB::afterCommit(function () use ($snapshot, $notify) {
            try {
                $order = Order::findOrFail($snapshot->order_id);
                $cache = app(OrderCacheService::class);
                if ($order->customer_id) {
                    $cache->forgetOrder($order->id, (int) $order->customer_id);
                    $cache->forgetOrdersList((int) $order->customer_id);
                }
                $cache->forgetCodOrders();
            } catch (\Throwable $exception) {
                Log::warning('Refund cache refresh failed.', ['refund_id' => $snapshot->id]);
            }
            if (! $notify) {
                return;
            }
            try {
                $snapshot->load('payment.order.customer', 'payment.method');
                $email = $snapshot->payment?->order?->customer?->email;
                if ($email && ! app(NotificationService::class)
                    ->trigger('Payments', 'RefundStatusUpdate', $email, $snapshot)) {
                    Log::warning('Refund status notification could not be sent.', ['refund_id' => $snapshot->id]);
                }
            } catch (\Throwable $exception) {
                Log::warning('Refund status notification failed.', ['refund_id' => $snapshot->id]);
            }
        });
    }
}
