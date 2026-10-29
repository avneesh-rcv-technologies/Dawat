<?php

namespace Modules\FoodManagements\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\FoodManagements\Services\FoodFeatureService;

class FoodFeaturesApiController extends Controller
{
    public function __construct(
        protected FoodFeatureService $featureService
    ) {}

    /**
     * GET /api/food-features
     *
     * Query params:
     *   category_id (optional) — filter features applicable to a category
     *   purpose     (optional) — filter by purpose (e.g. "filter", "variation")
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:food_categories,id',
            'purpose'     => 'nullable|string|max:64',
        ]);

        try {
            $groups = $this->featureService->getGroupsForApi(
                $request->integer('category_id') ?: null,
                $request->query('purpose')
            );

            return response()->json([
                'success' => true,
                'data'    => [
                    'groups'      => $groups,
                    'total_count' => $groups->count(),
                ],
                'message' => 'Food features retrieved successfully',
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}
