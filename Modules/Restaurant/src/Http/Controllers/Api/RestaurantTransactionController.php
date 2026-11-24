<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use Modules\Restaurant\Http\Requests\RestaurantTransactionFilters;
use Modules\Restaurant\Http\Resources\RestaurantTransactionResource;
use Modules\Restaurant\Services\RestaurantOrderService;
use Modules\Restaurant\Services\RestaurantTransactionService;

class RestaurantTransactionController
{
    public function __construct(private RestaurantTransactionService $service, private RestaurantOrderService $orders) {}

    public function index(RestaurantTransactionFilters $request)
    {
        $restaurant = $this->orders->restaurantForUser($request->user());
        $filters = $request->validated();
        $page = $this->service->listing($restaurant, $filters);

        return response()->json([
            'success' => true, 'message' => 'Transactions retrieved successfully.',
            'data' => $page->getCollection()->map(fn ($transaction) => (new RestaurantTransactionResource($transaction))->resolve($request)),
            'meta' => [
                'current_page' => $page->currentPage(), 'per_page' => $page->perPage(),
                'total' => $page->total(), 'last_page' => $page->lastPage(), 'has_more_pages' => $page->hasMorePages(),
            ],
            'filters' => $filters,
        ])->header('Cache-Control', 'private, no-store');
    }

    public function kpis(RestaurantTransactionFilters $request)
    {
        $restaurant = $this->orders->restaurantForUser($request->user());
        $filters = $request->validated();

        return response()->json([
            'success' => true, 'message' => 'Transaction KPIs retrieved successfully.',
            'data' => $this->service->kpis($restaurant, $filters), 'filters' => $filters,
        ])->header('Cache-Control', 'private, no-store');
    }
}
