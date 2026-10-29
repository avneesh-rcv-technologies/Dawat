<?php

namespace Modules\Shipping\Processors;

use Exception;
use Modules\Shipping\DTO\ShippingResponse;
use Modules\Shipping\Contracts\ShippingProcessor;

class DeliveryProcessor implements ShippingProcessor
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
        return 'Delivery';
    }

    public function getTemplate(): ?string
    {
        return 'shipping::settings.delivery';
    }

    public function getProcessorTemplate(): ?string
    {
        return 'shipping::processors.delivery';
    }

    public function calculate(array $payload): ShippingResponse
    {
        try {
            $cost = $this->calculateDeliveryCost($payload);
            
            return new ShippingResponse(
                method: 'delivery',
                cost: $cost,
                currency: $payload['currency'] ?? 'USD',
                deliveryTime: $this->getDeliveryTime($payload),
                isAvailable: $this->isAvailable($payload),
                metadata: [
                    'delivery_radius' => $this->configuration['delivery_radius'] ?? 0,
                    'minimum_order' => $this->configuration['minimum_order'] ?? 0,
                    'free_delivery_threshold' => $this->configuration['free_delivery_threshold'] ?? null,
                    'delivery_zones' => $this->configuration['delivery_zones'] ?? [],
                ],
                configuration: $this->configuration
            );
        } catch (\Exception $e) {
            throw new Exception("Delivery calculation failed: " . $e->getMessage());
        }
    }

    public function isAvailable(array $payload): bool
    {
        // Check if delivery is enabled
        if (!($this->configuration['is_enabled'] ?? true)) {
            return false;
        }

        // Check minimum order amount
        $minimumOrder = $this->configuration['minimum_order'] ?? 0;
        if (($payload['order_total'] ?? 0) < $minimumOrder) {
            return false;
        }

        // Check delivery radius/zone
        if (!$this->isWithinDeliveryArea($payload)) {
            return false;
        }

        // Check operating hours
        if (isset($this->configuration['delivery_hours'])) {
            $currentTime = now()->format('H:i');
            $openTime = $this->configuration['delivery_hours']['start'] ?? '00:00';
            $closeTime = $this->configuration['delivery_hours']['end'] ?? '23:59';
            
            if ($currentTime < $openTime || $currentTime > $closeTime) {
                return false;
            }
        }

        return true;
    }

    public function getDeliveryTime(array $payload): array
    {
        $preparationTime = $this->configuration['preparation_time'] ?? 20; // minutes
        $deliveryTime = $this->calculateDeliveryTime($payload);
        $bufferTime = $this->configuration['buffer_time'] ?? 10; // minutes
        
        $totalTime = $preparationTime + $deliveryTime + $bufferTime;
        
        return [
            'preparation_time' => $preparationTime,
            'delivery_time' => $deliveryTime,
            'buffer_time' => $bufferTime,
            'total_time' => $totalTime,
            'unit' => 'minutes',
            'estimated_delivery_at' => now()->addMinutes($totalTime)->toISOString(),
        ];
    }

    public function getConfigurationFields(): array
    {
        return [
            'is_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Delivery',
                'default' => true,
            ],
            'base_delivery_fee' => [
                'type' => 'number',
                'label' => 'Base Delivery Fee',
                'default' => 5.00,
                'step' => 0.01,
            ],
            'minimum_order' => [
                'type' => 'number',
                'label' => 'Minimum Order Amount',
                'default' => 10.00,
                'step' => 0.01,
            ],
            'free_delivery_threshold' => [
                'type' => 'number',
                'label' => 'Free Delivery Threshold (optional)',
                'step' => 0.01,
            ],
            'delivery_radius' => [
                'type' => 'number',
                'label' => 'Delivery Radius (km)',
                'default' => 10,
            ],
            'preparation_time' => [
                'type' => 'number',
                'label' => 'Preparation Time (minutes)',
                'default' => 20,
            ],
            'buffer_time' => [
                'type' => 'number',
                'label' => 'Buffer Time (minutes)',
                'default' => 10,
            ],
            'per_km_rate' => [
                'type' => 'number',
                'label' => 'Rate per KM',
                'default' => 1.00,
                'step' => 0.01,
            ],
            'delivery_hours' => [
                'type' => 'group',
                'label' => 'Delivery Hours',
                'fields' => [
                    'start' => [
                        'type' => 'time',
                        'label' => 'Start Time',
                        'default' => '10:00',
                    ],
                    'end' => [
                        'type' => 'time',
                        'label' => 'End Time',
                        'default' => '23:00',
                    ],
                ],
            ],
            'delivery_zones' => [
                'type' => 'array',
                'label' => 'Delivery Zones',
                'fields' => [
                    'name' => [
                        'type' => 'text',
                        'label' => 'Zone Name',
                    ],
                    'postcodes' => [
                        'type' => 'text',
                        'label' => 'Postcodes (comma separated)',
                    ],
                    'delivery_fee' => [
                        'type' => 'number',
                        'label' => 'Delivery Fee',
                        'step' => 0.01,
                    ],
                    'delivery_time' => [
                        'type' => 'number',
                        'label' => 'Delivery Time (minutes)',
                    ],
                ],
            ],
        ];
    }

    public function processShipping(array $payload): array
    {
        $deliveryTime = $this->getDeliveryTime($payload);
        
        return [
            'status' => 'processing',
            'tracking_number' => 'DEL-' . strtoupper(uniqid()),
            'estimated_delivery_at' => $deliveryTime['estimated_delivery_at'],
            'delivery_address' => $payload['delivery_address'] ?? [],
            'delivery_instructions' => $payload['delivery_instructions'] ?? '',
            'delivery_fee' => $this->calculateDeliveryCost($payload),
        ];
    }

    protected function calculateDeliveryCost(array $payload): float
    {
        $orderTotal = $payload['order_total'] ?? 0;
        
        // Check for free delivery threshold
        $freeDeliveryThreshold = $this->configuration['free_delivery_threshold'] ?? null;
        if ($freeDeliveryThreshold && $orderTotal >= $freeDeliveryThreshold) {
            return 0;
        }

        // Check delivery zones first
        if ($zone = $this->getDeliveryZone($payload)) {
            return $zone['delivery_fee'] ?? $this->configuration['base_delivery_fee'] ?? 5.00;
        }

        // Calculate based on distance
        $distance = $this->calculateDistance($payload);
        $baseFee = $this->configuration['base_delivery_fee'] ?? 5.00;
        $perKmRate = $this->configuration['per_km_rate'] ?? 1.00;
        
        return $baseFee + ($distance * $perKmRate);
    }

    protected function calculateDeliveryTime(array $payload): int
    {
        // Check delivery zones first
        if ($zone = $this->getDeliveryZone($payload)) {
            return $zone['delivery_time'] ?? 30;
        }

        // Calculate based on distance (assume 30 km/h average speed)
        $distance = $this->calculateDistance($payload);
        return max(15, (int)($distance * 2)); // minimum 15 minutes
    }

    protected function isWithinDeliveryArea(array $payload): bool
    {
        // Check delivery zones
        if ($this->getDeliveryZone($payload)) {
            return true;
        }

        // Check delivery radius
        $distance = $this->calculateDistance($payload);
        $maxRadius = $this->configuration['delivery_radius'] ?? 10;
        
        return $distance <= $maxRadius;
    }

    protected function getDeliveryZone(array $payload): ?array
    {
        $deliveryZones = $this->configuration['delivery_zones'] ?? [];
        $customerPostcode = $payload['delivery_address']['postcode'] ?? '';
        
        foreach ($deliveryZones as $zone) {
            $postcodes = array_map('trim', explode(',', $zone['postcodes'] ?? ''));
            if (in_array($customerPostcode, $postcodes)) {
                return $zone;
            }
        }
        
        return null;
    }

    protected function calculateDistance(array $payload): float
    {
        // This is a simplified distance calculation
        // In a real application, you would use Google Maps API or similar
        // For now, return a mock distance based on postcode or coordinates
        
        if (isset($payload['delivery_address']['coordinates'])) {
            // Calculate using coordinates if available
            $customerLat = $payload['delivery_address']['coordinates']['lat'];
            $customerLng = $payload['delivery_address']['coordinates']['lng'];
            $storeLat = $this->configuration['store_coordinates']['lat'] ?? 0;
            $storeLng = $this->configuration['store_coordinates']['lng'] ?? 0;
            
            return $this->haversineDistance($storeLat, $storeLng, $customerLat, $customerLng);
        }
        
        // Fallback: estimate based on postcode or return default
        return 5.0; // Default 5km
    }

    protected function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
}