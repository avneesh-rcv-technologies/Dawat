<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Admin\Services\AnalyticsService;
use Modules\Restaurant\Services\RestaurantDataScope;
use Modules\Orders\Models\Order;

class OrderAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Show order detail page with analytics
     */
    public function show($orderId)
    {
        abort_unless(app(RestaurantDataScope::class)->canAccessOrder($orderId), 404);
        $order = Order::with(['customer', 'vendor', 'items', 'payments', 'transactions'])->findOrFail($orderId);
        
        $analytics = $this->analyticsService->getOrderAnalytics($orderId);

        return view('admin::pages.analytics.order-detail', compact('order', 'analytics'));
    }
}
