<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Services\AnalyticsService;
use Modules\Restaurant\Models\Restaurant;

class RestaurantAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Show restaurant detail page with analytics
     */
    public function show(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $analytics = $this->analyticsService->getRestaurantAnalytics($restaurantId, $dateFrom, $dateTo);

        return view('admin::pages.analytics.restaurant-detail', compact('restaurant', 'analytics', 'dateFrom', 'dateTo'));
    }
}
