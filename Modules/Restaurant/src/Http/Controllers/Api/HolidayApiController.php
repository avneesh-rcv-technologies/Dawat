<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantHolidayService;

class HolidayApiController extends Controller
{
    public function __construct(protected RestaurantHolidayService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $filters = $request->only([
            'search',
            'date_from',
            'date_to',
            'is_closed',
            'per_page',
            'sort_by',
            'sort_dir',
        ]);

        $holidays = $this->service->listForRestaurant($restaurant->id, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Restaurant holidays retrieved successfully.',
            'data' => $holidays->items(),
            'meta' => [
                'current_page' => $holidays->currentPage(),
                'per_page' => $holidays->perPage(),
                'total' => $holidays->total(),
                'last_page' => $holidays->lastPage(),
                'has_more_pages' => $holidays->hasMorePages(),
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'date' => ['required', 'date'],
                'reason' => ['nullable', 'string', 'max:255'],
                'is_closed' => ['nullable', 'boolean'],
            ], [
                'date.required' => 'Please select a holiday date.',
                'date.date' => 'Holiday date must be a valid date.',
            ])->validate();

            $holiday = $this->service->createForRestaurant($restaurant->id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Holiday created successfully.',
                'data' => $holiday,
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to create holiday.',
            ], 500);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $holiday = $this->service->showForRestaurant($restaurant->id, $id);

            return response()->json([
                'success' => true,
                'message' => 'Holiday retrieved successfully.',
                'data' => $holiday,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Holiday not found.');
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'date' => ['sometimes', 'required', 'date'],
                'reason' => ['sometimes', 'nullable', 'string', 'max:255'],
                'is_closed' => ['sometimes', 'nullable', 'boolean'],
            ], [
                'date.required' => 'Please select a holiday date.',
                'date.date' => 'Holiday date must be a valid date.',
            ])->validate();

            $holiday = $this->service->updateForRestaurant($restaurant->id, $id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Holiday updated successfully.',
                'data' => $holiday,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Holiday not found.');
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
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
                'message' => $deleted ? 'Holiday deleted successfully.' : 'Holiday could not be deleted.',
            ], $deleted ? 200 : 404);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Holiday not found.');
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        $validated = Validator::make($request->all(), [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Please select holidays for delete.',
            'ids.min' => 'Please select holidays for delete.',
        ])->validate();

        $deletedCount = $this->service->bulkDeleteForRestaurant($restaurant->id, $validated['ids']);

        return response()->json([
            'success' => true,
            'message' => $deletedCount > 0 ? 'Selected holidays deleted successfully.' : 'No holidays were deleted.',
            'deleted_count' => $deletedCount,
        ], 200);
    }

    protected function notFoundResponse(string $message = 'Holiday not found.'): JsonResponse
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
