<?php

namespace Modules\Shipping\DTO;

class ShippingResponse
{
    public function __construct(
        public readonly string $method,
        public readonly float $cost,
        public readonly string $currency,
        public readonly array $deliveryTime,
        public readonly bool $isAvailable,
        public readonly array $metadata = [],
        public readonly ?string $trackingNumber = null,
        public readonly ?array $configuration = null
    ) {}

    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'cost' => $this->cost,
            'currency' => $this->currency,
            'delivery_time' => $this->deliveryTime,
            'is_available' => $this->isAvailable,
            'metadata' => $this->metadata,
            'tracking_number' => $this->trackingNumber,
            'configuration' => $this->configuration,
        ];
    }
}