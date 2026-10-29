<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantFoodCategoryService;

class FoodCategoryApiController extends Controller
{
    public function __construct(protected RestaurantFoodCategoryService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $filters = $request->only([
            'search',
            'status',
            'food_type',
            'parent_id',
            'per_page',
            'sort_by',
            'sort_dir',
        ]);

        $categories = $this->service->listForRestaurant($restaurant->id, $filters);


        return response()->json([
            'success' => true,
            'message' => 'Food categories retrieved successfully.',
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'last_page' => $categories->lastPage(),
                'has_more_pages' => $categories->hasMorePages(),
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('food_categories', 'name')
                        ->where(
                            fn($query) => $query->where(
                                'restaurant_id',
                                $restaurant->id
                            )
                        ),
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'food_type' => [
                    'required',
                    'in:all,veg,non_veg,vegan',
                ],

                'parent_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('food_categories', 'id')
                        ->where(
                            fn($query) => $query->where(
                                'restaurant_id',
                                $restaurant->id
                            )
                        ),
                ],
                'status' => [
                    'nullable',
                    'boolean',
                    'deafult:true',
                ],
                'image' => [
                    'nullable',
                    'file',
                    'image',
                    'mimes:jpg,jpeg,png',
                    'max:2048',
                ],
            ],
            [
                'name.required' => 'Category name is required.',
                'name.string' => 'Category name must be a string.',
                'name.max' => 'Category name may not be greater than 255 characters.',
                'name.unique' => 'This category name already exists.',

                'slug.string' => 'Slug must be a string.',
                'slug.max' => 'Slug may not be greater than 255 characters.',
                'slug.unique' => 'This slug already exists.',

                'description.string' => 'Description must be a string.',
                'description.max' => 'Description may not be greater than 500 characters.',

                'food_type.required' => 'Food type is required.',
                'food_type.in' => 'Food type must be all, veg, non_veg, or vegan.',

                'parent_id.integer' => 'Parent category ID must be an integer.',
                'parent_id.exists' => 'The selected parent category is invalid.',

                'status.boolean' => 'Status must be true or false.',

                'image.file' => 'The image must be a valid file.',
                'image.image' => 'The uploaded file must be an image.',
                'image.mimes' => 'The image must be a JPG, JPEG, or PNG file.',
                'image.max' => 'The image may not be larger than 2 MB.',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $validated['status'] = $request->boolean('status', true);
        /*
        |--------------------------------------------------------------------------
        | Validate Parent Category
        |--------------------------------------------------------------------------
        */
        if (!empty($validated['parent_id'])) {

            $parentCategory = $this->service->showForRestaurant(
                $restaurant->id,
                (int) $validated['parent_id']
            );

            if ($parentCategory->parent_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only root categories can be used as parent categories.',
                    'errors' => [
                        'parent_id' => [
                            'Only root categories can be used as parent categories.',
                        ],
                    ],
                ], 422);
            }
        }
        /*
        |--------------------------------------------------------------------------
        | Create Category
        |--------------------------------------------------------------------------
        */
        $data=$this->service->createForRestaurant(
            $restaurant->id,
            $validated
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Food category created successfully.',
            'data' => $data,
        ], 201);
    }


    public function show(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $category = $this->service->showForRestaurant($restaurant->id, $id);

            return response()->json([
                'success' => true,
                'message' => 'Food category retrieved successfully.',
                'data' => $category,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food category not found.');
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'status' => ['required', 'boolean'],
            ])->validate();

            $category = $this->service->updateStatusForRestaurant($restaurant->id, $id, (bool) $validated['status']);

            return response()->json([
                'success' => true,
                'message' => 'Food category status updated successfully.',
                // 'data' => $category,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food category not found.');
        }
    }

    public function activeParentCategoryLists(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $filters = $request->only([
            'search',
            'food_type',
        ]);

        $categories = $this->service->listActiveParentCategoriesForRestaurant($restaurant->id, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Active parent categories retrieved successfully.',
            'data' => $categories,
        ], 200);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('food_categories', 'name')->ignore($id)->where(fn($query) => $query->where('restaurant_id', $restaurant->id)),
                ],
                'slug' => ['prohibited'],
                'description' => ['sometimes', 'nullable', 'string', 'max:500'],
                'food_type' => ['sometimes', 'in:all,veg,non_veg,vegan'],
                'parent_id' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    Rule::exists('food_categories', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurant->id)),
                ],
                'status' => ['sometimes', 'boolean'],
                'image' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            ])->validate();

            if (array_key_exists('parent_id', $validated)) {
                if (!empty($validated['parent_id'])) {
                    $parentCategory = $this->service->showForRestaurant($restaurant->id, (int) $validated['parent_id']);
                    if ($parentCategory->parent_id !== null) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only root categories can be used as parent categories.',
                        ], 422);
                    }
                }
            }

            if ($id === ($validated['parent_id'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A category cannot be assigned as its own parent.',
                ], 422);
            }

            $category = $this->service->updateForRestaurant($restaurant->id, $id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Food category updated successfully.',
                'data' => $category,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food category not found.');
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $deleted = $this->service->deleteForRestaurant($restaurant->id, $id);

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'Food category deleted successfully.' : 'Food category could not be deleted.',
            ], $deleted ? 200 : 404);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food category not found.');
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $validated = Validator::make($request->all(), [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:food_categories,id'],
        ], [
            'ids.required' => 'Please select categories for delete.',
            'ids.min' => 'Please select categories for delete.',
        ])->validate();

        $deletedCount = $this->service->bulkDeleteForRestaurant($restaurant->id, $validated['ids']);

        return response()->json([
            'success' => true,
            'message' => $deletedCount > 0
                ? 'Selected food categories deleted successfully.'
                : 'No food categories were deleted.',
            'deleted_count' => $deletedCount,
        ], 200);
    }

    public function uploadCategoryImage(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            ])->validate();

            $category = $this->service->uploadCategoryImage($restaurant->id, $id, $validated['image']);

            return response()->json([
                'success' => true,
                'message' => 'Food category image uploaded successfully.',
                // 'data' => $category,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food category not found.');
        }
    }

    public function removeCategoryImage(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $result = $this->service->removeCategoryImage($restaurant->id, $id);

            $removed = $result['deleted'] ?? false;

            return response()->json([
                'success' => $removed,
                'message' => $result['message'] ?? 'Food category image could not be removed.',
            ], $removed ? 200 : ($result['message'] === 'Image not found to delete.' ? 404 : 400));
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food category not found.');
        }
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

        if (!$admin) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401));
        }

        $restaurant = Restaurant::query()
            ->where('admin_id', $admin->id)
            ->first();

        if (!$restaurant) {
            abort(response()->json([
                'success' => false,
                'message' => 'Restaurant profile not found for this account.',
            ], 404));
        }

        return $restaurant;
    }
}
