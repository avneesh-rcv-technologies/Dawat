<?php

namespace Modules\Shipping\Listeners;

use Modules\Shipping\Events\ShippingStatusUpdated;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncWithExternalTracking
{
    /**
     * Handle the event.
     */
    public function handle(ShippingStatusUpdated $event): void
    {
        $shipping = $event->shipping;
        
        // Only sync for delivery orders that have external tracking
        if ($shipping->shipping_method_code !== 'delivery') {
            return;
        }

        // Check if external tracking is enabled
        if (!config('shipping.external_tracking.enabled', false)) {
            return;
        }

        try {
            $this->syncWithTrackingService($shipping, $event->previousStatus);
        } catch (\Exception $e) {
            Log::error('Failed to sync with external tracking service', [
                'shipping_id' => $shipping->id,
                'tracking_number' => $shipping->tracking_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sync shipping status with external tracking service.
     */
    protected function syncWithTrackingService(object $shipping, string $previousStatus): void
    {
        $trackingApiUrl = config('shipping.external_tracking.api_url');
        $apiKey = config('shipping.external_tracking.api_key');

        if (!$trackingApiUrl || !$apiKey) {
            return;
        }

        $payload = [
            'tracking_number' => $shipping->tracking_number,
            'status' => $shipping->status->value,
            'previous_status' => $previousStatus,
            'order_id' => $shipping->order_id,
            'updated_at' => $shipping->updated_at->toISOString(),
            'metadata' => [
                'delivery_address' => $shipping->delivery_address,
                'estimated_delivery' => $shipping->estimated_delivery_at?->toISOString(),
                'actual_delivery' => $shipping->actual_delivery_at?->toISOString(),
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($trackingApiUrl . '/tracking/update', $payload);

        if ($response->successful()) {
            Log::info('Successfully synced with external tracking service', [
                'shipping_id' => $shipping->id,
                'tracking_number' => $shipping->tracking_number,
                'status' => $shipping->status->value,
            ]);
        } else {
            Log::warning('Failed to sync with external tracking service', [
                'shipping_id' => $shipping->id,
                'tracking_number' => $shipping->tracking_number,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);
        }
    }

    /**
     * Send delivery confirmation to external service.
     */
    protected function sendDeliveryConfirmation(object $shipping): void
    {
        if ($shipping->status->value !== 'delivered') {
            return;
        }

        $webhookUrl = config('shipping.external_tracking.webhook_url');
        
        if (!$webhookUrl) {
            return;
        }

        $payload = [
            'event' => 'delivery_completed',
            'tracking_number' => $shipping->tracking_number,
            'order_id' => $shipping->order_id,
            'delivered_at' => $shipping->actual_delivery_at?->toISOString(),
            'delivery_address' => $shipping->delivery_address,
        ];

        Http::timeout(15)->post($webhookUrl, $payload);
    }
}