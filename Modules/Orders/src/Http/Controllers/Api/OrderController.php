<?php

namespace Modules\Orders\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\OrderService;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Fetch all orders with filters
     * GET /api/orders
     */
    public function index(Request $request)
    {
        try {
            $customer = auth('customer')->user();

            // If no authenticated customer, return error
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Start query - filter by authenticated customer only
            $query = Order::with(['vendor'])
                ->where('customer_type', \Modules\Acl\Models\Customer::class)
                ->where('customer_id', $customer->id);

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Filter by payment method (COD or Online)
            if ($request->has('payment_method') && !empty($request->payment_method)) {
                $query->where('payment_method', $request->payment_method);
            }

            // Filter by payment status
            if ($request->has('payment_status') && !empty($request->payment_status)) {
                $query->where('payment_status', $request->payment_status);
            }

            // Filter by order type
            if ($request->has('order_type') && !empty($request->order_type)) {
                $query->where('order_type', $request->order_type);
            }

            // Filter by vendor/restaurant
            if ($request->has('vendor_id') && !empty($request->vendor_id)) {
                $query->where('vendor_id', $request->vendor_id);
            }

            // Filter by date range
            if ($request->has('from_date') && !empty($request->from_date)) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->has('to_date') && !empty($request->to_date)) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            // Filter COD orders only
            if ($request->has('cod_only') && $request->cod_only) {
                $query->whereIn('payment_method', ['cash_on_delivery', 'cod', 'cash']);
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate
            $perPage = $request->get('per_page', 15);
            $orders = $query->paginate($perPage);

            // Return minimal data for list view
            $orders->getCollection()->transform(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'restaurant_name' => $order->vendor?->name,
                    'currency' => fn_format_default_currency(),
                    'status' => $order->status,
                    'formatted_status' => $order->formatted_status,
                    'order_type' => $order->order_type,
                    'formatted_order_type' => $order->formatted_order_type,
                    'total' => $order->total,
                    'payment_method' => $order->payment_method_name,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at,

                ];
            });

            return response()->json([
                'success' => true,
                'data' => $orders,
                'message' => 'Orders retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single order details
     */
    public function show($orderId)
    {
        try {
            $customer = auth('customer')->user();


            // Verify customer is authenticated
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Find the order
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Verify order belongs to authenticated customer
            if ($order->customer_id !== $customer->id || $order->customer_type !== \Modules\Acl\Models\Customer::class) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to order'
                ], 403);
            }

            $order->load(['items.item', 'vendor']);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'formatted_status' => $order->formatted_status,
                    'order_type' => $order->order_type,
                    'formatted_order_type' => $order->formatted_order_type,
                    'payment_method' => $order->payment_method_name,
                    'payment_status' => $order->payment_status,
                    'currency' => fn_format_default_currency(),
                    'is_cod' => $order->isCashOnDelivery(),
                    'restaurant' => [
                        'id' => $order->vendor?->id,
                        'name' => $order->vendor?->name,
                        'phone' => $order->vendor?->phone,
                    ],
                    'items' => $order->items->map(fn($item) => [
                        'id' => $item->id,
                        'name' => $item->item?->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ]),
                    'pricing' => [
                        'subtotal' => (float) $order->subtotal,
                        'tax' => (float) $order->tax,
                        'delivery_fee' => (float) $order->delivery_fee,
                        'service_charge' => (float) $order->service_charge,
                        'discount' => (float) $order->discount,
                        'total' => (float) $order->total,
                    ],
                    'delivery_address' => $order->delivery_address,
                    'estimated_delivery_time' => $order->estimated_delivery_time,
                    'actual_delivery_time' => $order->actual_delivery_time,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ],
                'message' => 'Order retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order status (Used by Admin UI)
     * This is an internal route called by the admin order details page
     */
    public function updateStatus(Request $request, Order $order)
    {
        try {
            $validStatuses = fn_get_order_statuses('O')->pluck('status_code')->toArray();
            $request->validate([
                'status' => 'required|string|in:' . implode(',', $validStatuses)
            ]);

            // Use OrderService to update status (handles notifications and events)
            $updatedOrder = $this->orderService->updateOrderStatus($order, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => [
                    'order_id' => $updatedOrder->id,
                    'order_number' => $updatedOrder->order_number,
                    'status' => $updatedOrder->status,
                    'formatted_status' => $updatedOrder->formatted_status,
                    'is_cod' => $updatedOrder->isCashOnDelivery(),
                    'payment_method' => $updatedOrder->payment_method,
                    'payment_status' => $updatedOrder->payment_status
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating order status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get COD orders only
     */
    public function getCodOrders(Request $request)
    {
        try {
            $query = Order::with(['customer', 'vendor', 'items', 'payments'])
                ->whereIn('payment_method', ['cash_on_delivery', 'cod', 'cash']);

            // Apply additional filters if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('vendor_id')) {
                $query->where('vendor_id', $request->vendor_id);
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $orders = $query->paginate($perPage);

            $orders->getCollection()->transform(function ($order) {
                $order->is_cod = true;
                $order->payment_method_type = 'COD';
                return $order;
            });

            return response()->json([
                'success' => true,
                'data' => $orders,
                'message' => 'COD orders fetched successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching COD orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if order can be cancelled
     * GET /api/orders/{orderId}/can-cancel
     */
    public function checkCancellation($orderId)
    {
        try {
            $customer = auth('customer')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $order = Order::with('vendor')->find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Verify order belongs to customer
            if ($order->customer_id !== $customer->id || $order->customer_type !== \Modules\Acl\Models\Customer::class) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to order'
                ], 403);
            }

            // Check cancellation eligibility
            $cancellationCheck = $this->orderService->checkCancellationEligibility($order);

            return response()->json([
                'success' => true,
                'data' => $cancellationCheck,
                'message' => $cancellationCheck['can_cancel'] ? 'Order can be cancelled' : 'Order cannot be cancelled'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking cancellation eligibility: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel order by customer
     * POST /api/orders/{orderId}/cancel
     */
    public function cancelOrder(Request $request, $orderId)
    {
        try {
            $customer = auth('customer')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $request->validate([
                'reason' => 'required|string|min:3|max:500'
            ]);

            $order = Order::with('vendor')->find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Verify order belongs to customer
            if ($order->customer_id !== $customer->id || $order->customer_type !== \Modules\Acl\Models\Customer::class) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to order'
                ], 403);
            }

            // Cancel the order with reason
            $cancelledOrder = $this->orderService->cancelOrder(
                $order,
                $request->input('reason')
            );

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => [
                    'order_id' => $cancelledOrder->id,
                    'order_number' => $cancelledOrder->order_number,
                    'status' => $cancelledOrder->status,
                    'formatted_status' => $cancelledOrder->formatted_status,
                    'cancellation_reason' => $cancelledOrder->cancellation_reason,
                    'cancelled_at' => $cancelledOrder->cancelled_at,
                    'refund_status' => $cancelledOrder->payment_status,
                    'total' => $cancelledOrder->total
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get restaurant cancellation policy
     * GET /api/restaurants/{restaurantId}/cancellation-policy
     */
    public function getRestaurantCancellationPolicy($restaurantId)
    {
        try {
            $restaurant = \Modules\Restaurant\Models\Restaurant::find($restaurantId);

            if (!$restaurant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found'
                ], 404);
            }

            $policy = $restaurant->getCancellationPolicy();

            return response()->json([
                'success' => true,
                'data' => $policy,
                'message' => 'Cancellation policy retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cancellation policy: ' . $e->getMessage()
            ], 500);
        }
    }
}
