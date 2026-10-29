<?php

namespace Modules\Shipping\Services;

use Exception;
use Modules\Shipping\Models\Shipping;
use Modules\Shipping\Models\ShippingMethod;
use Modules\Shipping\Services\ShippingProcessorRegistry;
use Modules\Shipping\Models\ShippingConfiguration;

class ShippingService
{
    protected ShippingProcessorRegistry $registry;

    public function __construct(ShippingProcessorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Calculate shipping options for given payload.
     */
    public function calculateShipping(array $payload): array
    {
        try {
            return $this->registry->getAvailableMethods($payload);
        } catch (Exception $e) {
            throw new Exception("Failed to calculate shipping: " . $e->getMessage());
        }
    }

    /**
     * Process shipping for an order.
     */
    public function processShipping(string $method, array $payload): array
    {
        try {
            $processor = $this->registry->get($method);
            $configuration = $this->getMethodConfiguration($method);
            
            // Initialize processor with configuration
            $processor = new ($processor::class)($configuration);
            
            return $processor->processShipping($payload);
        } catch (Exception $e) {
            throw new Exception("Failed to process shipping: " . $e->getMessage());
        }
    }

    /**
     * Get shipping method configuration.
     */
    public function getMethodConfiguration(string $method): array
    {
        $shippingMethod = ShippingMethod::where('code', $method)->first();
        
        if (!$shippingMethod) {
            return [];
        }

        $configuration = ShippingConfiguration::where('shipping_method_id', $shippingMethod->id)->first();
        
        return $configuration ? $configuration->configuration : [];
    }

    /**
     * Update shipping method configuration.
     */
    public function updateMethodConfiguration(string $method, array $config): bool
    {
        try {
            $shippingMethod = ShippingMethod::where('code', $method)->first();
            
            if (!$shippingMethod) {
                throw new Exception("Shipping method not found: {$method}");
            }

            ShippingConfiguration::updateOrCreate(
                ['shipping_method_id' => $shippingMethod->id],
                ['configuration' => $config]
            );

            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to update configuration: " . $e->getMessage());
        }
    }

    /**
     * Get all available shipping methods.
     */
    public function getAvailableMethods(): array
    {
        return ShippingMethod::where('is_active', true)->get()->toArray();
    }

    /**
     * Create shipping record.
     */
    public function createShipping(array $data): Shipping
    {
        return Shipping::create($data);
    }

    /**
     * Update shipping status.
     */
    public function updateShippingStatus(int $shippingId, string $status): bool
    {
        $shipping = Shipping::find($shippingId);
        
        if (!$shipping) {
            throw new Exception("Shipping record not found");
        }

        $shipping->update(['status' => $status]);
        
        return true;
    }

    /**
     * Get shipping by tracking number.
     */
    public function getShippingByTrackingNumber(string $trackingNumber): ?Shipping
    {
        return Shipping::where('tracking_number', $trackingNumber)->first();
    }
}