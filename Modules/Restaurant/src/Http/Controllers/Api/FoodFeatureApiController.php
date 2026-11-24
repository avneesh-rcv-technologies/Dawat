<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\FoodManagements\Models\FoodFeature;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantFoodFeatureService;

class FoodFeatureApiController extends Controller
{
    public function __construct(protected RestaurantFoodFeatureService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $filters = $request->only([
            'search',
            'group_id',
            'status',
            'purpose',
            'category_id',
            'per_page',
            'sort_by',
            'sort_dir',
        ]);

        $features = $this->service->listForRestaurant($restaurant->id, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Food features retrieved successfully.',
            'data' => $features->items(),
            'meta' => [
                'current_page' => $features->currentPage(),
                'per_page' => $features->perPage(),
                'total' => $features->total(),
                'last_page' => $features->lastPage(),
                'has_more_pages' => $features->hasMorePages(),
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $validated = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('food_feature_descriptions', 'name')->whereIn('feature_id', FoodFeature::forRestaurant($restaurant->id)->pluck('feature_id'))->where(fn ($query) => $query->where('lang_code', 'en')),
            ],
            'description' => ['nullable', 'string'],
            'feature_code' => [
                'nullable',
                'string',
                'max:32',
                Rule::unique('food_features', 'feature_code')->where('restaurant_id', $restaurant->id),
            ],
            'parent_id' => ['nullable', 'integer', Rule::exists('food_features', 'feature_id')->where('restaurant_id', $restaurant->id)->where('feature_type', 'G')],
            'purpose' => ['nullable', 'string', 'max:32'],
            'feature_style' => ['nullable', 'string', 'max:32'],
            'filter_style' => ['nullable', 'string', 'max:32'],
            'feature_type' => ['required', 'string', Rule::in(array_keys(RestaurantFoodFeatureService::TYPES))],
            'status' => ['nullable', 'string', 'in:A,H,D'],
            'position' => ['nullable', 'integer', 'min:0'],
            'display_on_product' => ['nullable', 'boolean'],
            'display_on_catalog' => ['nullable', 'boolean'],
            'display_on_header' => ['nullable', 'boolean'],
            'comparison' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', Rule::exists('food_categories', 'id')->where('restaurant_id', $restaurant->id)],
            'variants' => ['sometimes', 'array', 'list', 'max:50'],
            'variants.*.variant_id' => ['prohibited'],
            'variants.*.description' => ['required', 'string', 'max:255'],
            'variants.*.position' => ['nullable', 'integer', 'min:0'],
            'variants.*.status' => ['nullable', 'string', 'in:A,D'],
        ], [
            'name.required' => 'Please enter a feature name.',
            'name.unique' => 'This feature name already exists.',
            'feature_code.unique' => 'This feature code already exists.',
        ])->validate();

        $feature = $this->service->createForRestaurant($restaurant->id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Food feature created successfully.',
            'data' => $feature,
        ], 201);
    }

    /** Active features with their options, for building the food item form in the app. */
    public function activeLists(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        return response()->json([
            'success' => true,
            'message' => 'Active food features retrieved successfully.',
            'data' => $this->service->activeListsForRestaurant($restaurant->id),
        ], 200);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'status' => ['required', 'boolean'],
            ])->validate();

            $feature = $this->service->updateStatusForRestaurant($restaurant->id, $id, (bool) $validated['status']);

            return response()->json([
                'success' => true,
                'message' => 'Food feature status updated successfully.',
                'data' => $feature,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food feature not found.');
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $feature = $this->service->showForRestaurant($restaurant->id, $id);

            return response()->json([
                'success' => true,
                'message' => 'Food feature retrieved successfully.',
                'data' => $feature,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food feature not found.');
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('food_feature_descriptions', 'name')->whereIn('feature_id', FoodFeature::forRestaurant($restaurant->id)->pluck('feature_id'))
                        ->where(fn ($query) => $query->where('lang_code', 'en'))
                        ->ignore($id, 'feature_id'),
                ],
                'description' => ['sometimes', 'nullable', 'string'],
                'feature_code' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:32',
                    Rule::unique('food_features', 'feature_code')->where('restaurant_id', $restaurant->id)->ignore($id, 'feature_id'),
                ],
                'parent_id' => ['sometimes', 'nullable', 'integer', Rule::exists('food_features', 'feature_id')->where('restaurant_id', $restaurant->id)->where('feature_type', 'G')],
                'purpose' => ['sometimes', 'nullable', 'string', 'max:32'],
                'feature_style' => ['sometimes', 'nullable', 'string', 'max:32'],
                'filter_style' => ['sometimes', 'nullable', 'string', 'max:32'],
                'feature_type' => ['sometimes', 'required', 'string', Rule::in(array_keys(RestaurantFoodFeatureService::TYPES))],
                'status' => ['sometimes', 'nullable', 'string', 'in:A,H,D'],
                'position' => ['sometimes', 'nullable', 'integer', 'min:0'],
                'display_on_product' => ['sometimes', 'nullable', 'boolean'],
                'display_on_catalog' => ['sometimes', 'nullable', 'boolean'],
                'display_on_header' => ['sometimes', 'nullable', 'boolean'],
                'comparison' => ['sometimes', 'nullable', 'boolean'],
                'category_ids' => ['sometimes', 'nullable', 'array'],
                'category_ids.*' => ['integer', Rule::exists('food_categories', 'id')->where('restaurant_id', $restaurant->id)],
                'variants' => ['sometimes', 'array', 'list', 'max:50'],
                'variants.*.variant_id' => ['nullable', 'integer', 'distinct', Rule::exists('food_feature_variants', 'variant_id')->where('feature_id', $id)],
                'variants.*.description' => ['required', 'string', 'max:255'],
                'variants.*.position' => ['nullable', 'integer', 'min:0'],
                'variants.*.status' => ['nullable', 'string', 'in:A,D'],
            ], [
                'name.required' => 'Please enter a feature name.',
                'name.unique' => 'This feature name already exists.',
                'feature_code.unique' => 'This feature code already exists.',
            ])->validate();

            $feature = $this->service->updateForRestaurant($restaurant->id, $id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Food feature updated successfully.',
                'data' => $feature,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food feature not found.');
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $deleted = $this->service->deleteForRestaurant($restaurant->id, $id);

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'Food feature deleted successfully.' : 'Food feature could not be deleted.',
            ], $deleted ? 200 : 404);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food feature not found.');
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $validated = Validator::make($request->all(), [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', Rule::exists('food_features', 'feature_id')->where('restaurant_id', $restaurant->id)],
        ], [
            'ids.required' => 'Please select food features for delete.',
            'ids.min' => 'Please select food features for delete.',
        ])->validate();

        $deletedCount = $this->service->bulkDeleteForRestaurant($restaurant->id, $validated['ids']);

        return response()->json([
            'success' => true,
            'message' => $deletedCount > 0 ? 'Selected food features deleted successfully.' : 'No food features were deleted.',
            'deleted_count' => $deletedCount,
        ], 200);
    }

    protected function notFoundResponse(string $message = 'Data not found.'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 404);
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
