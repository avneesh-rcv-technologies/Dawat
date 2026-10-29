<?php

namespace Modules\MobileConfiguration\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\MobileConfiguration\Services\MobileBannerService;
use Modules\MobileConfiguration\Http\Resources\BannerResource;

class BannerApiController extends Controller
{
    protected MobileBannerService $service;

    public function __construct(MobileBannerService $service)
    {
        $this->service = $service;
    }

    /**
     * Get only active banners (for mobile app)
     */
    public function active()
    {
        try {
            $banners = $this->service->getActiveBanners();
           

            return response()->json([
                'success' => true,
                'data' => BannerResource::collection($banners),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active banners',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}