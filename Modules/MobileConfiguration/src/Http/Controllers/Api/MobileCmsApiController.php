<?php

namespace Modules\MobileConfiguration\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\MobileConfiguration\Services\MobileCmsService;
use Modules\MobileConfiguration\Http\Resources\MobileCmsResource;

class MobileCmsApiController extends Controller
{
    protected MobileCmsService $service;

    public function __construct(MobileCmsService $service)
    {
        $this->service = $service;
    }

    /**
     * Get only active mobile CMS (for mobile app)
     */
    public function index()
    {
        try {
            $mobileCms = $this->service->getActiveCms();

            return response()->json([
                'success' => true,
                'data' => MobileCmsResource::collection($mobileCms),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch mobile CMS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get mobile CMS details by ID
     */
    public function show(int $id)
    {
        try {
            $mobileCms = $this->service->findById($id);

            if (!$mobileCms) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mobile CMS not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new MobileCmsResource($mobileCms),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch mobile CMS details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}