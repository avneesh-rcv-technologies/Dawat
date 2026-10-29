<?php

namespace Modules\Orders\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\OrderTrackingService;

class OrderTrackingController extends Controller
{
    public function __construct(
        protected OrderTrackingService $trackingService
    ) {}

    public function trackOrder(Request $request): JsonResponse
    {
        try {
            $request->validate(['order_number' => 'required|string']);

            $customer = auth('customer')->user();
            
            // If no authenticated customer, return error
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $orderNumber = $request->query('order_number');
            
            // Find order and verify it belongs to authenticated customer
            $order = Order::where('order_number', $orderNumber)
                ->where('customer_type', \Modules\Acl\Models\Customer::class)
                ->where('customer_id', $customer->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $data = $this->trackingService->getOrderTracking($order);

            return response()->json([
                'success' => true,
                'message' => 'Order tracked successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
