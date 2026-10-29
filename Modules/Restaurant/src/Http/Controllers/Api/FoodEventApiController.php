<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantFoodEventService;

class FoodEventApiController extends Controller
{
    public function __construct(protected RestaurantFoodEventService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $filters = $request->only([
            'search',
            'status',
            'per_page',
            'sort_by',
            'sort_dir',
        ]);

        $events = $this->service->listForRestaurant($restaurant->id, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Food events retrieved successfully.',
            'data' => $events->items(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'last_page' => $events->lastPage(),
                'has_more_pages' => $events->hasMorePages(),
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('food_events', 'slug'),
                ],
                'description' => 'nullable|string|max:1000',
                'status' => 'nullable|boolean',
                'food_categories' => 'nullable|array',
                'food_categories.*' => 'integer|exists:food_categories,id',
                'food_items' => 'nullable|array',
                'food_items.*' => 'integer|exists:food_items,id',
            ])->validate();

            $event = $this->service->createForRestaurant($restaurant->id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Food event created successfully.',
                'data' => $event,
            ], 201);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food event not found.');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to create food event.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $event = $this->service->showForRestaurant($restaurant->id, $id);

            return response()->json([
                'success' => true,
                'message' => 'Food event retrieved successfully.',
                'data' => $event->load(['foodCategories', 'foodItems.categories']),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food event not found.');
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'slug' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('food_events', 'slug')->ignore($id),
                ],
                'description' => 'sometimes|nullable|string|max:1000',
                'status' => 'sometimes|nullable|boolean',
                'food_categories' => 'sometimes|nullable|array',
                'food_categories.*' => 'integer|exists:food_categories,id',
                'food_items' => 'sometimes|nullable|array',
                'food_items.*' => 'integer|exists:food_items,id',
            ])->validate();

            $event = $this->service->updateForRestaurant($restaurant->id, $id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Food event updated successfully.',
                'data' => $event,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food event not found.');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update food event.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $deleted = $this->service->deleteForRestaurant($restaurant->id, $id);

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'Food event deleted successfully.' : 'Food event could not be deleted.',
            ], $deleted ? 200 : 404);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food event not found.');
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer'],
            ])->validate();

            $deletedCount = $this->service->bulkDeleteForRestaurant($restaurant->id, $validated['ids']);

            return response()->json([
                'success' => true,
                'message' => 'Selected food events deleted successfully.',
                'deleted_count' => $deletedCount,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to delete selected food events.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function toggleStatus(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer'],
                'status' => ['required', 'boolean'],
            ])->validate();

            $updatedCount = $this->service->toggleStatusForRestaurant($restaurant->id, $validated['ids'], (bool) $validated['status']);

            return response()->json([
                'success' => true,
                'message' => 'Food event status updated successfully.',
                'updated_count' => $updatedCount,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update food event status.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function getEventItems(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $items = $this->service->getRestaurantEventItems($restaurant->id, $id);

            return response()->json([
                'success' => true,
                'message' => 'Food event items retrieved successfully.',
                'data' => $items,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food event not found.');
        }
    }

    public function getItemsList(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $filters = $request->only([
            'search',
            'status',
            'per_page',
            'sort_by',
            'sort_dir',
        ]);

        $items = $this->service->getItemsListForRestaurant($restaurant->id, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Restaurant food item list retrieved successfully.',
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

    public function getEventCategories(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $categories = $this->service->getRestaurantEventCategories($restaurant->id, $id);

            return response()->json([
                'success' => true,
                'message' => 'Food event categories retrieved successfully.',
                'data' => $categories,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food event not found.');
        }
    }

    public function updateItems(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'food_items' => 'nullable|array',
                'food_items.*' => 'integer|exists:food_items,id',
            ])->validate();

            $event = $this->service->showForRestaurant($restaurant->id, $id);
            $this->service->updateForRestaurant($restaurant->id, $id, [
                'food_items' => $validated['food_items'] ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Food event items updated successfully.',
                'data' => $event->fresh(['foodItems.categories', 'foodCategories']),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food event not found.');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update food event items.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateCategories(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'food_categories' => 'nullable|array',
                'food_categories.*' => 'integer|exists:food_categories,id',
            ])->validate();

            $event = $this->service->showForRestaurant($restaurant->id, $id);
            $this->service->updateForRestaurant($restaurant->id, $id, [
                'food_categories' => $validated['food_categories'] ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Food event categories updated successfully.',
                'data' => $event->fresh(['foodItems.categories', 'foodCategories']),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food event not found.');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update food event categories.',
                'error' => $e->getMessage(),
            ], 422);
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
