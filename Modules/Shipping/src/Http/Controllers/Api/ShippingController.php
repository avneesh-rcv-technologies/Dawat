<?php

namespace Modules\Shipping\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shipping\Services\ShippingService;
use Modules\Shipping\Http\Controllers\Controller;
use Modules\Shipping\Services\ShippingProcessorRegistry;

class ShippingController extends Controller
{
    protected ShippingService $shippingService;
    protected ShippingProcessorRegistry $registry;

    public function __construct(ShippingService $shippingService, ShippingProcessorRegistry $registry)
    {
        $this->shippingService = $shippingService;
        $this->registry = $registry;
    }

    /**
     * Calculate shipping options for given payload.
     */
    public function calculate(Request $request): JsonResponse
    {
        try {
            $payload = $request->validate([
                'order_total' => 'required|numeric|min:0',
                'currency' => 'string|size:3',
                'delivery_address' => 'array',
                'delivery_address.postcode' => 'string',
                'delivery_address.coordinates' => 'array',
                'delivery_address.coordinates.lat' => 'numeric',
                'delivery_address.coordinates.lng' => 'numeric',
                'items' => 'array',
                'weight' => 'numeric|min:0',
            ]);

            $shippingOptions = $this->shippingService->calculateShipping($payload);

            return response()->json([
                'success' => true,
                'data' => $shippingOptions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get available shipping methods.
     */
    public function methods(): JsonResponse
    {
        try {
            $methods = $this->shippingService->getAvailableMethods();

            return response()->json([
                'success' => true,
                'data' => $methods,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Process shipping for an order.
     */
    public function process(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'method' => 'required|string',
                'order_id' => 'required|integer',
                'order_total' => 'required|numeric|min:0',
                'currency' => 'string|size:3',
                'delivery_address' => 'array',
                'delivery_instructions' => 'string|nullable',
            ]);

            $result = $this->shippingService->processShipping($data['method'], $data);

            // Create shipping record
            $shipping = $this->shippingService->createShipping([
                'order_id' => $data['order_id'],
                'shipping_method_code' => $data['method'],
                'tracking_number' => $result['tracking_number'],
                'status' => $result['status'],
                'cost' => $result['delivery_fee'] ?? 0,
                'currency' => $data['currency'] ?? 'USD',
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_instructions' => $data['delivery_instructions'] ?? null,
                'estimated_delivery_at' => $result['estimated_delivery_at'] ?? $result['estimated_ready_at'] ?? null,
                'metadata' => $result,
            ]);

            return response()->json([
                'success' => true,
                'data' => $shipping,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Track shipping by tracking number.
     */
    public function track(string $trackingNumber): JsonResponse
    {
        try {
            $shipping = $this->shippingService->getShippingByTrackingNumber($trackingNumber);

            if (!$shipping) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipping not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $shipping,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update shipping status.
     */
    public function updateStatus(Request $request, int $shippingId): JsonResponse
    {
        try {
            $data = $request->validate([
                'status' => 'required|string',
            ]);

            $this->shippingService->updateShippingStatus($shippingId, $data['status']);

            return response()->json([
                'success' => true,
                'message' => 'Shipping status updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}