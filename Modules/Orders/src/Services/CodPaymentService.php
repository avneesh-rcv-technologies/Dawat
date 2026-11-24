<?php

namespace Modules\Orders\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Transaction;
use Modules\Payments\Models\PaymentConfiguration;
use Modules\Payments\Models\Payments;
use Modules\Payments\Processors\CodProcessor;

class CodPaymentService
{
    public function configuration($paymentMethod): ?PaymentConfiguration
    {
        if (!$paymentMethod) return null;
        return is_numeric($paymentMethod)
            ? PaymentConfiguration::with('method')->find($paymentMethod)
            : PaymentConfiguration::with('method')->whereHas('method', fn ($query) => $query->where('code', $paymentMethod))->first();
    }

    public function isCodConfiguration(?PaymentConfiguration $configuration): bool
    {
        $method = $configuration?->method;
        return $method && (in_array(strtolower($method->code ?? ''), ['cod', 'cash', 'cash_on_delivery'], true)
            || ltrim($method->class_name ?? '', '\\') === CodProcessor::class);
    }

    public function isCodPaymentMethod($paymentMethod): bool
    {
        return $this->isCodConfiguration($this->configuration($paymentMethod));
    }

    public function assertCollectable(Order $order, ?PaymentConfiguration $configuration): void
    {
        if (!$this->isCodConfiguration($configuration)) {
            throw ValidationException::withMessages(['payment_status' => 'Manual payment updates are available only for cash-on-delivery orders.']);
        }
        if (in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_FAILED, Order::STATUS_REFUNDED], true)) {
            throw ValidationException::withMessages(['payment_status' => 'Cash cannot be collected for a cancelled, failed, or refunded order.']);
        }
        if (!in_array($order->payment_status, ['pending', 'unpaid', 'paid'], true)) {
            throw ValidationException::withMessages(['payment_status' => 'This payment status cannot be updated as a cash collection.']);
        }
    }

    public function collect(Order $order, Admin $actor, ?string $notes = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $notes) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $configuration = $this->configuration($order->payment_method);
            $this->assertCollectable($order, $configuration);
            // Serialize repeated clicks and preserve the original collection audit information.
            if ($order->payment_status === 'paid') return $order;

            $payments = Payments::with('method.method')->where('order_id', $order->id)->lockForUpdate()->get();
            foreach ($payments as $existing) {
                if (!$this->isCodConfiguration($existing->method)
                    || !in_array($existing->status, ['pending', 'failed', 'unpaid'], true)
                    || preg_match('/^(pi_|ch_|cs_)/', $existing->gateway_reference ?? '')) {
                    throw ValidationException::withMessages(['payment_status' => 'This order has another payment record. Review its transactions before recording cash.']);
                }
            }
            if ($payments->count() > 1) {
                throw ValidationException::withMessages(['payment_status' => 'This order has multiple payment records. Review them before recording cash.']);
            }
            $payment = $payments->first() ?? new Payments();
            $collectedAt = now();
            $reference = $payment->gateway_reference ?: 'COD-ORDER-'.$order->id;
            $audit = ['collection_type' => 'cash', 'collected_by' => $actor->id,
                'collected_by_name' => $actor->name, 'collected_by_email' => $actor->email,
                'collected_at' => $collectedAt->toIso8601String(), 'collection_notes' => $notes];
            $payment->fill([
                'order_id' => $order->id, 'vendor_type' => $order->vendor_type, 'vendor_id' => $order->vendor_id,
                'payment_method_id' => $configuration->id, 'payment_type' => 'order_payment',
                'amount' => $order->total, 'currency' => $order->currency, 'status' => 'completed',
                'gateway_reference' => $reference, 'processed_at' => $collectedAt,
                'details' => array_replace($payment->details ?? [], $audit, ['status' => 'completed']),
            ])->save();

            $pending = Transaction::where('order_id', $order->id)->where('payment_id', $payment->id)
                ->where('type', 'payment')->where('status', 'pending')->lockForUpdate()->get();
            if ($pending->count() > 1) {
                throw ValidationException::withMessages(['payment_status' => 'Multiple pending transactions need review before recording cash.']);
            }
            $transaction = $pending->first() ?? new Transaction([
                'transaction_number' => 'TXN-'.$collectedAt->format('Ymd').'-'.Str::upper(Str::random(12)),
            ]);
            $transaction->fill([
                'order_id' => $order->id, 'payment_id' => $payment->id, 'type' => 'payment',
                'amount' => $order->total, 'currency' => $order->currency, 'status' => 'completed',
                'gateway_reference' => $reference, 'processed_at' => $collectedAt,
                'metadata' => array_replace($transaction->metadata ?? [], $audit),
                'notes' => trim(($transaction->notes ? $transaction->notes."\n" : '').'Cash collected manually by '.trim($actor->name.' ('.$actor->email.')').'.'.($notes ? ' '.$notes : '')),
            ])->forceFill(['balance' => 0, 'processed_by' => $actor->id])->save();
            $payment->update(['transaction_id' => $transaction->id]);
            $order->update(['payment_status' => 'paid']);
            DB::afterCommit(function () use ($payment) {
                try {
                    $receipt = Payments::with(['order.customer', 'method.method', 'transactions'])->find($payment->id);
                    $customer = $receipt?->order?->customer;
                    if (!$receipt || $receipt->status !== 'completed' || !$customer?->email) return;
                    $sent = app(\Modules\Notifications\Services\NotificationService::class)
                        ->trigger('Payments', 'CodPaymentReceived', $customer->email, $receipt);
                    if (!$sent) {
                        \Illuminate\Support\Facades\Log::warning('COD payment receipt notification could not be sent.', ['payment_id' => $payment->id]);
                    }
                } catch (\Throwable $exception) {
                    // A channel outage must not undo a recorded cash collection.
                    \Illuminate\Support\Facades\Log::warning('COD payment receipt notification failed.', ['payment_id' => $payment->id, 'exception_class' => get_class($exception)]);
                }
            });
            DB::afterCommit(function () use ($order) {
                try {
                    $cache = app(OrderCacheService::class);
                    if ($order->customer_id) {
                        $cache->forgetOrder($order->id, (int) $order->customer_id);
                        $cache->forgetOrdersList((int) $order->customer_id);
                    }
                    $cache->forgetCodOrders();
                } catch (\Throwable $exception) {
                    \Illuminate\Support\Facades\Log::warning('COD payment cache refresh failed.', ['order_id' => $order->id]);
                }
            });

            return $order->refresh();
        });
    }
}
