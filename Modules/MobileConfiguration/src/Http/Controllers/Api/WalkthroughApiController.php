<?php

namespace Modules\MobileConfiguration\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\MobileConfiguration\Services\MobileWalkthroughService;
use Modules\MobileConfiguration\Http\Resources\WalkthroughResource;

class WalkthroughApiController extends Controller
{
    protected MobileWalkthroughService $service;

    public function __construct(MobileWalkthroughService $service)
    {
        $this->service = $service;
    }

    /**
     * Get only active walkthroughs (for mobile app)
     */
    public function index()
    {
        try {
            $walkthroughs = $this->service->getActiveWalkthroughs();

            return response()->json([
                'success' => true,
                'data' => WalkthroughResource::collection($walkthroughs),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch walkthroughs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}