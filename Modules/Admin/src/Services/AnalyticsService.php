<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Models\Restaurant;
use Modules\Acl\Models\Customer;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Status;
use Modules\Payments\Models\Payments;
use Modules\Payments\Models\PaymentMethod;
use Modules\Payments\Models\PaymentConfiguration;
use Modules\Orders\Models\Transaction;
use Carbon\Carbon;

class AnalyticsService
{
    protected function formatAnalyticsCurrency(float $amount): string
    {
        return fn_convert_currency($amount, fn_get_setting('general.currency', 'USD'));
    }

    protected function resolvePaymentMethodName($paymentMethod): string
    {
        if (empty($paymentMethod) && $paymentMethod !== '0') {
            return 'N/A';
        }

        if (is_numeric($paymentMethod)) {
            $paymentConfiguration = PaymentConfiguration::with('method')->find($paymentMethod);

            if ($paymentConfiguration) {
                return $paymentConfiguration->merchant_name
                    ?: ($paymentConfiguration->method->name ?? ('Payment Method #' . $paymentMethod));
            }

            $paymentConfiguration = PaymentConfiguration::with('method')
                ->where('payment_method_id', $paymentMethod)
                ->first();

            if ($paymentConfiguration) {
                return $paymentConfiguration->merchant_name
                    ?: ($paymentConfiguration->method->name ?? ('Payment Method #' . $paymentMethod));
            }

            $paymentMethodRecord = PaymentMethod::find($paymentMethod);
            if ($paymentMethodRecord) {
                return $paymentMethodRecord->name;
            }
        }

        $paymentMethodRecord = PaymentMethod::where('code', $paymentMethod)->first();
        if ($paymentMethodRecord) {
            return $paymentMethodRecord->name;
        }

        return ucwords(str_replace('_', ' ', (string) $paymentMethod));
    }

    /**
     * Get restaurant analytics with related data
     */
    public function getRestaurantAnalytics($restaurantId, $dateFrom = null, $dateTo = null)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        
        $dateFrom = $dateFrom
            ? Carbon::parse($dateFrom)->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();
        $dateTo = $dateTo
            ? Carbon::parse($dateTo)->endOfDay()
            : Carbon::now()->endOfDay();

        // Orders analytics
        $ordersQuery = Order::where('vendor_type', Restaurant::class)
            ->where('vendor_id', $restaurantId)
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        $totalOrders = $ordersQuery->count();
        $completedOrders = (clone $ordersQuery)->where('status', 'D')->count();
        $cancelledOrders = (clone $ordersQuery)->where('status', Order::STATUS_CANCELLED)->count();
        $pendingOrders = (clone $ordersQuery)->whereIn('status', ['W', 'O', 'P', 'H', 'Z', 'N'])->count();

        $paidOrdersQuery = (clone $ordersQuery)->where('payment_status', Order::PAYMENT_PAID);
        $paidOrdersCount = (clone $paidOrdersQuery)->count();

        // Revenue analytics
        $totalRevenue = (clone $paidOrdersQuery)->sum('total');
        $averageOrderValue = $paidOrdersCount > 0 ? $totalRevenue / $paidOrdersCount : 0;

        // Payment analytics
        $totalPayments = (clone $ordersQuery)->where('payment_status', Order::PAYMENT_PAID)->sum('total');
        $pendingPayments = (clone $ordersQuery)->where('payment_status', Order::PAYMENT_PENDING)->sum('total');
        $failedPayments = (clone $ordersQuery)->where('payment_status', Order::PAYMENT_FAILED)->count();

        // Transaction analytics
        $transactionsQuery = Transaction::whereHas('order', function($q) use ($restaurantId) {
            $q->where('vendor_type', Restaurant::class)->where('vendor_id', $restaurantId);
        })->whereBetween('created_at', [$dateFrom, $dateTo]);

        $totalTransactions = $transactionsQuery->count();
        $completedTransactions = (clone $transactionsQuery)->where('status', Transaction::STATUS_COMPLETED)->count();
        $refundTransactions = (clone $transactionsQuery)->where('type', Transaction::TYPE_REFUND)->count();

        // Customer analytics
        $uniqueCustomers = (clone $ordersQuery)->distinct('customer_id')->count('customer_id');
        $repeatCustomers = (clone $ordersQuery)
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        // Order status breakdown with proper names from statuses table
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

        // Order type breakdown
        $orderTypeBreakdown = (clone $ordersQuery)
            ->select(
                'order_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN payment_status = "' . Order::PAYMENT_PAID . '" THEN total ELSE 0 END) as revenue')
            )
            ->groupBy('order_type')
            ->get()
            ->mapWithKeys(function($item) {
                return [
                    $item->order_type => [
                        'count' => $item->count,
                        'revenue' => $item->revenue,
                        'name' => ucfirst(str_replace('_', ' ', $item->order_type))
                    ]
                ];
            });

        // Payment method breakdown
        $paymentMethodBreakdown = (clone $paidOrdersQuery)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as revenue'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(function($item) {
                return [
                    $item->payment_method => [
                        'count' => $item->count,
                        'revenue' => $item->revenue,
                        'name' => $this->resolvePaymentMethodName($item->payment_method)
                    ]
                ];
            });

        // Daily trend (last 7 days)
        $dailyTrend = (clone $ordersQuery)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN status = "D" THEN 1 ELSE 0 END) as completed_orders'),
                DB::raw('SUM(CASE WHEN payment_status = "' . Order::PAYMENT_PAID . '" THEN total ELSE 0 END) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get()
            ->reverse()
            ->values();

        // Peak hours analysis
        $peakHours = (clone $ordersQuery)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(6)
            ->get()
            ->pluck('count', 'hour');

        // Busiest days of week
        $busiestDays = (clone $ordersQuery)
            ->select(DB::raw('DAYNAME(created_at) as day'), DB::raw('COUNT(*) as count'), DB::raw('SUM(CASE WHEN payment_status = "' . Order::PAYMENT_PAID . '" THEN total ELSE 0 END) as revenue'))
            ->groupBy('day')
            ->orderByDesc('count')
            ->get();

        // Average order processing time (using updated_at as completion time for delivered orders)
        $avgProcessingTime = (clone $ordersQuery)
            ->where('status', 'D')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes')
            ->value('avg_minutes') ?? 0;

        // Highest order value
        $highestOrder = (clone $paidOrdersQuery)->max('total') ?? 0;
        $lowestOrder = (clone $paidOrdersQuery)->min('total') ?? 0;

        // Top customers
        $topCustomers = (clone $ordersQuery)
            ->select(
                'customer_id',
                'customer_type',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(CASE WHEN payment_status = "' . Order::PAYMENT_PAID . '" THEN total ELSE 0 END) as total_spent'),
                DB::raw('SUM(CASE WHEN payment_status = "' . Order::PAYMENT_PAID . '" THEN 1 ELSE 0 END) as paid_order_count')
            )
            ->groupBy('customer_id', 'customer_type')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get()
            ->map(function($item) {
                $customer = $item->customer_type::find($item->customer_id);
                return [
                    'name' => $customer->name ?? 'N/A',
                    'email' => $customer->email ?? 'N/A',
                    'orders' => $item->order_count,
                    'total_spent' => $item->total_spent,
                    'total_spent_formatted' => $this->formatAnalyticsCurrency((float) $item->total_spent),
                    'avg_order' => $item->paid_order_count > 0 ? $item->total_spent / $item->paid_order_count : 0,
                    'avg_order_formatted' => $this->formatAnalyticsCurrency((float) ($item->paid_order_count > 0 ? $item->total_spent / $item->paid_order_count : 0)),
                ];
            });

        // New customers in period
        $newCustomers = (clone $ordersQuery)
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('MIN(created_at) >= ?', [$dateFrom])
            ->get()
            ->count();

        // Customer spending analytics
        $customerSpending = (clone $paidOrdersQuery)
            ->select('customer_id', DB::raw('SUM(total) as total_spent'), DB::raw('COUNT(*) as order_count'))
            ->groupBy('customer_id')
            ->get();

        $avgSpendPerCustomer = $uniqueCustomers > 0 ? $customerSpending->avg('total_spent') : 0;
        $highestSpendingCustomer = $customerSpending->max('total_spent') ?? 0;
        $avgOrdersPerCustomer = $uniqueCustomers > 0 ? $customerSpending->avg('order_count') : 0;

        // Customer acquisition by month
        $customersByMonth = (clone $ordersQuery)
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(DISTINCT customer_id) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->limit(6)
            ->get()
            ->pluck('count', 'month');

        // Recent orders
        $recentOrders = (clone $ordersQuery)
            ->with(['customer', 'statusRelation'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name ?? 'N/A',
                    'total' => $order->total,
                    'total_formatted' => $this->formatAnalyticsCurrency((float) $order->total),
                    'status' => $order->formatted_status,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return [
            'restaurant' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'status' => $restaurant->status,
            ],
            'date_range' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
            'orders' => [
                'total' => $totalOrders,
                'completed' => $completedOrders,
                'cancelled' => $cancelledOrders,
                'pending' => $pendingOrders,
                'completion_rate' => $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 2) : 0,
                'cancellation_rate' => $totalOrders > 0 ? round(($cancelledOrders / $totalOrders) * 100, 2) : 0,
                'by_status' => $orderStatusBreakdown,
                'peak_hours' => $peakHours,
                'busiest_days' => $busiestDays,
                'avg_processing_time' => round($avgProcessingTime, 1),
            ],
            'revenue' => [
                'total' => round($totalRevenue, 2),
                'total_formatted' => $this->formatAnalyticsCurrency((float) $totalRevenue),
                'average_order_value' => round($averageOrderValue, 2),
                'average_order_value_formatted' => $this->formatAnalyticsCurrency((float) $averageOrderValue),
                'highest_order' => round($highestOrder, 2),
                'highest_order_formatted' => $this->formatAnalyticsCurrency((float) $highestOrder),
                'lowest_order' => round($lowestOrder, 2),
                'lowest_order_formatted' => $this->formatAnalyticsCurrency((float) $lowestOrder),
                'currency' => fn_get_setting('general.currency', 'USD'),
            ],
            'payments' => [
                'total_collected' => round($totalPayments, 2),
                'total_collected_formatted' => $this->formatAnalyticsCurrency((float) $totalPayments),
                'pending' => round($pendingPayments, 2),
                'pending_formatted' => $this->formatAnalyticsCurrency((float) $pendingPayments),
                'failed_count' => $failedPayments,
            ],
            'transactions' => [
                'total' => $totalTransactions,
                'completed' => $completedTransactions,
                'refunds' => $refundTransactions,
            ],
            'customers' => [
                'unique' => $uniqueCustomers,
                'repeat' => $repeatCustomers,
                'new' => $newCustomers,
                'repeat_rate' => $uniqueCustomers > 0 ? round(($repeatCustomers / $uniqueCustomers) * 100, 2) : 0,
                'average_spend' => round($avgSpendPerCustomer, 2),
                'average_spend_formatted' => $this->formatAnalyticsCurrency((float) $avgSpendPerCustomer),
                'highest_spend' => round($highestSpendingCustomer, 2),
                'highest_spend_formatted' => $this->formatAnalyticsCurrency((float) $highestSpendingCustomer),
                'average_orders' => round($avgOrdersPerCustomer, 2),
                'top_customers' => $topCustomers,
                'by_month' => $customersByMonth,
            ],
            'order_type_breakdown' => $orderTypeBreakdown,
            'payment_method_breakdown' => $paymentMethodBreakdown,
            'daily_trend' => $dailyTrend,
            'top_customers' => $topCustomers,
        ];
    }

    /**
     * Get customer analytics with related data
     */
    public function getCustomerAnalytics($customerId, $dateFrom = null, $dateTo = null)
    {
        $customer = Customer::findOrFail($customerId);

        $dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
        $dateTo = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;

        // Orders analytics
        $ordersQuery = Order::where('customer_type', Customer::class)
            ->where('customer_id', $customerId);

        if ($dateFrom && $dateTo) {
            $ordersQuery->whereBetween('created_at', [$dateFrom, $dateTo]);
        }

        $totalOrders = $ordersQuery->count();
        $completedOrders = (clone $ordersQuery)->where('status', Order::STATUS_COMPLETED)->count();
        $cancelledOrders = (clone $ordersQuery)->where('status', Order::STATUS_CANCELLED)->count();
        $pendingOrders = (clone $ordersQuery)->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_NEW, Order::STATUS_PROCESSING])->count();
        $paidOrdersQuery = (clone $ordersQuery)->where('payment_status', Order::PAYMENT_PAID);
        $paidOrdersCount = (clone $paidOrdersQuery)->count();

        // Spending analytics
        $totalSpent = (clone $paidOrdersQuery)->sum('total');
        $averageOrderValue = $paidOrdersCount > 0 ? $totalSpent / $paidOrdersCount : 0;
        $highestOrderValue = (clone $paidOrdersQuery)->max('total') ?? 0;
        $lowestOrderValue = (clone $paidOrdersQuery)->min('total') ?? 0;

        // Order frequency (days between orders)
        $orderDates = (clone $paidOrdersQuery)
            ->orderBy('created_at')
            ->pluck('created_at');
        
        $avgDaysBetweenOrders = 0;
        if ($orderDates->count() > 1) {
            $daysDiffs = [];
            for ($i = 1; $i < $orderDates->count(); $i++) {
                $daysDiffs[] = $orderDates[$i]->diffInDays($orderDates[$i-1]);
            }
            $avgDaysBetweenOrders = count($daysDiffs) > 0 ? round(array_sum($daysDiffs) / count($daysDiffs), 1) : 0;
        }

        // Favorite time to order
        $favoriteHour = (clone $ordersQuery)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderByDesc('count')
            ->first();

        // Favorite day to order
        $favoriteDay = (clone $ordersQuery)
            ->select(DB::raw('DAYNAME(created_at) as day'), DB::raw('COUNT(*) as count'))
            ->groupBy('day')
            ->orderByDesc('count')
            ->first();

        // Restaurant preferences
        $restaurantBreakdown = (clone $ordersQuery)
            ->where('vendor_type', Restaurant::class)
            ->select('vendor_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total) as total_spent'))
            ->groupBy('vendor_id')
            ->orderByDesc('order_count')
            ->limit(10)
            ->get()
            ->map(function($item) {
                $restaurant = Restaurant::find($item->vendor_id);
                return [
                    'restaurant_id' => $item->vendor_id,
                    'restaurant_name' => $restaurant->name ?? 'N/A',
                    'order_count' => $item->order_count,
                    'total_spent' => $item->total_spent,
                    'total_spent_formatted' => $this->formatAnalyticsCurrency((float) $item->total_spent),
                ];
            });

        // Order type preferences
        $orderTypeBreakdown = (clone $ordersQuery)
            ->select('order_type', DB::raw('COUNT(*) as count'))
            ->groupBy('order_type')
            ->get()
            ->pluck('count', 'order_type');

        // Payment method preferences
        $paymentMethodBreakdown = (clone $ordersQuery)
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $this->resolvePaymentMethodName($item->payment_method) => $item->count,
                ];
            });

        // Monthly spending trend
        $monthlySpending = (clone $paidOrdersQuery)
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total) as total_spent'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Recent orders
        $recentOrders = (clone $ordersQuery)
            ->with(['vendor', 'statusRelation'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'restaurant_name' => $order->vendor->name ?? 'N/A',
                    'total' => $order->total,
                    'total_formatted' => $this->formatAnalyticsCurrency((float) $order->total),
                    'status' => $order->formatted_status,
                    'order_type' => $order->order_type,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Transaction history
        $transactions = Transaction::whereHas('order', function($q) use ($customerId) {
            $q->where('customer_type', Customer::class)->where('customer_id', $customerId);
        })
        ->with('order')
        ->orderByDesc('created_at')
        ->limit(20)
        ->when($dateFrom && $dateTo, function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        })
        ->get()
        ->map(function($transaction) {
            return [
                'id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'order_number' => $transaction->order->order_number ?? 'N/A',
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'amount_formatted' => $this->formatAnalyticsCurrency((float) $transaction->amount),
                'status' => $transaction->status,
                'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'status' => $customer->status,
                'member_since' => $customer->created_at->format('Y-m-d'),
            ],
            'date_range' => [
                'from' => $dateFrom?->format('Y-m-d'),
                'to' => $dateTo?->format('Y-m-d'),
            ],
            'orders' => [
                'total' => $totalOrders,
                'completed' => $completedOrders,
                'cancelled' => $cancelledOrders,
                'pending' => $pendingOrders,
            ],
            'spending' => [
                'total' => round($totalSpent, 2),
                'total_formatted' => $this->formatAnalyticsCurrency((float) $totalSpent),
                'average_order_value' => round($averageOrderValue, 2),
                'average_order_value_formatted' => $this->formatAnalyticsCurrency((float) $averageOrderValue),
                'highest_order' => round($highestOrderValue, 2),
                'highest_order_formatted' => $this->formatAnalyticsCurrency((float) $highestOrderValue),
                'lowest_order' => round($lowestOrderValue, 2),
                'lowest_order_formatted' => $this->formatAnalyticsCurrency((float) $lowestOrderValue),
                'currency' => fn_get_setting('general.currency', 'USD'),
            ],
            'behavior' => [
                'avg_days_between_orders' => $avgDaysBetweenOrders,
                'favorite_hour' => $favoriteHour ? $favoriteHour->hour : null,
                'favorite_day' => $favoriteDay ? $favoriteDay->day : null,
            ],
            'favorite_restaurants' => $restaurantBreakdown,
            'order_type_preferences' => $orderTypeBreakdown,
            'payment_method_preferences' => $paymentMethodBreakdown,
            'monthly_spending_trend' => $monthlySpending,
            'recent_orders' => $recentOrders,
            'recent_transactions' => $transactions,
        ];
    }

    /**
     * Get order analytics with related data
     */
    public function getOrderAnalytics($orderId)
    {
        $order = Order::with(['customer', 'vendor', 'items', 'payments', 'transactions', 'statusRelation'])
            ->findOrFail($orderId);

        // Order items breakdown
        $itemsBreakdown = $order->items->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ];
        });

        // Payment history
        $paymentHistory = $order->payments->map(function($payment) {
            return [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'payment_type' => $payment->payment_type,
                'processed_at' => $payment->processed_at ? $payment->processed_at->format('Y-m-d H:i:s') : null,
            ];
        });

        // Transaction history
        $transactionHistory = $order->transactions->map(function($transaction) {
            return [
                'id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->formatted_status,
                'order_type' => $order->order_type,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            ],
            'customer' => [
                'id' => $order->customer->id ?? null,
                'name' => $order->customer->name ?? 'N/A',
                'email' => $order->customer->email ?? 'N/A',
                'phone' => $order->customer->phone ?? 'N/A',
            ],
            'restaurant' => [
                'id' => $order->vendor->id ?? null,
                'name' => $order->vendor->name ?? 'N/A',
                'phone' => $order->vendor->phone ?? 'N/A',
            ],
            'pricing' => [
                'subtotal' => $order->subtotal,
                'discount' => $order->discount,
                'tax' => $order->tax,
                'delivery_fee' => $order->delivery_fee,
                'service_charge' => $order->service_charge,
                'total' => $order->total,
                'currency' => $order->currency ?? 'USD',
            ],
            'items' => $itemsBreakdown,
            'payment_summary' => [
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'total_paid' => $order->payments->where('status', Payments::STATUS_COMPLETED)->sum('amount'),
                'total_refunded' => $order->transactions->where('type', Transaction::TYPE_REFUND)->sum('amount'),
            ],
            'payment_history' => $paymentHistory,
            'transaction_history' => $transactionHistory,
        ];
    }

    /**
     * Get transaction analytics
     */
    public function getTransactionAnalytics($transactionId)
    {
        $transaction = Transaction::with(['order.customer', 'order.vendor', 'payment'])
            ->findOrFail($transactionId);

        return [
            'transaction' => [
                'id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
                'currency' => $transaction->currency,
                'gateway_reference' => $transaction->gateway_reference,
                'notes' => $transaction->notes,
                'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                'processed_at' => $transaction->processed_at ? $transaction->processed_at->format('Y-m-d H:i:s') : null,
            ],
            'order' => [
                'id' => $transaction->order->id ?? null,
                'order_number' => $transaction->order->order_number ?? 'N/A',
                'total' => $transaction->order->total ?? 0,
                'status' => $transaction->order->formatted_status ?? 'N/A',
            ],
            'customer' => [
                'id' => $transaction->order->customer->id ?? null,
                'name' => $transaction->order->customer->name ?? 'N/A',
                'email' => $transaction->order->customer->email ?? 'N/A',
            ],
            'restaurant' => [
                'id' => $transaction->order->vendor->id ?? null,
                'name' => $transaction->order->vendor->name ?? 'N/A',
            ],
            'payment' => [
                'id' => $transaction->payment->id ?? null,
                'status' => $transaction->payment->status ?? 'N/A',
                'payment_type' => $transaction->payment->payment_type ?? 'N/A',
            ],
        ];
    }
}
