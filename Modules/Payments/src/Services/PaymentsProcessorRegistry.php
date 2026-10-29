<?php

namespace Modules\Payments\Services;

use InvalidArgumentException;
use Modules\Payments\Models\Payment;
use Illuminate\Support\Facades\Schema;
use Modules\Payments\Models\PaymentMethod;
use Modules\Payments\Contracts\PaymentProcessor;

class PaymentsProcessorRegistry
{
    /**
     * @var array<string, PaymentProcessor>
     */
    protected array $processors = [];

    /**
     * Register a processor and store it in the database.
     */
    public function register(string $code, $processor): void
    {   
        if(Schema::hasTable('payment_methods')) {
            // Save to database if not already registered
            $paymentMethod = PaymentMethod::firstOrCreate(
                ['code' => $code],
                [
                    'class_name' => get_class($processor),
                    'name' => $processor->getName(), // Assuming the processor has a getName() method
                    'template' => $processor->getTemplate(), // Assuming the processor has a getTemplate() method
                    'processor' => $processor->getProcessorTemplate(), // Assuming the processor has a getTemplate() method
                    'is_active' => true, // default active status
                ]
            );
        }

        // Store in memory for easy access
        $this->processors[$code] = $processor;
    }

    /**
     * Check if a processor is registered.
     */
    public function has(string $code): bool
    {
        return isset($this->processors[$code]);
    }

    /**
     * Get a processor by code.
     */
    public function get(string $code): PaymentProcessor
    {
        if (! $this->has($code)) {
            throw new InvalidArgumentException("Payment processor [{$code}] not registered.");
        }

        return $this->processors[$code];
    }

    /**
     * Get all registered processors.
     */
    public function all(): array
    {
        return $this->processors;
    }
}
