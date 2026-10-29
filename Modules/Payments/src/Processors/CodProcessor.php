<?php

namespace Modules\Payments\Processors;

use Exception;
use Modules\Payments\DTO\PaymentResponse;
use Modules\Payments\Contracts\PaymentProcessor;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class CodProcessor implements PaymentProcessor
{

    protected $configuration;

    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->configuration = $config;
        }
    }

    public function getName(): string
    {
        return 'Cod';
    }

    public function getTemplate(): ?string
    {
        return 'payments::settings.cod';
    }

    public function getProcessorTemplate(): ?string
    {
        return 'payments::processors.cod';
    }

    public function refresh(array $payload)
    {
        return [];
    }

    public function charge(array $payload, $request = null): PaymentResponse
    {
        try {

            // Map into your standard DTO
            return new PaymentResponse([
                'gateway'               => 'cod',
                'gateway_payment_id'    => '',
                'status'                => 'pending',
                'amount'                => $payload['total'] ?? 0,
                'currency'              => $payload['currency'] ?? null,
                'order_id'              => $payload['id'],
                'receipt_url'           => null,
                'card'                  => [],
                'billing_address'       => $payload['billing_address'] ?? [],
                'shipping_address'      => $payload['billing_address'] ?? [],
                'metadata'              => [],
                'configuration'         => $this->configuration,
                'payment_method'        => $request['payment'] ?? null,
                'client_secret'      => "",
            ]);
        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            throw new Exception("Payment processing failed: ");
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Invalid refund amount.');
        }

        return ['status' => 'requires_action', 'manual' => true];
    }

    public function handleWebhook() {}

    public function subscribe(array $payload): array
    {
        // implement subscription handling
        return ['status' => 'subscription_created'];
    }
}
