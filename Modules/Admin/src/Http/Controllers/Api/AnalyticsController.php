<?php

namespace Modules\Admin\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Admin\Services\AnalyticsService;
use Illuminate\Routing\Controller;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get restaurant analytics
     * 
     * @param Request $request
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function restaurantAnalytics(Request $request, $restaurantId): JsonResponse
    {
        try {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            $analytics = $this->analyticsService->getRestaurantAnalytics($restaurantId, $dateFrom, $dateTo);

            return response()->json([
                'success' => true,
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch restaurant analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer analytics
     * 
     * @param Request $request
     * @param int $customerId
     * @return JsonResponse
     */
    public function customerAnalytics(Request $request, $customerId): JsonResponse
    {
        try {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            $analytics = $this->analyticsService->getCustomerAnalytics($customerId, $dateFrom, $dateTo);

            return response()->json([
                'success' => true,
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customer analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order analytics
     * 
     * @param int $orderId
     * @return JsonResponse
     */
    public function orderAnalytics($orderId): JsonResponse
    {
        try {
            $analytics = $this->analyticsService->getOrderAnalytics($orderId);

            return response()->json([
                'success' => true,
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get transaction analytics
     * 
     * @param int $transactionId
     * @return JsonResponse
     */
    public function transactionAnalytics($transactionId): JsonResponse
    {
        try {
            $analytics = $this->analyticsService->getTransactionAnalytics($transactionId);

            return response()->json([
                'success' => true,
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
