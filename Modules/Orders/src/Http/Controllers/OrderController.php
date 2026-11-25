<?php

namespace Modules\Orders\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Status;
use Modules\Admin\Models\Country;
use Illuminate\Routing\Controller;
use Modules\Customers\Models\User;
use Modules\Catalog\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Orders\DataView\OrderGrid;
use Illuminate\Support\Facades\Session;
use Modules\Filemanager\Models\FileLink;
use Modules\Orders\Services\OrderService;
use Modules\Orders\Services\OrderCommissionAccess;
use Modules\Alacarte\Models\GroupBenefitMap;
use Modules\Orders\Models\AttachFileMapping;
use Modules\Orders\Http\Requests\OrderRequest;
use Modules\Payments\Models\PaymentConfiguration;
use Carbon\Carbon;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantOwnerRoleResolver;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Rating\Models\Rating;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Session::forget('cart');
        $lists = fn_datagrid(OrderGrid::class)->process();

        // Get date range from request or use defaults
        $dateFrom = $request->input('date_from', date('Y-m-d', strtotime('-30 days')));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        // Get analytics data
        $analyticsController = app(\Modules\Orders\Http\Controllers\OrderController::class);
        $analyticsResponse = $this->analytics($request);
        $analyticsData = $analyticsResponse->getData();

        return view('orders::orders.index', compact('lists', 'dateFrom', 'dateTo', 'analyticsData'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $payments = PaymentConfiguration::where('is_active', true)->get();

        if ($request->input('tab')) {
            $html = view('orders::orders.components.order_detail', array_merge(
                session('cart', []),
                ['mode' => 'create', 'payments' => $payments]
            ))->render();

            return response()->json([
                'success' => true,
                'order_datas' => $html
            ]);
        }

        return view('orders::orders.form', array_merge(
            session('cart', []),
            ['mode' => 'create', 'payments' => $payments]
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        Session::forget('cart');
        $order = Order::with(['customer', 'vendor', 'items', 'transactions'])->findOrFail($id);
        $orderData = $this->orderService->getOrder($id);
        $isRestaurantOwner = app(RestaurantOwnerRoleResolver::class)->isOwner(auth('admin')->user());
        $ownerFoodReviews = collect();
        if ($isRestaurantOwner) {
            $ownerRestaurant = Restaurant::where('admin_id', auth('admin')->id())->firstOrFail();
            abort_unless($order->vendor_type === Restaurant::class
                && (int) $order->vendor_id === (int) $ownerRestaurant->id, 404);

            $ownerFoodReviews = Rating::with(['user', 'order.customer', 'ratable'])
                ->where('order_id', $order->id)
                ->where('ratable_type', FoodItem::class)
                ->whereHasMorph('ratable', [FoodItem::class], fn ($food) => $food->where('restaurant_id', $ownerRestaurant->id))
                ->latest('created_at')
                ->get();
        }
        $canViewPayoutDetails = OrderCommissionAccess::visibleInSidebar();
        $orderPayout = $canViewPayoutDetails
            ? app(OrderCommissionAccess::class)->query(auth('admin')->user())->where('order_id', $order->id)->first()
            : null;
        $payments = PaymentConfiguration::where('is_active', true)->get();
        


        if ($request->input('tab')) {
            $html = view('orders::orders.components.order_detail', array_merge(
                $orderData,
                [
                    'mode' => 'view',
                    'payments' => $payments,
                    'can_view_payout_details' => $canViewPayoutDetails,
                    'order_payout' => $orderPayout,
                    'order' => $order,
                    'order_id' => $order->id,
                    'is_restaurant_owner' => $isRestaurantOwner,
                    'owner_food_reviews' => $ownerFoodReviews,
                ]
            ))->render();

            return response()->json([
                'success' => true,
                'order_datas' => $html
            ]);
        }

        $groupIds = [];
        foreach ($order->items as $item) {
            $options = is_string($item->selected_options) ? json_decode($item->selected_options, true) : $item->selected_options;
            if (is_array($options) && isset($options['groups']) && is_array($options['groups'])) {
                foreach ($options['groups'] as $group) {
                    if (isset($group['group_id'])) {
                        $groupIds[] = $group['group_id'];
                    }
                }
            }
        }
        $groupIds = array_unique($groupIds);



        return view('orders::orders.form', array_merge(
            $orderData,
            [
                'mode' => 'view',
                'payments' => $payments,
                'can_view_payout_details' => $canViewPayoutDetails,
                'order_payout' => $orderPayout,
                'order' => $order,
                'order_id' => $order->id,
                'is_restaurant_owner' => $isRestaurantOwner,
                'owner_food_reviews' => $ownerFoodReviews,

            ]
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        if (!session()->get('cart', [])) {
            $order = $this->orderService->getOrder($id);
            session(['cart' => $order]);
        }

        $payments = PaymentConfiguration::where('is_active', true)->get();

        if ($request->input('tab')) {
            $html = view('orders::orders.components.order_detail', array_merge(
                session('cart', []),
                ['mode' => 'edit', 'payments' => $payments]
            ))->render();

            return response()->json([
                'success' => true,
                'order_datas' => $html
            ]);
        }

        return view('orders::orders.form', array_merge(
            session('cart', []),
            ['mode' => 'edit', 'payments' => $payments]
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $sessionOrder = session('cart', []);

        if (empty($sessionOrder)) {
            return redirect()->back()->with('success', 'No session order data found');
        }

        $sessionOrder = $this->orderService->recalculateSessionOrder($sessionOrder);
        
        $createData = [
            'customer_id' => $sessionOrder['customer_details']['id'] ?? null,
            'customer_type' => $sessionOrder['customer_details']['type'] ?? 'App\\Models\\User',
            'items' => $sessionOrder['order_items'] ?? [],
            'currency' => $sessionOrder['currency'] ?? 'USD',
            'status' => $sessionOrder['status'] ?? fn_get_setting('general.order.create'),
            'coupon_code' => $sessionOrder['coupon_code'] ?? null,
            'billing_address' => $sessionOrder['billing_address'] ?? [],
            'shipping_address' => $sessionOrder['shipping_address'] ?? [],
            'notes' => $sessionOrder['order_note'] ?? null,
            'payment_method' => $sessionOrder['payment_method'] ?? null,
            'subtotal' => $sessionOrder['subtotal'],
            'total' => $sessionOrder['total'],
            'requires_payment' => false, // or false if you're only storing order
        ];

        try {
            $this->orderService->createOrder($createData);
            return redirect()->back()->with('success', 'Order created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('success', 'Failed to create order');
        } catch (\Throwable $e) {

            return redirect()->back()->with('success', 'Failed to create order');
        } catch (\InvalidArgumentException $e) {

            return redirect()->back()->with('success', 'Failed to create order');
        } catch (\Exception $e) {
            return redirect()->back()->with('success', 'Failed to create order');
        }
    }

    // /**
    //  * Update the specified resource in storage.
    //  */
    public function update(Request $request, string $id)
    {
        // Handle AJAX actions for order item management
        if ($request->has('action')) {
            return $this->handleAjaxAction($request, $id);
        }

        try {
            $sessionData = session('cart', []); // or however you store it

            if (empty($sessionData['order_id'])) {
                return redirect()->route('admin.orders.index')->with('error', 'Missing order ID');
            }

            $order = Order::findOrFail($sessionData['order_id']);

            $updateData = [
                'order_id' => $sessionData['order_id'],
                'status' => $sessionData['status'],
                'total' => $sessionData['total'],
                'subtotal' => $sessionData['subtotal'],
                'billing_address' => $sessionData['billing_address'],
                'shipping_address' => $sessionData['shipping_address'],
                'notes' => $sessionData['order_note'],
                'items' => $sessionData['order_items'],
                'coupon_code' => $sessionData['coupon_code'],
                'payment_method' => $sessionData['payment_method'],
                'customer_id' => $sessionData['customer_details']['id'],
                'customer_type' => $sessionData['customer_details']['type'] ?? 'App\\Models\\User'
            ];

            $this->orderService->updateOrder($order, $updateData, true);
            return redirect()->route('admin.orders.show', $sessionData['order_id'])->with('success', 'Order updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.orders.show', $sessionData['order_id'])->with('success', 'Unable to update order');
        } catch (\Throwable $e) {

            return redirect()->route('admin.orders.show', $sessionData['order_id'])->with('success', 'Unable to update order');
        } catch (\InvalidArgumentException $e) {

            return redirect()->route('admin.orders.show', $sessionData['order_id'])->with('success', 'Unable to update order');
        } catch (\Exception $e) {
            return redirect()->route('admin.orders.show', $sessionData['order_id'])->with('success', 'Unable to update order');
        }
    }

    /**
     * Handle AJAX actions for order management
     */
    private function handleAjaxAction(Request $request, string $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            
            switch ($request->action) {
                case 'update_item_quantity':
                    return $this->updateItemQuantity($request, $order);
                    
                case 'remove_item':
                    return $this->removeOrderItem($request, $order);
                    
                case 'remove_coupon':
                    return $this->removeCoupon($order);
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action'
                    ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order item quantity
     */
    private function updateItemQuantity(Request $request, Order $order)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $orderItem = $order->items()->findOrFail($request->item_id);
        if ($orderItem->selected_option_snapshot !== null) {
            throw \Illuminate\Validation\ValidationException::withMessages(['quantity' => 'Serving selections on placed orders are read only.']);
        }
        $orderItem->quantity = $request->quantity;
        
        // Recalculate total price
        $optionsPrice = 0;
        if ($orderItem->selected_options) {
            foreach ($orderItem->selected_options as $option) {
                $optionsPrice += $option['price'] ?? 0;
            }
        }
        $orderItem->total_price = ($orderItem->price + $optionsPrice) * $orderItem->quantity;
        $orderItem->save();

        // Recalculate order totals
        $this->orderService->recalculateOrderTotals($order);

        return response()->json([
            'success' => true,
            'message' => 'Quantity updated successfully'
        ]);
    }

    /**
     * Remove order item
     */
    private function removeOrderItem(Request $request, Order $order)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id'
        ]);

        $orderItem = $order->items()->findOrFail($request->item_id);
        $orderItem->delete();

        // Recalculate order totals
        $this->orderService->recalculateOrderTotals($order);

        return response()->json([
            'success' => true,
            'message' => 'Item removed successfully'
        ]);
    }

    /**
     * Remove coupon from order
     */
    private function removeCoupon(Order $order)
    {
        $order->update([
            'coupon_code' => null,
            'discount' => 0
        ]);

        // Recalculate order totals
        $this->orderService->recalculateOrderTotals($order);

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $order = Order::findOrFail($id);
            if (!$order->canDelete()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending unpaid, cancelled, or failed orders can be deleted.',
                ], 422);
            }

            if (!empty($order)) {
                Order::destroy($id);
            }
            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully',
                'redirect_url' => route('admin.orders.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => 'Unable to delete order',
                'redirect_url' => route('admin.orders.index')
            ]);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:orders,id',
        ]);

        try {
            [$deletedCount, $skippedCount] = DB::transaction(function () use ($request) {
                $orders = Order::whereIn('id', $request->input('ids'))->lockForUpdate()->get();
                $deletedCount = 0;
                $skippedCount = 0;

                foreach ($orders as $order) {
                    if (!$order->canDelete()) {
                        $skippedCount++;
                        continue;
                    }

                    if (!$order->delete()) {
                        throw new \RuntimeException('Unable to delete order.');
                    }

                    $deletedCount++;
                }

                return [$deletedCount, $skippedCount];
            });

            if ($deletedCount === 0) {
                return redirect()->route('admin.orders.index')->with('error', 'No orders deleted. Only unpaid orders with pending, cancelled, or failed status can be deleted.');
            }

            $message = "Deleted {$deletedCount} order(s) successfully.";
            if ($skippedCount > 0) {
                $message .= " Skipped {$skippedCount} order(s) that cannot be deleted.";
            }

            return redirect()->route('admin.orders.index')->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()->route('admin.orders.index')->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Update order status (Bulk action from orders list)
     */
    public function updateStatus(Request $request)
    {
       
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:orders,id'
        ]);

        try {
            $statusCode = $request->input('action');
            $orderIds = $request->input('ids', []);
            if (empty($orderIds)) {
                return back()->with('error', 'No orders selected.');
            }

            // Validate that the status exists
            $validStatuses = fn_get_order_statuses('O')->pluck('status_code')->toArray();
           
            if (!in_array($statusCode, $validStatuses)) {
                return back()->with('error', 'Invalid status selected.');
            }

            $updatedCount = 0;
            $failedMessages = [];
            foreach ($orderIds as $orderId) {
                try {
                    $order = Order::findOrFail($orderId);
                    $this->orderService->updateOrderStatus($order, $statusCode);
                    $updatedCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to update order {$orderId}: " . $e->getMessage());
                    $failedMessages[] = "Order #{$orderId}: " . $e->getMessage();
                }
            }

            if ($updatedCount > 0 && empty($failedMessages)) {
                return back()->with('success', "Successfully updated {$updatedCount} order(s) status.");
            }

            if ($updatedCount > 0 && !empty($failedMessages)) {
                return back()->with('error', implode(' ', $failedMessages));
            }

            if (!empty($failedMessages)) {
                return back()->with('error', implode(' ', $failedMessages));
            }

            return back()->with('error', 'Failed to update any orders.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    /**
     * Update single order status (from order detail page)
     */
    public function updateSingleStatus(Request $request, Order $order)
    {
        
        $request->validate([
            'status' => 'required|string',
            ...\Modules\Orders\Services\OrderStatusActionService::rules((string) $request->input('status'))
        ]);

        try {
            // Validate that the status exists
            $validStatuses = fn_get_order_statuses('O')->pluck('status_code')->toArray();
           
            if ($request->status !== 'R' && !in_array($request->status, $validStatuses)) {
                if ($request->ajax() || $request->input('is_ajax')) {
                    return response()->json([
                        'success' => false,
                        'errors' => 'Invalid status selected.',
                        'message' => 'Invalid status selected.'
                    ]);
                }
                return redirect()->back()->with('error', 'Invalid status selected.');
            }

            $actor = $request->user('admin');
            abort_unless($actor instanceof \Modules\Acl\Models\Admin, 403);
            $source = app(\Modules\Restaurant\Services\RestaurantOwnerRoleResolver::class)->isOwner($actor) ? 'restaurant_cancellation' : 'admin';
            app(\Modules\Orders\Services\OrderStatusActionService::class)->update($order, $request->status, $request->all(), $actor, $source);

            $statusName = $request->status === 'R' ? 'Cancelled / payment refunded' : fn_get_order_status($request->status, 'O')->name;

            if ($request->ajax() || $request->input('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order status updated successfully to ' . $statusName,
                    'redirect_url' => route('admin.orders.show', $order->id)
                ]);
            }

            return redirect()->back()->with('success', 'Order status updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Order status update failed: ' . $e->getMessage());
            
            if ($request->ajax() || $request->input('is_ajax')) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->getMessage(),
                    'message' => 'Failed to update order status: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    public function updatePaymentStatus(Request $request, Order $order, \Modules\Orders\Services\CodPaymentService $cashPayments)
    {
        $actor = auth('admin')->user();
        abort_unless($actor && $actor->hasPermission('admin.orders.update-payment-status'), 403);
        $ownedRestaurants = Restaurant::where('admin_id', $actor->id);
        $isRestaurantOwner = app(\Modules\Restaurant\Services\RestaurantOwnerRoleResolver::class)->isOwner($actor);
        if ($isRestaurantOwner || (clone $ownedRestaurants)->exists()) {
            abort_unless($order->vendor_type === (new Restaurant())->getMorphClass()
                && $ownedRestaurants->whereKey($order->vendor_id)->exists(), 403);
        }
        $validated = $request->validate([
            'payment_status' => 'required|in:paid',
            'payment_notes' => 'nullable|string|max:1000',
        ]);
        $cashPayments->collect($order, $actor, $validated['payment_notes'] ?? null);
        $message = 'Cash payment recorded. The order is now marked as paid.';
        if ($request->expectsJson() || $request->ajax() || $request->boolean('is_ajax')) {
            return response()->json(['success' => true, 'message' => $message,
                'redirect_url' => route('admin.orders.show', $order->id)]);
        }
        return redirect()->route('admin.orders.show', $order->id)->with('success', $message);
    }

    /**
     * Get overall orders analytics
     */
    public function analytics(Request $request)
    {
        try {
            $dateFrom = $request->input('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));

            $dateFrom = Carbon::parse($dateFrom)->startOfDay();
            $dateTo = Carbon::parse($dateTo)->endOfDay();

            $ordersQuery = Order::whereBetween('created_at', [$dateFrom, $dateTo]);

            $scope = app(\Modules\Restaurant\Services\RestaurantDataScope::class);
            $restaurantId = $scope->restaurantId();
            $scope->scopeOrders($ordersQuery, $restaurantId);
            $commissionAnalytics = app(\Modules\Orders\Services\CommissionRevenueAnalytics::class)
                ->forOrders($ordersQuery, $restaurantId !== null);

            $totalOrders = $ordersQuery->count();
            $completedOrders = (clone $ordersQuery)->where('status', Order::STATUS_COMPLETED)->count();
            $cancelledOrders = (clone $ordersQuery)->where('status', Order::STATUS_CANCELLED)->count();
            $pendingOrders = (clone $ordersQuery)->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_NEW, Order::STATUS_PROCESSING])->count();
            $siteCurrency = fn_get_setting('general.currency', 'USD');
            $paymentOrders = (clone $ordersQuery)
                ->select('total', 'currency', 'payment_status', 'payment_method', 'status')
                ->get();

            $paymentStatusTotals = [
                Order::PAYMENT_PAID => 0.0,
                Order::PAYMENT_FAILED => 0.0,
                Order::PAYMENT_PENDING => 0.0,
                Order::PAYMENT_REFUNDED => 0.0,
            ];

            foreach ($paymentOrders as $paymentOrder) {
                $convertedTotal = $this->convertOrderAmountToCurrency(
                    (float) $paymentOrder->total,
                    $paymentOrder->currency ?: $siteCurrency,
                    $siteCurrency
                );

                $paymentStatus = $paymentOrder->payment_status ?? Order::PAYMENT_PENDING;
                if (in_array($paymentStatus, ['completed', 'captured'], true)) {
                    $paymentStatus = Order::PAYMENT_PAID;
                }
                if ($paymentStatus === Order::PAYMENT_PENDING
                    && in_array($paymentOrder->status, [Order::STATUS_CANCELLED, Order::STATUS_FAILED], true)) {
                    continue;
                }

                if (!array_key_exists($paymentStatus, $paymentStatusTotals)) {
                    $paymentStatusTotals[$paymentStatus] = 0.0;
                }

                $paymentStatusTotals[$paymentStatus] += $convertedTotal;
            }

            // Order status breakdown with proper names
            $orderStatusBreakdown = (clone $ordersQuery)
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get()
                ->mapWithKeys(function($item) {
                    $statusInfo = Status::where('status_code', $item->status)
                        ->where('type_code', 'O')
                        ->first();
                    
                    return [
                        $item->status => [
                            'count' => $item->count,
                            'name' => $statusInfo ? $statusInfo->name : ucfirst($item->status),
                            'color' => $statusInfo ? $statusInfo->color : '#6B7280',
                            'code' => $item->status
                        ]
                    ];
                });

            $paymentMethodBreakdown = $commissionAnalytics['by_method']->map(function ($item, $key) {
                return $item + ['name' => ucfirst(str_replace('_', ' ', $key))];
            });

            // Daily trend (last 7 days)
            $dailyTrend = (clone $ordersQuery)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(CASE WHEN status = "' . Order::STATUS_COMPLETED . '" THEN 1 ELSE 0 END) as completed_orders')
                )
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(7)
                ->get()
                ->reverse()
                ->values();

            $dailyTrend->each(function ($day) use ($commissionAnalytics) {
                $day->revenue = $commissionAnalytics['by_date']->get($day->date)['revenue'] ?? 0;
                $day->revenue_formatted = fn_format_currency_amount($day->revenue, $commissionAnalytics['revenue']['currency']);
            });

            // Customer analytics
            $uniqueCustomers = (clone $ordersQuery)->distinct('customer_id')->count('customer_id');
            $repeatCustomers = (clone $ordersQuery)
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'date_range' => [
                        'from' => $dateFrom->format('Y-m-d'),
                        'to' => $dateTo->format('Y-m-d'),
                    ],
                    'orders' => [
                        'total' => $totalOrders,
                        'completed' => $completedOrders,
                        'cancelled' => $cancelledOrders,
                        'pending' => $pendingOrders,
                        'by_status' => $orderStatusBreakdown,
                    ],
                    'revenue' => $commissionAnalytics['revenue'],
                    'payments' => [
                        'paid' => round($paymentStatusTotals[Order::PAYMENT_PAID] ?? 0, 2),
                        'paid_formatted' => fn_convert_currency($paymentStatusTotals[Order::PAYMENT_PAID] ?? 0, $siteCurrency),
                        'failed' => round($paymentStatusTotals[Order::PAYMENT_FAILED] ?? 0, 2),
                        'failed_formatted' => fn_convert_currency($paymentStatusTotals[Order::PAYMENT_FAILED] ?? 0, $siteCurrency),
                        'pending' => round($paymentStatusTotals[Order::PAYMENT_PENDING] ?? 0, 2),
                        'pending_formatted' => fn_convert_currency($paymentStatusTotals[Order::PAYMENT_PENDING] ?? 0, $siteCurrency),
                        'refunded' => round($paymentStatusTotals[Order::PAYMENT_REFUNDED] ?? 0, 2),
                        'refunded_formatted' => fn_convert_currency($paymentStatusTotals[Order::PAYMENT_REFUNDED] ?? 0, $siteCurrency),
                    ],
                    'customers' => [
                        'unique' => $uniqueCustomers,
                        'repeat' => $repeatCustomers,
                        'repeat_rate' => $uniqueCustomers > 0 ? round(($repeatCustomers / $uniqueCustomers) * 100, 2) : 0,
                    ],
                    'payment_method_breakdown' => $paymentMethodBreakdown,
                    'daily_trend' => $dailyTrend,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function convertOrderAmountToCurrency(float $amount, string $fromCurrencyCode, string $toCurrencyCode): float
    {
        try {
            if ($fromCurrencyCode === $toCurrencyCode) {
                return round($amount, 2);
            }

            $fromCurrency = fn_get_currency_by_code($fromCurrencyCode);
            $toCurrency = fn_get_currency_by_code($toCurrencyCode);

            if (!$fromCurrency || !$toCurrency) {
                return round($amount, 2);
            }

            $rateRow = DB::table('currency_exchange_rates')
                ->where('base_currency', $fromCurrency->id)
                ->where('target_currency', $toCurrency->id)
                ->first();

            $rate = null;
            if ($rateRow && isset($rateRow->rate)) {
                $rate = (float) $rateRow->rate;
            } else {
                $reverse = DB::table('currency_exchange_rates')
                    ->where('base_currency', $toCurrency->id)
                    ->where('target_currency', $fromCurrency->id)
                    ->first();

                if ($reverse && isset($reverse->rate) && (float) $reverse->rate !== 0.0) {
                    $rate = 1 / (float) $reverse->rate;
                }
            }

            if (!$rate) {
                return round($amount, 2);
            }

            return round($amount * $rate, (int) ($toCurrency->decimal ?? 2));
        } catch (\Throwable $e) {
            Log::warning('Orders analytics currency conversion fallback used', [
                'from_currency' => $fromCurrencyCode,
                'to_currency' => $toCurrencyCode,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return round($amount, 2);
        }
    }


}
