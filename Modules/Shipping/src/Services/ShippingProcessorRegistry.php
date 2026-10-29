<?php

namespace Modules\Shipping\Services;

use InvalidArgumentException;
use Illuminate\Support\Facades\Schema;
use Modules\Shipping\Models\ShippingMethod;
use Modules\Shipping\Contracts\ShippingProcessor;

class ShippingProcessorRegistry
{
    /**
     * @var array<string, ShippingProcessor>
     */
    protected array $processors = [];

    /**
     * Register a processor and store it in the database.
     */
    public function register(string $code, ShippingProcessor $processor): void
    {   
        if(Schema::hasTable('shipping_methods')) {
            // Save to database if not already registered
            $shippingMethod = ShippingMethod::firstOrCreate(
                ['code' => $code],
                [
                    'class_name' => get_class($processor),
                    'name' => $processor->getName(),
                    'template' => $processor->getTemplate(),
                    'processor' => $processor->getProcessorTemplate(),
                    'is_active' => true,
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
    public function get(string $code): ShippingProcessor
    {
        if (! $this->has($code)) {
            throw new InvalidArgumentException("Shipping processor [{$code}] not registered.");
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

    /**
     * Get available shipping methods for given payload.
     */
    public function getAvailableMethods(array $payload): array
    {
        $availableMethods = [];
        
        foreach ($this->processors as $code => $processor) {
            if ($processor->isAvailable($payload)) {
                $response = $processor->calculate($payload);
                $availableMethods[$code] = $response->toArray();
            }
        }
        
        return $availableMethods;
    }
}