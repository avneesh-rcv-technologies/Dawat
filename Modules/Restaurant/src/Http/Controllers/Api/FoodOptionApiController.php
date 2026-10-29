<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantFoodOptionService;

class FoodOptionApiController extends Controller
{
    public function __construct(protected RestaurantFoodOptionService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        $filters = $request->only([
            'search',
            'is_required',
            'min',
            'max',
            'per_page',
            'sort_by',
            'sort_dir',
        ]);

        $groups = $this->service->listForRestaurant($filters);

        return response()->json([
            'success' => true,
            'message' => 'Food option groups retrieved successfully.',
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
            'name' => ['required', 'string', 'max:255'],
            'is_required' => ['nullable', 'boolean'],
            'min' => ['nullable', 'integer', 'min:0'],
            'max' => ['nullable', 'integer', 'min:1'],
        ], [
            'name.required' => 'Please enter an option group name.',
        ])->validate();

        if (isset($validated['min'], $validated['max']) && (int) $validated['max'] < (int) $validated['min']) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum selection cannot be smaller than minimum selection.',
            ], 422);
        }

        $group = $this->service->createForRestaurant($validated);

        return response()->json([
            'success' => true,
            'message' => 'Option group created successfully.',
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
                'message' => 'Option group retrieved successfully.',
                'data' => $group,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Option group not found.');
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'is_required' => ['sometimes', 'nullable', 'boolean'],
                'min' => ['sometimes', 'nullable', 'integer', 'min:0'],
                'max' => ['sometimes', 'nullable', 'integer', 'min:1'],
            ], [
                'name.required' => 'Please enter an option group name.',
            ])->validate();

            if (isset($validated['min'], $validated['max']) && (int) $validated['max'] < (int) $validated['min']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum selection cannot be smaller than minimum selection.',
                ], 422);
            }

            $group = $this->service->updateForRestaurant($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Option group updated successfully.',
                'data' => $group,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Option group not found.');
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        try {
            $deleted = $this->service->deleteForRestaurant($id);

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'Option group deleted successfully.' : 'Option group could not be deleted.',
            ], $deleted ? 200 : 404);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Option group not found.');
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        $validated = Validator::make($request->all(), [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:food_option_groups,id'],
        ], [
            'ids.required' => 'Please select option groups for delete.',
            'ids.min' => 'Please select option groups for delete.',
        ])->validate();

        $deletedCount = $this->service->bulkDeleteForRestaurant($validated['ids']);

        return response()->json([
            'success' => true,
            'message' => $deletedCount > 0 ? 'Selected option groups deleted successfully.' : 'No option groups were deleted.',
            'deleted_count' => $deletedCount,
        ], 200);
    }

    public function listOptions(Request $request, int $groupId): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        try {
            $filters = $request->only([
                'search',
                'is_active',
                'per_page',
                'sort_by',
                'sort_dir',
            ]);

            $options = $this->service->listOptionsForGroup($groupId, $filters);

            return response()->json([
                'success' => true,
                'message' => 'Option group options retrieved successfully.',
                'data' => $options->items(),
                'meta' => [
                    'current_page' => $options->currentPage(),
                    'per_page' => $options->perPage(),
                    'total' => $options->total(),
                    'last_page' => $options->lastPage(),
                    'has_more_pages' => $options->hasMorePages(),
                ],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Option group not found.');
        }
    }

    public function showOption(Request $request, int $groupId, int $optionId): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        try {
            $option = $this->service->showForRestaurant($groupId)->options()->where('id', $optionId)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Option retrieved successfully.',
                'data' => $option,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Option not found.');
        }
    }

    public function bulkDeleteOptions(Request $request, int $groupId): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer'],
            ], [
                'ids.required' => 'Please select options for delete.',
                'ids.min' => 'Please select options for delete.',
            ])->validate();

            $deletedCount = $this->service->bulkDeleteOptionsForGroup($groupId, $validated['ids']);

            return response()->json([
                'success' => true,
                'message' => $deletedCount > 0 ? 'Selected options deleted successfully.' : 'No options were deleted.',
                'deleted_count' => $deletedCount,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Option group not found.');
        }
    }

    public function storeOption(Request $request, int $groupId): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'is_active' => ['nullable', 'boolean'],
            ], [
                'name.required' => 'Please enter an option name.',
            ])->validate();

            $option = $this->service->createOptionForGroup($groupId, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Option created successfully.',
                'data' => $option,
            ], 201);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Option group not found.');
        }
    }

    public function updateOption(Request $request, int $groupId, int $optionId): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'is_active' => ['sometimes', 'nullable', 'boolean'],
            ], [
                'name.required' => 'Please enter an option name.',
            ])->validate();

            $option = $this->service->updateOptionForGroup($groupId, $optionId, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Option updated successfully.',
                'data' => $option,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Option not found.');
        }
    }

    public function destroyOption(Request $request, int $groupId, int $optionId): JsonResponse
    {
        $this->getRestaurantForAuthUser($request);

        try {
            $deleted = $this->service->deleteOptionForGroup($groupId, $optionId);

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'Option deleted successfully.' : 'Option could not be deleted.',
            ], $deleted ? 200 : 404);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Option group or option not found.');
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
