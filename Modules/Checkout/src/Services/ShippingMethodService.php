<?php

namespace Modules\Checkout\Services;

use Exception;
use Modules\Shipping\Services\ShippingProcessorRegistry;

class ShippingMethodService
{
    protected $shippingProcessorRegistry;

    public function __construct(ShippingProcessorRegistry $shippingProcessorRegistry)
    {
        $this->shippingProcessorRegistry = $shippingProcessorRegistry;
    }

    /**
     * Get available shipping methods using processors
     *
     * @param array $payload
     * @return array
     */
    public function getShippingMethods(array $payload = []): array
    {
        try {
            // Get available methods from shipping processor registry
            $availableMethods = $this->shippingProcessorRegistry->getAvailableMethods($payload);
            // dd($availableMethods);
            
            // Transform processor responses to checkout API format
            $methods = [];
            foreach ($availableMethods as $code => $methodData) {
                $methods[] = [
                    'id' => $code,
                    'name' => $methodData['method'] ?? ucfirst($code),
                    'description' => $this->getMethodDescription($code),
                    'cost' => $methodData['cost'] ?? 0.00,
                    'estimated_time' => $this->formatDeliveryTime($methodData['deliveryTime'] ?? []),
                    'icon' => $code,
                    'currency' => $methodData['currency'] ?? 'USD',
                    'is_available' => $methodData['isAvailable'] ?? true,
                    'metadata' => $methodData['metadata'] ?? []
                ];
            }
            
            return $methods;
        } catch (Exception $e) {
            // Fallback to basic methods if processor registry fails
            return $this->getFallbackMethods();
        }
    }

    /**
     * Get method description based on code
     */
    private function getMethodDescription(string $code): string
    {
        $descriptions = [
            'takeaway' => 'Pick up your order from the restaurant',
            'delivery' => 'Get your order delivered to your address',
        ];

        return $descriptions[$code] ?? 'Shipping method';
    }

    /**
     * Format delivery time array to string
     */
    private function formatDeliveryTime(array $deliveryTime): string
    {
        if (empty($deliveryTime)) {
            return 'Contact restaurant';
        }

        $min = $deliveryTime['min'] ?? 0;
        $max = $deliveryTime['max'] ?? 0;
        $unit = $deliveryTime['unit'] ?? 'minutes';

        if ($min && $max) {
            return "{$min}-{$max} {$unit}";
        } elseif ($min) {
            return "{$min} {$unit}";
        }

        return 'Contact restaurant';
    }

    /**
     * Fallback methods if processor registry fails
     */
    private function getFallbackMethods(): array
    {
        return [
            [
                'id' => 'takeaway',
                'name' => 'Takeaway',
                'description' => 'Pick up your order from the restaurant',
                'cost' => 0.00,
                'estimated_time' => '15-20 minutes',
                'icon' => 'takeaway'
            ],
            [
                'id' => 'delivery',
                'name' => 'Home Delivery',
                'description' => 'Get your order delivered to your address',
                'cost' => 5.00,
                'estimated_time' => '30-45 minutes',
                'icon' => 'delivery'
            ]
        ];
    }

    /**
     * Validate shipping method using processor registry
     *
     * @param string $method
     * @param array $payload
     * @return bool
     * @throws Exception
     */
    public function validateShippingMethod(string $method, array $payload = []): bool
    {
        try {
            // Check if processor exists and is available
            if (!$this->shippingProcessorRegistry->has($method)) {
                throw new Exception('Invalid shipping method');
            }

            $processor = $this->shippingProcessorRegistry->get($method);
            return $processor->isAvailable($payload);
            
        } catch (Exception $e) {
            // Fallback validation for basic methods
            $validMethods = ['takeaway', 'delivery'];
            
            if (!in_array($method, $validMethods)) {
                throw new Exception('Invalid shipping method');
            }

            return true;
        }
    }
}