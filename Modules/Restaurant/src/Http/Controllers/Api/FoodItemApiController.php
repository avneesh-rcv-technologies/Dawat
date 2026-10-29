<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Filemanager\Services\FileService;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodOptionGroup;
use Modules\FoodManagements\Models\FoodTag;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantFoodItemService;

class FoodItemApiController extends Controller
{
    public function __construct(protected RestaurantFoodItemService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $filters = $request->only([
            'search',
            'category_id',
            'tag_id',
            'status',
            'food_type',
            'per_page',
            'sort_by',
            'sort_dir',
        ]);

        $items = $this->service->listForRestaurant((int) $restaurant->id, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Food items retrieved successfully.',
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
                'has_more_pages' => $items->hasMorePages(),
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'food_type' => 'required|in:veg,non_veg',
            'quantity_type' => 'required|in:manual,grouping,piece',
            'quantity' => 'required|integer|min:0',

            // Optional - default true
            'is_available' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'in_stock' => 'nullable|boolean',

            'categories' => 'required|array|min:1',
            'categories.*' => 'required|integer|exists:food_categories,id',

            'cost_price' => 'nullable|numeric|min:0',
            'list_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'long_description' => 'nullable|string',

            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:food_tags,id',

            'option_groups' => 'nullable|array',
            'option_groups.*' => 'integer|exists:food_option_groups,id',

            'option_prices' => 'nullable|array',
            'option_prices.*' => 'nullable|numeric|min:0',

            'features' => 'nullable|array',

            'images' => 'nullable|array',
            'images.*' => 'file|image|mimes:jpg,jpeg,png|max:2048',

            'food_image' => 'nullable|file|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Default values
        $data = $request->all();

        $data['is_available'] = $request->input('is_available', true);
        $data['is_active'] = $request->input('is_active', true);
        $data['in_stock'] = $request->input('in_stock', true);

        $categoryIds = $request->input('categories', []);

        $allowedCategoryIds = FoodCategory::query()
            ->where('restaurant_id', $restaurant->id)
            ->pluck('id')
            ->all();

        if (!empty($categoryIds)) {
            $invalidCategoryIds = array_values(
                array_diff(
                    array_map('intval', $categoryIds),
                    $allowedCategoryIds
                )
            );

            if (!empty($invalidCategoryIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some selected categories do not belong to this restaurant.',
                    'invalid_category_ids' => $invalidCategoryIds,
                ], 422);
            }
        }

        if ($request->filled('option_groups')) {
            $optionGroupIds = array_map(
                'intval',
                $request->input('option_groups', [])
            );

            $validOptionGroupIds = FoodOptionGroup::query()
                ->whereIn('id', $optionGroupIds)
                ->pluck('id')
                ->all();

            $missingOptionGroups = array_values(
                array_diff($optionGroupIds, $validOptionGroupIds)
            );

            if (!empty($missingOptionGroups)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some option groups are invalid.',
                    'invalid_option_group_ids' => $missingOptionGroups,
                ], 422);
            }
        }

        $foodItem = $this->service->createForRestaurant(
            (int) $restaurant->id,
            $data,
            $request->allFiles()
        );

        return response()->json([
            'success' => true,
            'message' => 'Food item created successfully.',
            'data' => $foodItem->fresh([
                'categories',
                'tags',
                'optionGroups.options',
                'featureValues',
            ]),
        ], 201);
}
    public function update(Request $request, int $id): JsonResponse
    {
     
        $restaurant = $this->getRestaurantForAuthUser($request);

        $validator = Validator::make($request->all(), [
            // Important fields - ALWAYS required
            'name' => 'required|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'quantity_type' => 'required|in:manual,grouping,piece',
            'food_type' => 'required|in:veg,non_veg',

            // Quantity - required
            'quantity' => 'required|integer|min:0',

            // Status - required
            'in_stock' => 'required|boolean',
            'is_available' => 'required|boolean',
            'is_active' => 'required|boolean',

            // Optional prices
            'cost_price' => 'sometimes|nullable|numeric|min:0',
            'list_price' => 'sometimes|nullable|numeric|min:0',

            // Optional descriptions
            'description' => 'sometimes|nullable|string',
            'long_description' => 'sometimes|nullable|string',

            // Categories - important
            'categories' => 'required|array|min:1',
            'categories.*' => 'required|integer|exists:food_categories,id',

            // Optional tags
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'integer|exists:food_tags,id',

            // Optional option groups
            'option_groups' => 'sometimes|nullable|array',
            'option_groups.*' => 'integer|exists:food_option_groups,id',

            // Optional option prices
            'option_prices' => 'sometimes|nullable|array',
            'option_prices.*' => 'nullable|numeric|min:0',

            // Optional features
            'features' => 'sometimes|nullable|array',

            // Optional images
            'images' => 'sometimes|nullable|array',
            'images.*' => 'file|image|mimes:jpg,jpeg,png|max:2048',

            'food_image' => 'sometimes|nullable|file|image|mimes:jpg,jpeg,png|max:2048',

            'remove_images_id' => 'sometimes|nullable|array',
            'remove_images_id.*' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $removeImageIds = array_values(array_unique(array_filter(array_map('intval', $request->input('remove_images_id', [])))));

            if (!empty($removeImageIds)) {
                $fileService = app(FileService::class);

                foreach ($removeImageIds as $fileLinkId) {
                    $fileService->deleteFileWithId($fileLinkId);
                }
            }

            $item = $this->service->updateForRestaurant((int) $restaurant->id, $id, $request->all(), $request->allFiles());

            return response()->json([
                'success' => true,
                'message' => 'Food item updated successfully.',
                // 'data' => $item,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Food item not found for this restaurant.',
            ], 404);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $this->service->deleteForRestaurant((int) $restaurant->id, $id);

            return response()->json([
                'success' => true,
                'message' => 'Food item deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Food item not found for this restaurant.',
            ], 404);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $item = $this->service->showForRestaurant((int) $restaurant->id, $id);

            return response()->json([
                'success' => true,
                'message' => 'Food item retrieved successfully.',
                'data' => $item,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Food item not found for this restaurant.',
            ], 404);
        }
    }

    protected function getRestaurantForAuthUser(Request $request): Restaurant
    {
        $admin = $request->user('sanctum');

        if (! $admin) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401));
        }

        $restaurant = Restaurant::query()
            ->where('admin_id', $admin->id)
            ->first();

        if (! $restaurant) {
            abort(response()->json([
                'success' => false,
                'message' => 'Restaurant profile not found for this account.',
            ], 404));
        }

        return $restaurant;
    }
}
