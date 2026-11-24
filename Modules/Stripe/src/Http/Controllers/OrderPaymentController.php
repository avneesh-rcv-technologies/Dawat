<?php

namespace Modules\Stripe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Transaction;
use Modules\Payments\Models\Payments;
use Modules\Payments\Services\PaymentsService;
use Modules\Stripe\Processors\StripeProcessor;
use Modules\Stripe\Services\OrderPaymentLink;

class OrderPaymentController extends Controller
{
    public function show(Request $request, Order $order, string $reference, PaymentsService $processors, OrderPaymentLink $links)
    {
        $payment = $this->findPayment($order, $reference);
        abort_unless($payment->method?->method?->code === 'stripe', 404);

        $state = 'unavailable';
        $clientSecret = $publicKey = null;
        $returnUrl = $links->url($order->id, $reference);
        if (in_array($order->payment_status, ['paid', 'completed', 'captured'], true)) {
            $state = 'paid';
        } elseif ($links->canPay($order, $payment) && $payment->method?->is_active) {
            try {
                $processor = $processors->getProcessor((string) $payment->payment_method_id);
                if (!$processor instanceof StripeProcessor) throw new \RuntimeException('Invalid payment processor.');
                $intent = $processor->retrieveOrderPaymentIntent($reference);
                if (!$links->matchesIntent($order, $payment, $intent)) {
                    throw new \RuntimeException('Payment does not match the order.');
                }
                if (($intent['status'] ?? '') === 'succeeded') {
                    $this->completePayment($order, $payment, $intent, $links);
                    $state = 'paid';
                } elseif (in_array($intent['status'] ?? '', ['processing', 'requires_capture'], true)) {
                    $state = 'processing';
                } elseif (in_array($intent['status'] ?? '', ['requires_payment_method', 'requires_confirmation', 'requires_action'], true)) {
                    $publicKey = $processor->publicKey();
                    $clientSecret = $intent['client_secret'] ?? null;
                    $state = $publicKey && $clientSecret ? 'pay' : 'unavailable';
                }
            } catch (\Throwable $e) {
                // Never log the intent, signed URL, configuration, or client secret.
                Log::warning('Unable to resume order payment.', ['order_id' => $order->id, 'exception_class' => get_class($e)]);
                $state = 'unavailable';
                $clientSecret = null;
            }
        } elseif (in_array($order->payment_status, ['processing', 'authorized'], true)) {
            $state = 'processing';
        }

        return response()->view('stripe::order-payment', compact('order', 'payment', 'state', 'clientSecret', 'publicKey', 'returnUrl'))
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    protected function findPayment(Order $order, string $reference): Payments
    {
        return Payments::with('method.method')->where('order_id', $order->id)
            ->where('gateway_reference', $reference)->firstOrFail();
    }

    private function completePayment(Order $order, Payments $payment, array $intent, OrderPaymentLink $links): void
    {
        DB::transaction(function () use ($order, $payment, $intent, $links) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $lockedPayment = Payments::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if (in_array($lockedOrder->payment_status, ['paid', 'completed', 'captured'], true)) return;
            if (!$links->canPay($lockedOrder, $lockedPayment) || !$links->matchesIntent($lockedOrder, $lockedPayment, $intent)
                || (int) ($intent['amount_received'] ?? 0) !== (int) $intent['amount']) {
                throw new \DomainException('Order is no longer eligible for this payment.');
            }
            $updates = ['payment_status' => 'paid'];
            if ($lockedOrder->status === Order::STATUS_PENDING) $updates['status'] = Order::STATUS_NEW;
            $lockedOrder->update($updates);
            $lockedPayment->update(['status' => 'completed', 'processed_at' => now(),
                'details' => array_replace($lockedPayment->details ?? [], ['status' => 'completed'])]);
            Transaction::where('order_id', $lockedOrder->id)->where('payment_id', $lockedPayment->id)
                ->where('gateway_reference', $lockedPayment->gateway_reference)->where('type', 'payment')
                ->where('status', 'pending')->update(['status' => 'completed', 'processed_at' => now(), 'updated_at' => now()]);
        });
    }
}
