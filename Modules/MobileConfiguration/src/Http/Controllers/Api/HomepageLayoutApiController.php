<?php

namespace Modules\MobileConfiguration\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\MobileConfiguration\Services\MobileHomepageLayoutService;

class HomepageLayoutApiController extends Controller
{
    protected MobileHomepageLayoutService $service;

    public function __construct(MobileHomepageLayoutService $service)
    {
        $this->service = $service;
    }

    /**
     * Get default homepage layout with all data
     */
    public function getLayout(Request $request)
    {
        try {
            $slug = $request->get('slug');
            $result = $this->service->getLayoutWithData($slug);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Homepage layout not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch homepage layout',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get layout by specific slug
     */
    public function getLayoutBySlug(string $slug)
    {
        try {
            $result = $this->service->getLayoutWithData($slug);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Homepage layout not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch homepage layout',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}