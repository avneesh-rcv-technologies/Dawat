<?php

namespace Modules\FoodManagements\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\FoodManagements\Services\CategoryService;
use Modules\FoodManagements\Services\FoodItemService;
use Modules\FoodManagements\Services\FoodTagService;
use Modules\FoodManagements\Services\FoodOptionGroupService;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class MenuApiController extends Controller
{
    protected CategoryService $categoryService;
    protected FoodItemService $foodItemService;
    protected FoodTagService $tagService;
    protected FoodOptionGroupService $optionGroupService;

    public function __construct(
        CategoryService $categoryService,
        FoodItemService $foodItemService,
        FoodTagService $tagService,
        FoodOptionGroupService $optionGroupService
    ) {
        $this->categoryService = $categoryService;
        $this->foodItemService = $foodItemService;
        $this->tagService = $tagService;
        $this->optionGroupService = $optionGroupService;
    }

    /**
     * Get active root categories with subcategories
     * GET /api/menu/categories?restaurant_id=1
     */
    public function getCategories(Request $request): JsonResponse
    {
        $request->validate([
            'restaurant_id' => 'nullable|integer|exists:restaurants,id',
        ]);

        try {
            $categories = $this->categoryService->getActiveCategoriesWithSubcategories(
                $request->integer('restaurant_id') ?: null
            );

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Categories retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Get food items by category (including subcategories)
     * GET /api/menu/categories/{categoryId}/items
     */
    public function getFoodItemsByCategory(Request $request, int $categoryId): JsonResponse
    {
        $request->validate([
            'restaurant_id' => 'nullable|integer|exists:restaurants,id',
            'food_type'     => 'nullable|in:all,veg,non_veg,vegan',
            'min_price'     => 'nullable|numeric|min:0',
            'max_price'     => 'nullable|numeric|min:0',
            'tags'          => 'nullable|array',
            'tags.*'        => 'integer|exists:food_tags,id',
            'variant_id'    => 'nullable|integer|exists:food_feature_variants,variant_id',
            'page'          => 'nullable|integer|min:1',
            'per_page'      => 'nullable|integer|min:1|max:50',
        ]);

        try {
            $filters = [
                'restaurant_id' => $request->integer('restaurant_id') ?: null,
                'food_type'     => $request->get('food_type'),
                'min_price'     => $request->get('min_price'),
                'max_price'     => $request->get('max_price'),
                'tags'          => $request->get('tags', []),
                'variant_id'    => $request->integer('variant_id') ?: null,
            ];

            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);

            $result = $this->foodItemService->getFoodItemsByCategory($categoryId, $filters, $perPage, $page);
            // dd($result);

            return response()->json([
                'success' => true,
                'data' => $result['items'],
                'meta' => $result['meta'],
                'message' => 'Food items retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Get food item details with all relations
     * GET /api/menu/items/{itemId}
     */
    public function getFoodItemDetails(int $itemId): JsonResponse
    {
        try {
            $item = $this->foodItemService->getFoodItemWithDetails($itemId);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Food item details retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Get food items by tag
     * GET /api/menu/tags/{tagId}/items
     */
    public function getFoodItemsByTag(Request $request, int $tagId): JsonResponse
    {
        $request->validate([
            'food_type' => 'nullable|in:all,veg,non_veg,vegan',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        try {
            $filters = [
                'food_type' => $request->get('food_type'),
                'min_price' => $request->get('min_price'),
                'max_price' => $request->get('max_price')
            ];

            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);

            $result = $this->foodItemService->getFoodItemsByTag($tagId, $filters, $perPage, $page);

            return response()->json([
                'success' => true,
                'data' => $result['items'],
                'meta' => $result['meta'],
                'message' => 'Food items retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Get option groups for cart screen
     * GET /api/menu/option-groups
     */
    public function getOptionGroups(Request $request): JsonResponse
    {
        $request->validate([
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'integer|exists:food_items,id'
        ]);

        try {
            $itemIds = $request->get('item_ids', []);
            $optionGroups = $this->optionGroupService->getOptionGroupsForCart($itemIds);

            return response()->json([
                'success' => true,
                'data' => $optionGroups,
                'message' => 'Option groups retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Combined menu API optimized for frontend
     * GET /api/menu/complete
     */
    public function getCompleteMenu(Request $request): JsonResponse
    {
        $request->validate([
            'food_type' => 'nullable|in:all,veg,non_veg,vegan',
            'include_items' => 'nullable|boolean'
        ]);

        try {
            $foodType = $request->get('food_type');
            $includeItems = $request->get('include_items', false);

            $menu = $this->categoryService->getCompleteMenu($foodType, $includeItems);

            return response()->json([
                'success' => true,
                'data' => $menu,
                'message' => 'Complete menu retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }
}