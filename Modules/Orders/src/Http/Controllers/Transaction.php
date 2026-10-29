<?php

namespace Modules\Orders\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Orders\Models\Order;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Customers\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\Orders\Enums\OrderStatus;
use Modules\Payments\Models\Payments;
use Modules\Cart\Services\CartService;
use Modules\Orders\Enums\TransactionType;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Orders\Enums\TransactionStatus;
use Modules\Orders\DataView\TransactionGrid;
use Modules\Payments\Services\PaymentsService;
use Modules\Orders\Services\TransactionService;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Notifications\Services\NotificationService;
use Modules\Orders\Models\Transaction as TransactionModel;
use Modules\Orders\Models\Transaction as ModelsTransaction;
use Carbon\Carbon;
use Modules\Restaurant\Models\Restaurant;

class Transaction extends Controller
{
    protected TransactionService $transactionService;
    protected CartService $cartService;
    protected $notificationService;
    protected PaymentsService $paymentsService;

    public function __construct(TransactionService $transactionService, CartService $cartService, NotificationService $notificationService, PaymentsService $paymentsService)
    {
        $this->transactionService = $transactionService;
        $this->cartService = $cartService;
        $this->notificationService = $notificationService;
        $this->paymentsService = $paymentsService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lists = fn_datagrid(TransactionGrid::class)->process();
        
        // Get date range from request or use defaults
        $dateFrom = $request->input('date_from', date('Y-m-d', strtotime('-30 days')));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        // Get analytics data
        $analyticsResponse = $this->analytics($request);
        $analyticsData = $analyticsResponse->getData();

        return view('orders::transaction.index', compact('lists', 'dateFrom', 'dateTo', 'analyticsData'));
    }

    public function create(Request $request)
    {
        $order = Order::findOrFail($request->id);
        return view('orders::transaction.form', compact('order'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|exists:payment_methods,id',
            'transaction_amount' => 'required|numeric',
            'transaction_notes' => 'nullable|string',
            'payment_reference_id' => 'required|string',
            'notify_to_customer' => 'nullable|in:Y,N',
            'status' => 'required|in:paid,unpaid,refunded'
        ]);

        try {
            // Check if there's an existing transaction that's not in a final state
            $existingTransaction = DB::table('payments')
                ->where('order_id', $request->order_id)
                ->whereNotIn('status', [
                    PaymentStatus::COMPLETED->value,
                    PaymentStatus::REFUNDED->value,
                    PaymentStatus::FAILED->value,
                    PaymentStatus::CANCELLED->value
                ])
                ->first();

            if ($existingTransaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'An active transaction already exists for this order. Please complete, cancel, or fail the existing transaction before creating a new one.',
                    'transaction_id' => $existingTransaction->id
                ], 400);
            }
            DB::transaction(function () use ($request) {
                $status = $request->status;
                $order = Order::findOrFail($request->order_id);

                $updateData = [
                    'order_id'         => $request->order_id,
                    'payment_method'   => $request->payment_method,
                ];

                if ($status === PaymentStatus::COMPLETED->value || $status === PaymentStatus::CAPTURED->value || $status === PaymentStatus::PAID->value) {
                    $updateData['status'] = fn_get_order_status_by_name('COMPLETED');
                    $updateData['payment_status'] = PaymentStatus::COMPLETED->value;
                } elseif ($status === PaymentStatus::AUTHORIZED->value) {
                    $updateData['status'] = fn_get_order_status_by_name('PROCESSING');
                    $updateData['payment_status'] = PaymentStatus::AUTHORIZED->value;
                } elseif ($status === PaymentStatus::REFUNDED->value) {
                    $updateData['status'] = fn_get_order_status_by_name('REFUNDED');
                    $updateData['payment_status'] = PaymentStatus::REFUNDED->value;
                } elseif ($status === PaymentStatus::UNPAID->value) {
                    $updateData['status'] = fn_get_order_status_by_name('PROCESSING');
                    $updateData['payment_status'] = PaymentStatus::UNPAID->value;
                } elseif ($status === PaymentStatus::PROCESSING->value) {
                    $updateData['status'] = fn_get_order_status_by_name('PROCESSING');
                    $updateData['payment_status'] = PaymentStatus::PROCESSING->value;
                } elseif ($status === PaymentStatus::FAILED->value) {
                    $updateData['status'] = fn_get_order_status_by_name('FAILED');
                    $updateData['payment_status'] = PaymentStatus::FAILED->value;
                }

                $order->update($updateData);
                $order->save();

                $payment = Payments::updateOrCreate(
                    [
                        'order_id' => $order->id,
                    ],
                    [
                        'gateway_reference' => $request->payment_reference_id,
                        'payment_method_id' => $request->payment_method,
                        'amount' => $request->transaction_amount,
                        'currency' => $order->currency,
                        'status' => $updateData['payment_status'],
                        'details' => [
                            'status'    => $updateData['payment_status'],
                            'card' => '',
                            'billing_address' => '',
                            'shipping_address' => '',
                            'metadata' => [
                                'gateway_response'  =>  '',
                                'receiptUrl'    => '',
                                'gateway'       => 'Custom payment'
                            ],
                        ],
                        'ip_address'         => request()->ip() ?? '',
                        'user_agent'         => request()->userAgent() ?? '',
                    ]
                );

                $transaction = $this->transactionService->record(
                    order: $order,
                    type: TransactionType::PAYMENT->value,
                    amount: $payment->amount,
                    status: $updateData['payment_status'],
                    payment: $payment,
                    metadata: [
                        'transaction_id' => $payment->id,
                        'gateway_response' => $payment->gateway_reference,
                    ],
                    notes: 'Transaction created by ' . Auth::guard('admin')->user()->name
                );

                $payment->transaction_id = $transaction->id;
                $payment->save();

                if ($request->notify_to_customer == 'Y') {
                    $user = $order->customer;

                    $this->notificationService->trigger(
                        'Cart', //module name
                        'PaymentConfirmation', // notification class name before Notification
                        $user->email,
                        ['payment' => $payment, 'customer' => $user, 'order' => $order]
                    );

                    $this->notificationService->trigger(
                        'Customers', //module name
                        'QuestionnaireSend', // notification class
                        $user,
                        [
                            'customer' => $user,
                            'order' => $order,
                        ]
                    );
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'redirect_url' => route('admin.transactions.index')
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ModelsTransaction $transaction)
    {
        $transaction->load([
            'payment.method.method',
            'order.payments.method.method',
            'order.customer',
            'order.vendor',
            'order.items',
        ]);

        $order = $transaction->order;
        $payment = $transaction->payment ?? $order?->payments->sortByDesc('id')->first();
        $paymentMethodName = app(\Modules\Orders\Services\OrderService::class)
            ->getPaymentMethodName($order?->payment_method, $payment);
        $items = $order?->items->map(fn ($item) => [
            'item_id' => $item->item_id,
            'selected_options' => $item->selected_options,
        ])->all() ?? [];
        $optionsByItem = app(\Modules\Orders\Services\OrderItemOptionsService::class)->forItems($items);

        return view('orders::transaction.show', compact('transaction', 'paymentMethodName', 'optionsByItem'));
    }

    /**
     * Display the specified resource.
     */
    public function edit(ModelsTransaction $transaction)
    {
        // Eager load relationships
        $transaction->load([
            'payment.method',
            'order.customer' // Load order with its customer relationship
        ]);

        // Get the payment method from the loaded relationship
        $paymentMethod = $transaction->payment->method ?? null;

        // Get customer details from the order
        $user = $transaction->order->customer ?? null;

        return view('orders::transaction.edit', compact('transaction', 'paymentMethod', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'notify_to_customer' => 'nullable|in:Y,N',
            'status' => 'required|in:completed'
        ]);

        try {
            // Check if there's an existing transaction that's not in a final state
            $existingTransaction = DB::table('transactions')
                ->where('order_id', $request->order_id)
                ->where('transaction_number', $request->transaction_number)
                ->first();

            if ($existingTransaction) {
                DB::transaction(function () use ($request, $existingTransaction) {
                    $status = $request->status;
                    $order = Order::findOrFail($request->order_id);

                    $updateData = [
                        'order_id'         => $request->order_id,
                    ];

                    if ($status === PaymentStatus::COMPLETED->value || $status === PaymentStatus::CAPTURED->value || $status === PaymentStatus::PAID->value) {
                        $updateData['status'] = fn_get_order_status_by_name('COMPLETED');
                        $updateData['payment_status'] = PaymentStatus::COMPLETED->value;
                    } elseif ($status === PaymentStatus::AUTHORIZED->value) {
                        $updateData['status'] = fn_get_order_status_by_name('PROCESSING');
                        $updateData['payment_status'] = PaymentStatus::AUTHORIZED->value;
                    } elseif ($status === PaymentStatus::REFUNDED->value) {
                        $updateData['status'] = fn_get_order_status_by_name('REFUNDED');
                        $updateData['payment_status'] = PaymentStatus::REFUNDED->value;
                    } elseif ($status === PaymentStatus::UNPAID->value) {
                        $updateData['status'] = fn_get_order_status_by_name('PROCESSING');
                        $updateData['payment_status'] = PaymentStatus::UNPAID->value;
                    } elseif ($status === PaymentStatus::PROCESSING->value) {
                        $updateData['status'] = fn_get_order_status_by_name('PROCESSING');
                        $updateData['payment_status'] = PaymentStatus::PROCESSING->value;
                    } elseif ($status === PaymentStatus::FAILED->value) {
                        $updateData['status'] = fn_get_order_status_by_name('FAILED');
                        $updateData['payment_status'] = PaymentStatus::FAILED->value;
                    }

                    $order->update($updateData);
                    $order->save();

                    $payment = Payments::updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'transaction_id' => $existingTransaction->id,
                        ],
                        [
                            'status' => $updateData['payment_status'],
                            'ip_address'         => request()->ip() ?? '',
                            'user_agent'         => request()->userAgent() ?? '',
                        ]
                    );

                    $payment->save();

                    $transaction = TransactionModel::updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'transaction_number' => $existingTransaction->transaction_number,
                        ],
                        [
                            'status' => $updateData['payment_status'],
                            'notes' => 'Transaction updated by ' . Auth::guard('admin')->user()->name . '\n' . $request->notes
                        ]
                    );

                    $transaction->save();

                    if ($request->notify_to_customer == 'Y') {
                        $user = $order->customer;

                        $this->notificationService->trigger(
                            'Cart', //module name
                            'PaymentConfirmation', // notification class name before Notification
                            $user->email,
                            ['payment' => $payment, 'customer' => $user, 'order' => $order]
                        );

                        $this->notificationService->trigger(
                            'Customers', //module name
                            'QuestionnaireSend', // notification class
                            $user,
                            [
                                'customer' => $user,
                                'order' => $order,
                            ]
                        );
                    }
                });
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction updated successfully',
                    'redirect_url' => route('admin.transactions.index')
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction updation failed.',
                'redirect_url' => route('admin.transactions.index')
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function reload(Request $request, string $id)
    {
        try {
            $transaction = ModelsTransaction::findOrFail($id);

            $response = $this->paymentsService->refresh($transaction->order->payment_method, ['transaction_id' => $transaction->id, 'reference_id' => $transaction->gateway_reference]);

            $message = 'Transaction refreshed successfully for order #' . $transaction->order->order_number;
            DB::transaction(function () use ($request, $response, $transaction, $message) {
                $status = $response['payment_status'] ?? 'processing';
                $order = Order::findOrFail($transaction->order_id);

                $updateData = [
                    'order_id'         => $transaction->order_id,
                ];

                if ($status === PaymentStatus::COMPLETED->value || $status === PaymentStatus::CAPTURED->value || $status === PaymentStatus::PAID->value) {
                    $updateData['status'] = fn_get_order_status_by_name('COMPLETED');
                    $updateData['payment_status'] = PaymentStatus::COMPLETED->value;
                } elseif ($status === PaymentStatus::AUTHORIZED->value) {
                    $updateData['status'] = fn_get_order_status_by_name('PROCESSING');
                    $updateData['payment_status'] = PaymentStatus::AUTHORIZED->value;
                } elseif ($status === PaymentStatus::REFUNDED->value) {
                    $updateData['status'] = fn_get_order_status_by_name('REFUNDED');
                    $updateData['payment_status'] = PaymentStatus::REFUNDED->value;
                } elseif ($status === PaymentStatus::UNPAID->value) {
                    $updateData['status'] = fn_get_order_status_by_name('PROCESSING');
                    $updateData['payment_status'] = PaymentStatus::UNPAID->value;
                } elseif ($status === PaymentStatus::PROCESSING->value) {
                    $updateData['status'] = fn_get_order_status_by_name('PROCESSING');
                    $updateData['payment_status'] = PaymentStatus::PROCESSING->value;
                } elseif ($status === PaymentStatus::FAILED->value) {
                    $updateData['status'] = fn_get_order_status_by_name('FAILED');
                    $updateData['payment_status'] = PaymentStatus::FAILED->value;
                }

                $order->update($updateData);
                $order->save();

                $payment = Payments::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'transaction_id' => $transaction->id,
                    ],
                    [
                        'status' => $updateData['payment_status'],
                        'ip_address'         => request()->ip() ?? '',
                        'user_agent'         => request()->userAgent() ?? '',
                    ]
                );

                $payment->save();

                $transaction = TransactionModel::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'transaction_number' => $transaction->transaction_number,
                    ],
                    [
                        'status' => $updateData['payment_status'],
                        'notes' => 'Transaction updated by ' . Auth::guard('admin')->user()->name . '\n' . $request->notes
                    ]
                );

                $transaction->save();

                if ($updateData['payment_status'] == PaymentStatus::COMPLETED->value) {
                    $user = $order->customer;

                    $this->notificationService->trigger(
                        'Cart', //module name
                        'PaymentConfirmation', // notification class name before Notification
                        $user->email,
                        ['payment' => $payment, 'customer' => $user, 'order' => $order]
                    );

                    $this->notificationService->trigger(
                        'Customers', //module name
                        'QuestionnaireSend', // notification class
                        $user,
                        [
                            'customer' => $user,
                            'order' => $order,
                        ]
                    );

                    $message .= ' and payment confirmation sent to customer';
                }
            });

            return redirect()
                ->back()
                ->with('success', $message);

        } catch (\Exception $e) {    
            ApiExceptionHandler::handle($e);
            $message = 'Transaction refreshed failed for order #' . $transaction->order->order_number;
            return redirect()
                ->back()
                ->with('error', $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $transaction = ModelsTransaction::findOrFail($id);
            if (!$this->transactionService->deleteTransaction($transaction)) {
                return response()->json([
                    'success' => false,
                    'errors' => 'This transaction cannot be deleted based on its transaction, payment, or order status.',
                    'message' => 'This transaction cannot be deleted based on its transaction, payment, or order status.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully',
                'redirect_url' => route('admin.transactions.index'),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Unable to delete transaction', ['transaction_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'errors' => 'Unable to delete transaction',
                'redirect_url' => route('admin.transactions.index'),
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:transactions,id',
        ]);

        $deletedCount = 0;
        $skippedCount = 0;
        foreach (array_unique($request->input('ids', [])) as $transactionId) {
            try {
                $transaction = ModelsTransaction::findOrFail($transactionId);
                if ($this->transactionService->deleteTransaction($transaction)) {
                    $deletedCount++;
                } else {
                    $skippedCount++;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Unable to delete transaction', ['transaction_id' => $transactionId, 'error' => $e->getMessage()]);
                $skippedCount++;
            }
        }

        $message = "Deleted {$deletedCount} transaction(s).";
        if ($skippedCount > 0) {
            $message .= " Skipped {$skippedCount} transaction(s) that could not be deleted.";
        }

        return redirect()->route('admin.transactions.index')->with($skippedCount > 0 ? 'error' : 'success', $message);
    }

    /**
     * Update eligible transactions and summarize the entire selection once.
     */
    public function updateStatus(Request $request)
    {
        $transactionStatuses = array_map(fn (TransactionStatus $status) => $status->value, TransactionStatus::cases());
        $request->validate([
            'action' => 'required|string|in:' . implode(',', $transactionStatuses),
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:transactions,id',
        ]);

        $updatedCount = 0;
        $unchangedCount = 0;
        $skippedCount = 0;
        $status = $request->input('action');
        foreach (array_unique($request->input('ids', [])) as $transactionId) {
            try {
                $transaction = ModelsTransaction::findOrFail($transactionId);
                $updated = $this->transactionService->updateStatus($transaction, $status);
                if ($updated->wasChanged('status')) {
                    $updatedCount++;
                } else {
                    $unchangedCount++;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Transaction status update skipped', ['transaction_id' => $transactionId, 'error' => $e->getMessage()]);
                $skippedCount++;
            }
        }

        $message = "Updated {$updatedCount} transaction(s).";
        if ($unchangedCount > 0) {
            $message .= " {$unchangedCount} transaction(s) already had the selected status.";
        }
        if ($skippedCount > 0) {
            $message .= " Skipped {$skippedCount} transaction(s) that could not be updated based on their transaction, payment, or order status.";
        }

        return back()->with($skippedCount > 0 ? 'error' : 'success', $message);
    }

    /**
     * Get transactions analytics
     */
    public function analytics(Request $request)
    {
        try {
            $dateFrom = $request->input('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));

            $dateFrom = Carbon::parse($dateFrom)->startOfDay();
            $dateTo = Carbon::parse($dateTo)->endOfDay();

            $transactionsQuery = ModelsTransaction::whereBetween('created_at', [$dateFrom, $dateTo]);

            $currentAdmin = auth('admin')->user();
            if ($currentAdmin) {
                $restaurant = Restaurant::where('admin_id', $currentAdmin->id)->first();

                if ($restaurant) {
                    $transactionsQuery->whereHas('order', function ($query) use ($restaurant) {
                        $query->where('vendor_type', Restaurant::class)
                            ->where('vendor_id', $restaurant->id);
                    });
                }
            }

            $totalTransactions = $transactionsQuery->count();
            $totalAmount = (clone $transactionsQuery)->sum('amount');
            $completedTransactions = (clone $transactionsQuery)->where('status', TransactionStatus::COMPLETED->value)->count();
            $failedTransactions = (clone $transactionsQuery)->where('status', TransactionStatus::FAILED->value)->count();
            $pendingTransactions = (clone $transactionsQuery)->where('status', TransactionStatus::PENDING->value)->count();
            $refundedTransactions = (clone $transactionsQuery)->where('type', TransactionType::REFUND->value)->count();
            $siteCurrency = fn_get_setting('general.currency', 'USD');

            $successRate = $totalTransactions > 0 ? round(($completedTransactions / $totalTransactions) * 100, 2) : 0;

            // Average, highest, lowest amounts
            $averageAmount = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;
            $highestAmount = (clone $transactionsQuery)->max('amount') ?? 0;
            $lowestAmount = (clone $transactionsQuery)->min('amount') ?? 0;

            // Transactions by status
            $transactionsByStatus = (clone $transactionsQuery)
                ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as amount'))
                ->groupBy('status')
                ->get()
                ->mapWithKeys(function($item) {
                    return [
                        $item->status => [
                            'count' => $item->count,
                            'amount' => $item->amount,
                            'name' => ucfirst(str_replace('_', ' ', $item->status))
                        ]
                    ];
                });

            // Transactions by type
            $transactionsByType = (clone $transactionsQuery)
                ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as amount'))
                ->groupBy('type')
                ->get()
                ->mapWithKeys(function($item) {
                    return [
                        $item->type => [
                            'count' => $item->count,
                            'amount' => $item->amount,
                            'name' => ucfirst(str_replace('_', ' ', $item->type))
                        ]
                    ];
                });

            // Daily trend (last 7 days)
            $dailyTrend = (clone $transactionsQuery)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total_transactions'),
                    DB::raw('SUM(CASE WHEN status = "' . TransactionStatus::COMPLETED->value . '" THEN 1 ELSE 0 END) as completed_transactions'),
                    DB::raw('SUM(amount) as amount')
                )
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(7)
                ->get()
                ->reverse()
                ->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'date_range' => [
                        'from' => $dateFrom->format('Y-m-d'),
                        'to' => $dateTo->format('Y-m-d'),
                    ],
                    'transactions' => [
                        'total' => $totalTransactions,
                        'total_amount' => round($totalAmount, 2),
                        'total_amount_formatted' => fn_convert_currency($totalAmount, $siteCurrency),
                        'completed' => $completedTransactions,
                        'failed' => $failedTransactions,
                        'pending' => $pendingTransactions,
                        'refunded' => $refundedTransactions,
                        'success_rate' => $successRate,
                        'average_amount' => round($averageAmount, 2),
                        'average_amount_formatted' => fn_convert_currency($averageAmount, $siteCurrency),
                        'highest_amount' => round($highestAmount, 2),
                        'highest_amount_formatted' => fn_convert_currency($highestAmount, $siteCurrency),
                        'lowest_amount' => round($lowestAmount, 2),
                        'lowest_amount_formatted' => fn_convert_currency($lowestAmount, $siteCurrency),
                        'by_status' => $transactionsByStatus,
                        'by_type' => $transactionsByType,
                    ],
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
}
