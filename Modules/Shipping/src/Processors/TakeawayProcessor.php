<?php

namespace Modules\Shipping\Processors;

use Exception;
use Modules\Shipping\DTO\ShippingResponse;
use Modules\Shipping\Contracts\ShippingProcessor;

class TakeawayProcessor implements ShippingProcessor
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
        return 'Takeaway';
    }

    public function getTemplate(): ?string
    {
        return 'shipping::settings.takeaway';
    }

    public function getProcessorTemplate(): ?string
    {
        return 'shipping::processors.takeaway';
    }

    public function calculate(array $payload): ShippingResponse
    {
        try {
            // Takeaway typically has no shipping cost
            $cost = $this->configuration['takeaway_fee'] ?? 0;
            
            return new ShippingResponse(
                method: 'takeaway',
                cost: $cost,
                currency: $payload['currency'] ?? 'USD',
                deliveryTime: $this->getDeliveryTime($payload),
                isAvailable: $this->isAvailable($payload),
                metadata: [
                    'pickup_location' => $this->configuration['pickup_location'] ?? '',
                    'pickup_instructions' => $this->configuration['pickup_instructions'] ?? '',
                    'contact_phone' => $this->configuration['contact_phone'] ?? '',
                ],
                configuration: $this->configuration
            );
        } catch (\Exception $e) {
            throw new Exception("Takeaway calculation failed: " . $e->getMessage());
        }
    }

    public function isAvailable(array $payload): bool
    {
        // Check if takeaway is enabled
        if (!($this->configuration['is_enabled'] ?? true)) {
            return false;
        }

        // Check operating hours if configured
        if (isset($this->configuration['operating_hours'])) {
            $currentTime = now()->format('H:i');
            $openTime = $this->configuration['operating_hours']['open'] ?? '00:00';
            $closeTime = $this->configuration['operating_hours']['close'] ?? '23:59';
            
            if ($currentTime < $openTime || $currentTime > $closeTime) {
                return false;
            }
        }

        return true;
    }

    public function getDeliveryTime(array $payload): array
    {
        $preparationTime = $this->configuration['preparation_time'] ?? 15; // minutes
        $bufferTime = $this->configuration['buffer_time'] ?? 5; // minutes
        
        $totalTime = $preparationTime + $bufferTime;
        
        return [
            'min_time' => $preparationTime,
            'max_time' => $totalTime,
            'unit' => 'minutes',
            'estimated_ready_at' => now()->addMinutes($totalTime)->toISOString(),
        ];
    }

    public function getConfigurationFields(): array
    {
        return [
            'is_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Takeaway',
                'default' => true,
            ],
            'takeaway_fee' => [
                'type' => 'number',
                'label' => 'Takeaway Fee',
                'default' => 0,
                'step' => 0.01,
            ],
            'preparation_time' => [
                'type' => 'number',
                'label' => 'Preparation Time (minutes)',
                'default' => 15,
            ],
            'buffer_time' => [
                'type' => 'number',
                'label' => 'Buffer Time (minutes)',
                'default' => 5,
            ],
            'pickup_location' => [
                'type' => 'text',
                'label' => 'Pickup Location Address',
                'required' => true,
            ],
            'pickup_instructions' => [
                'type' => 'textarea',
                'label' => 'Pickup Instructions',
            ],
            'contact_phone' => [
                'type' => 'text',
                'label' => 'Contact Phone Number',
            ],
            'operating_hours' => [
                'type' => 'group',
                'label' => 'Operating Hours',
                'fields' => [
                    'open' => [
                        'type' => 'time',
                        'label' => 'Opening Time',
                        'default' => '09:00',
                    ],
                    'close' => [
                        'type' => 'time',
                        'label' => 'Closing Time',
                        'default' => '22:00',
                    ],
                ],
            ],
        ];
    }

    public function processShipping(array $payload): array
    {
        return [
            'status' => 'ready_for_pickup',
            'tracking_number' => 'PICKUP-' . strtoupper(uniqid()),
            'pickup_location' => $this->configuration['pickup_location'] ?? '',
            'estimated_ready_at' => now()->addMinutes($this->configuration['preparation_time'] ?? 15)->toISOString(),
            'contact_phone' => $this->configuration['contact_phone'] ?? '',
        ];
    }
}