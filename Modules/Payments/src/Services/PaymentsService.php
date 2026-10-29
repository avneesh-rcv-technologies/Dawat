<?php

namespace Modules\Payments\Services;

use Modules\Payments\Models\PaymentConfiguration;

class PaymentsService
{
    public function getProcessor(string $code)
    {

        $paymentConfiguration = $this->resolveConfiguration($code);

        if (!$paymentConfiguration) {
            throw new \RuntimeException('Payment configuration not found for the selected payment method.');
        }

        $class = $paymentConfiguration->method?->class_name;
        if (!$class || !class_exists($class)) {
            throw new \RuntimeException('Payment processor is not configured.');
        }

        $config = $paymentConfiguration->config;
        return new $class(is_array($config) ? $config : (json_decode((string) $config, true) ?: []));
    }

    protected function resolveConfiguration(string $code): ?PaymentConfiguration
    {
        // Checkout and payment records store the configuration ID.
        return ctype_digit($code)
            ? PaymentConfiguration::with('method')->find($code)
            : PaymentConfiguration::with('method')->whereHas('method', fn ($query) => $query->where('code', $code))->first();
    }

    public function charge(string $code, $order, $request = null)
    {
        
        //  return $this->getProcessor($code)->charge($order, $request);

        // Convert Eloquent model to array if needed — StripeProcessor::charge() expects array
        $payload = ($order instanceof \Illuminate\Database\Eloquent\Model)
            ? $order->toArray()
            : (array) $order;
      
        $code=fn_get_payment_method_code($payload['payment_method']);
        return $this->getProcessor($code)->charge($payload);
    }

    public function refund(string $code, string $transactionId, float $amount): array
    {
        return $this->getProcessor($code)->refund($transactionId, $amount);
    }

    public function handleWebhook(string $code, array $payload)
    {
        return $this->getProcessor($code)->handleWebhook();
    }

    public function refresh(string $code, array $payload)
    {
        return $this->getProcessor($code)->refresh($payload);
    }

    public function subscribe(string $code, array $payload)
    {
        return $this->getProcessor($code)->subscribe($payload);
    }
}
