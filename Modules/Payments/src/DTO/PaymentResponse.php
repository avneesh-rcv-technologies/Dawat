<?php

namespace Modules\Payments\DTO;

use Modules\Payments\Enums\PaymentStatus;

class PaymentResponse
{
    public string $gateway;
    public string $gatewayPaymentId;
    public string $orderId;
    public float $amount;
    public string $currency;
    public string $status;
    public ?string $receiptUrl;
    public array $card;
    public array $billingAddress;
    public array $shippingAddress;
    public array $metadata;
    public array $configuration = [];
    public string $payment_method;
    public string $redirect_url;
    public ?string $client_secret; 

    public function __construct(array $data)
    {
        $this->gateway          = $data['gateway'];
        $this->gatewayPaymentId = $data['gateway_payment_id'];
        $this->orderId          = $data['order_id'];
        $this->amount           = $data['amount'];
        $this->currency         = $data['currency'];
        $this->status           = $data['status'];
        $this->receiptUrl       = $data['receipt_url'] ?? null;
        $this->card             = $data['card'] ?? [];
        $this->billingAddress   = $data['billing_address'] ?? [];
        $this->shippingAddress  = $data['shipping_address'] ?? [];
        $this->metadata         = $data['metadata'] ?? [];
        $this->configuration    = $data['configuration'] ?? [];
        $this->payment_method   = $data['payment_method'] ?? '';
        $this->redirect_url     = $data['redirect_url'] ?? '';
        $this->client_secret    = $data['client_secret'] ?? null;
    }

    public function isSuccessful(): bool
    {
        return in_array($this->getNormalizedStatus(), [PaymentStatus::COMPLETED, PaymentStatus::CAPTURED, PaymentStatus::PAID], true);
    }

    public function isFailure(): bool
    {
        return in_array($this->getNormalizedStatus(), [PaymentStatus::FAILED, PaymentStatus::CANCELLED], true);
    }

    public function getNormalizedStatus(): PaymentStatus
    {
        return match (strtolower($this->status)) {
            'authorized' => PaymentStatus::AUTHORIZED,
            'captured'   => PaymentStatus::CAPTURED,
            'completed', 'settled', 'paid', 'succeeded' => PaymentStatus::COMPLETED,
            'cancelled', 'canceled' => PaymentStatus::CANCELLED,
            'refunded'   => PaymentStatus::REFUNDED,
            'pending'    => PaymentStatus::PENDING,
            'unpaid'    => PaymentStatus::UNPAID,
            'processing'    => PaymentStatus::PROCESSING,
            'failed'    => PaymentStatus::FAILED,
            default      => PaymentStatus::FAILED,
        };
    }
}
