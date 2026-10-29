<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantFoodFeatureGroupService;

class FoodFeatureGroupApiController extends Controller
{
    public function __construct(protected RestaurantFoodFeatureGroupService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        $filters = $request->only([
            'search',
            'status',
            'category_id',
            'per_page',
            'sort_by',
            'sort_dir',
        ]);

        $groups = $this->service->listForRestaurant($filters);

        return response()->json([
            'success' => true,
            'message' => 'Feature groups retrieved successfully.',
            'data' => $groups->items(),
            'meta' => [
                'current_page' => $groups->currentPage(),
                'per_page' => $groups->perPage(),
                'total' => $groups->total(),
                'last_page' => $groups->lastPage(),
                'has_more_pages' => $groups->hasMorePages(),
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        $validated = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('food_feature_descriptions', 'name')
                    ->where(fn ($query) => $query->where('lang_code', 'en')),
            ],
            'description' => ['nullable', 'string'],
            'feature_code' => [
                'nullable',
                'string',
                'max:32',
                Rule::unique('food_features', 'feature_code')->where(fn ($query) => $query->where('feature_type', 'G')),
            ],
            'status' => ['nullable', 'string', 'in:A,H,D'],
            'position' => ['nullable', 'integer', 'min:0'],
            'display_on_product' => ['nullable', 'boolean'],
            'display_on_catalog' => ['nullable', 'boolean'],
            'display_on_header' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:food_categories,id'],
        ], [
            'name.required' => 'Please enter a feature group name.',
            'name.unique' => 'This feature group name already exists.',
            'feature_code.unique' => 'This feature code already exists.',
        ])->validate();

        $group = $this->service->createForRestaurant($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feature group created successfully.',
            'data' => $group,
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        try {
            $group = $this->service->showForRestaurant($id);

            return response()->json([
                'success' => true,
                'message' => 'Feature group retrieved successfully.',
                'data' => $group,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Feature group not found.');
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('food_feature_descriptions', 'name')
                        ->where(fn ($query) => $query->where('lang_code', 'en'))
                        ->ignore($id, 'feature_id'),
                ],
                'description' => ['sometimes', 'nullable', 'string'],
                'feature_code' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:32',
                    Rule::unique('food_features', 'feature_code')
                        ->where(fn ($query) => $query->where('feature_type', 'G'))
                        ->ignore($id),
                ],
                'status' => ['sometimes', 'nullable', 'string', 'in:A,H,D'],
                'position' => ['sometimes', 'nullable', 'integer', 'min:0'],
                'display_on_product' => ['sometimes', 'nullable', 'boolean'],
                'display_on_catalog' => ['sometimes', 'nullable', 'boolean'],
                'display_on_header' => ['sometimes', 'nullable', 'boolean'],
                'category_ids' => ['sometimes', 'nullable', 'array'],
                'category_ids.*' => ['integer', 'exists:food_categories,id'],
            ], [
                'name.required' => 'Please enter a feature group name.',
                'name.unique' => 'This feature group name already exists.',
                'feature_code.unique' => 'This feature code already exists.',
            ])->validate();

            $group = $this->service->updateForRestaurant($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Feature group updated successfully.',
                'data' => $group,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Feature group not found.');
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        try {
            $deleted = $this->service->deleteForRestaurant($id);

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'Feature group deleted successfully.' : 'Feature group could not be deleted.',
            ], $deleted ? 200 : 404);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Feature group not found.');
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        $validated = Validator::make($request->all(), [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:food_features,feature_id'],
        ], [
            'ids.required' => 'Please select feature groups for delete.',
            'ids.min' => 'Please select feature groups for delete.',
        ])->validate();

        $deletedCount = $this->service->bulkDeleteForRestaurant($validated['ids']);

        return response()->json([
            'success' => true,
            'message' => $deletedCount > 0 ? 'Selected feature groups deleted successfully.' : 'No feature groups were deleted.',
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
