<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Restaurant\Services\RestaurantFoodTagService;

class FoodTagsApiController extends Controller
{
    public function __construct(protected RestaurantFoodTagService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->getAuthenticatedAdmin($request);

        $filters = $request->only([
            'search',
            'name',
            'per_page',
            'sort_by',
            'sort_dir',
        ]);

        $tags = $this->service->listForRestaurant($filters);

        $tags->getCollection()->each->makeHidden([
            'slug',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Food tags retrieved successfully.',
            'data' => $tags->items(),
            'meta' => [
                'current_page' => $tags->currentPage(),
                'per_page' => $tags->perPage(),
                'total' => $tags->total(),
                'last_page' => $tags->lastPage(),
                'has_more_pages' => $tags->hasMorePages(),
            ],
        ], 200);
}

    public function store(Request $request): JsonResponse
    {
        $this->getAuthenticatedAdmin($request);

        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('food_tags', 'name')],
            'slug' => ['prohibited'],
        ])->validate();

        $tag = $this->service->createForRestaurant($validated);

        return response()->json([
            'success' => true,
            'message' => 'Food tag created successfully.',
            'data' => $tag,
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->getAuthenticatedAdmin($request);

        try {
            $tag = $this->service->showForRestaurant($id);

            return response()->json([
                'success' => true,
                'message' => 'Food tag retrieved successfully.',
                'data' => $tag,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food tag not found.');
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->getAuthenticatedAdmin($request);

        try {
            $validated = Validator::make($request->all(), [
                'name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('food_tags', 'name')->ignore($id),
                ],
                'slug' => ['prohibited'],
            ])->validate();

            $tag = $this->service->updateForRestaurant($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Food tag updated successfully.',
                'data' => $tag,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food tag not found.');
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->getAuthenticatedAdmin($request);

        try {
            $deleted = $this->service->deleteForRestaurant($id);

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'Food tag deleted successfully.' : 'Food tag could not be deleted.',
            ], $deleted ? 200 : 404);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Food tag not found.');
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $this->getAuthenticatedAdmin($request);

        $validated = Validator::make($request->all(), [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:food_tags,id'],
        ], [
            'ids.required' => 'Please select tags for delete.',
            'ids.min' => 'Please select tags for delete.',
        ])->validate();

        $deletedCount = $this->service->bulkDeleteForRestaurant($validated['ids']);

        return response()->json([
            'success' => true,
            'message' => $deletedCount > 0
                ? 'Selected food tags deleted successfully.'
                : 'No food tags were deleted.',
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

    protected function getAuthenticatedAdmin(Request $request): mixed
    {
        $admin = $request->user('sanctum');

        if (!$admin) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401));
        }

        return $admin;
    }
}
