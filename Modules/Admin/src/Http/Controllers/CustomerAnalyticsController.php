<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Services\AnalyticsService;
use Modules\Acl\Models\Customer;

class CustomerAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Show customer detail page with analytics
     */
    public function show(Request $request, $customerId)
    {
        $customer = Customer::findOrFail($customerId);
        
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $analytics = $this->analyticsService->getCustomerAnalytics($customerId, $dateFrom, $dateTo);

        return view('admin::pages.analytics.customer-detail', compact('customer', 'analytics', 'dateFrom', 'dateTo'));
    }
}
