<?php

namespace Modules\Shipping\Contracts;

use Modules\Shipping\DTO\ShippingResponse;

interface ShippingProcessor
{
    public function getName(): string;

    public function getTemplate(): ?string;

    public function getProcessorTemplate(): ?string;

    /**
     * Calculate shipping cost and delivery time.
     */
    public function calculate(array $payload): ShippingResponse;

    /**
     * Validate shipping availability.
     */
    public function isAvailable(array $payload): bool;

    /**
     * Get delivery time estimate.
     */
    public function getDeliveryTime(array $payload): array;

    /**
     * Get shipping configuration fields.
     */
    public function getConfigurationFields(): array;

    /**
     * Process shipping order.
     */
    public function processShipping(array $payload): array;
}