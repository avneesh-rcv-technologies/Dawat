<?php

namespace Modules\Stripe\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Modules\Orders\Models\Order;
use Modules\Payments\Models\Payments;

class OrderPaymentLink
{
    public function url(int $orderId, string $reference): string
    {
        return URL::signedRoute('stripe.payment.show', ['order' => $orderId, 'reference' => $reference]);
    }

    public static function activeUrl($metadata, ?string $type, ?string $status, ?string $orderStatus, ?string $paymentStatus): ?string
    {
        if ($type !== 'payment' || $status !== 'pending'
            || in_array($orderStatus, [Order::STATUS_CANCELLED, Order::STATUS_FAILED, Order::STATUS_REFUNDED], true)
            || in_array($paymentStatus, ['paid', 'completed', 'captured', 'authorized', 'processing', 'refunded', 'partially_refunded', 'cancelled'], true)) {
            return null;
        }
        if (is_string($metadata)) $metadata = json_decode($metadata, true);
        $url = is_array($metadata) ? ($metadata['payment_url'] ?? null) : null;
        if (!is_string($url) || !filter_var($url, FILTER_VALIDATE_URL)
            || !in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)
            || !preg_match('#/stripe/order-payment/[0-9]+/pi_[a-zA-Z0-9]+$#', parse_url($url, PHP_URL_PATH) ?? '')) {
            return null;
        }
        return URL::hasValidSignature(Request::create($url)) ? $url : null;
    }

    public function canPay(Order $order, Payments $payment): bool
    {
        return !in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_FAILED, Order::STATUS_REFUNDED], true)
            && in_array($order->payment_status, ['pending', 'unpaid', 'failed'], true)
            && in_array($payment->status, ['pending', 'unpaid', 'failed'], true)
            && $payment->amount === $order->total
            && strtoupper($payment->currency) === strtoupper($order->currency);
    }

    public function matchesIntent(Order $order, Payments $payment, array $intent): bool
    {
        return ($intent['id'] ?? null) === $payment->gateway_reference
            && (string) data_get($intent, 'metadata.order_id') === (string) $order->id
            && strtoupper($intent['currency'] ?? '') === strtoupper($payment->currency)
            && (int) ($intent['amount'] ?? -1) === (int) round((float) $payment->amount * 100);
    }
}
