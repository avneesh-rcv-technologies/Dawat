<?php

namespace Modules\FoodManagements\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\FoodManagements\Services\FeaturedItemService;

class FeaturedItemsApiController extends Controller
{
    protected FeaturedItemService $service;

    public function __construct(FeaturedItemService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all active featured items
     */
    public function index()
    {
        try {
            $featuredItems = $this->service->getActiveFeaturedItems();

            return response()->json([
                'success' => true,
                'message' => 'Featured items retrieved successfully',
                'data' => $featuredItems,
                'meta' => [
                    'total_count' => $featuredItems->count(),
                    'food_items_count' => $featuredItems->where('type', 'food_item')->count(),
                    'categories_count' => $featuredItems->where('type', 'category')->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve featured items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured food items only
     */
    public function getFoodItems()
    {
        try {
            $featuredFoodItems = $this->service->getFeaturedFoodItems();

            return response()->json([
                'success' => true,
                'message' => 'Featured food items retrieved successfully',
                'data' => $featuredFoodItems,
                'meta' => [
                    'total_count' => $featuredFoodItems->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve featured food items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured categories only
     */
    public function getCategories()
    {
        try {
            $featuredCategories = $this->service->getFeaturedCategories();

            return response()->json([
                'success' => true,
                'message' => 'Featured categories retrieved successfully',
                'data' => $featuredCategories,
                'meta' => [
                    'total_count' => $featuredCategories->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve featured categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific featured item by ID
     */
    public function show(int $id)
    {
        try {
            $featuredItem = $this->service->getFeaturedItemForDisplay($id);

            return response()->json([
                'success' => true,
                'message' => 'Featured item retrieved successfully',
                'data' => $featuredItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Featured item not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}