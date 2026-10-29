<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cms\Services\CmsPageService;
use Modules\Cms\Http\Resources\PageResource;

class PageApiController extends Controller
{
    protected CmsPageService $service;

    public function __construct(CmsPageService $service)
    {
        $this->service = $service;
    }

    /**
     * Get page by slug
     */
    public function show(string $slug)
    {
        try {
            $page = $this->service->getBySlug($slug);

            if (!$page || !$page->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Page not found or inactive',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new PageResource($page),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch page',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}