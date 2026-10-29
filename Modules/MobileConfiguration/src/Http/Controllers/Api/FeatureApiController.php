<?php

namespace Modules\MobileConfiguration\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\MobileConfiguration\Services\MobileFeatureService;
use Modules\MobileConfiguration\Http\Resources\FeatureResource;

class FeatureApiController extends Controller
{
    protected MobileFeatureService $service;

    public function __construct(MobileFeatureService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all features with pagination
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $features = $this->service->getPaginatedFeatures($perPage);

            return response()->json([
                'success' => true,
                'data' => FeatureResource::collection($features->items()),
                'pagination' => [
                    'current_page' => $features->currentPage(),
                    'last_page' => $features->lastPage(),
                    'per_page' => $features->perPage(),
                    'total' => $features->total(),
                    'from' => $features->firstItem(),
                    'to' => $features->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch features',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get feature by slug with its items
     */
    public function show(string $slug)
    {
        try {
            $result = $this->service->getFeatureWithItems($slug);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Feature not found or inactive',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'feature' => new FeatureResource($result['feature']),
                    'items' => $result['items'],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch feature',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}