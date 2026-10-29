<?php

namespace Modules\Payments\Contracts;

use Core\Request\Request;
use Modules\Payments\DTO\PaymentResponse;

interface PaymentProcessor
{
    public function getName(): string;

    public function getTemplate(): ?string;

    public function getProcessorTemplate(): ?string;

    /**
     * Charge a payment.
     */
    public function charge(array $payload): PaymentResponse;

    /**
     * Refund a payment.
     */
    public function refund(string $transactionId, float $amount): array;

    /**
     * Handle gateway webhook.
     */
    public function handleWebhook();

    public function refresh(array $payload);

    // public function cancel();

    /**
     * Create/Manage subscription.
     */
    public function subscribe(array $payload): array;
    

   
}
