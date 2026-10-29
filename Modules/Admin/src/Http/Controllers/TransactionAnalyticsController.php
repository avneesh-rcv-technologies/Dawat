<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Admin\Services\AnalyticsService;
use Modules\Orders\Models\Transaction;

class TransactionAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Show transaction detail page with analytics
     */
    public function show($transactionId)
    {
        $transaction = Transaction::with(['order.customer', 'order.vendor', 'payment'])->findOrFail($transactionId);
        
        $analytics = $this->analyticsService->getTransactionAnalytics($transactionId);

        return view('admin::pages.analytics.transaction-detail', compact('transaction', 'analytics'));
    }
}
