<?php

namespace Modules\MobileConfiguration\Http\Controllers\HomepageLayout;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\MobileConfiguration\Models\MobileHomepageLayout;
use Modules\MobileConfiguration\DataView\MobileHomepageLayoutGrid;
use Modules\MobileConfiguration\Services\MobileHomepageLayoutService;
use Modules\MobileConfiguration\Services\MobileBannerService;
use Modules\MobileConfiguration\Services\MobileFeatureService;
use Throwable;

class HomepageLayoutsController extends Controller
{
    protected MobileHomepageLayoutService $service;
    protected MobileBannerService $bannerService;
    protected MobileFeatureService $featureService;

    public function __construct(
        MobileHomepageLayoutService $service,
        MobileBannerService $bannerService,
        MobileFeatureService $featureService
    ) {
        $this->service = $service;
        $this->bannerService = $bannerService;
        $this->featureService = $featureService;
    }

    public function index()
    {
        $lists = fn_datagrid(MobileHomepageLayoutGrid::class)->process();
        return view('mobileconfiguration::homepage-layouts.index', compact('lists'));
    }

    public function create()
    {
        $banners = $this->bannerService->getActiveBanners();
        $features = $this->featureService->getActiveFeatures();
        
        return view('mobileconfiguration::homepage-layouts.form', compact('banners', 'features'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:mobile_homepage_layouts,slug',
                'description' => 'nullable|string|max:1000',
                'is_default' => 'nullable|boolean',
                'status' => 'required|boolean',
                'position' => 'nullable|integer|min:0',
                'sections' => 'nullable|array',
                'sections.*.type' => 'required_with:sections|in:banners,features',
                'sections.*.banner_ids' => 'required_if:sections.*.type,banners|array',
                'sections.*.feature_ids' => 'required_if:sections.*.type,features|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Debug: Log the sections data
            \Log::info('Creating layout with sections:', $request->input('sections', []));

            $this->service->create($request);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Homepage layout created successfully!',
                    'redirect_url' => route('mobileconfiguration.homepage-layouts.index'),
                ]);
            }

            return redirect()
                ->route('mobileconfiguration.homepage-layouts.index')
                ->with('success', 'Homepage layout created successfully!');

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function edit(MobileHomepageLayout $homepageLayout)
    {
        $banners = $this->bannerService->getActiveBanners();
        $features = $this->featureService->getActiveFeatures();
        
        return view('mobileconfiguration::homepage-layouts.form', compact('homepageLayout', 'banners', 'features'));
    }

    public function update(Request $request, MobileHomepageLayout $homepageLayout)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:mobile_homepage_layouts,slug,' . $homepageLayout->id,
                'description' => 'nullable|string|max:1000',
                'is_default' => 'nullable|boolean',
                'status' => 'required|boolean',
                'position' => 'nullable|integer|min:0',
                'sections' => 'nullable|array',
                'sections.*.type' => 'required_with:sections|in:banners,features',
                'sections.*.banner_ids' => 'required_if:sections.*.type,banners|array',
                'sections.*.feature_ids' => 'required_if:sections.*.type,features|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Debug: Log the sections data
            \Log::info('Updating layout with sections:', $request->input('sections', []));

            $this->service->update($request, $homepageLayout);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Homepage layout updated successfully!',
                    'redirect_url' => route('mobileconfiguration.homepage-layouts.index'),
                ]);
            }

            return redirect()
                ->route('mobileconfiguration.homepage-layouts.index')
                ->with('success', 'Homepage layout updated successfully!');

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy(MobileHomepageLayout $homepageLayout)
    {
        try {
            $this->service->delete($homepageLayout);

            return response()->json([
                'success' => true,
                'message' => 'Homepage layout deleted successfully!',
            ]);

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:mobile_homepage_layouts,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => 'Homepage layouts deleted successfully!',
            ]);

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function toggleStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:mobile_homepage_layouts,id',
                'status' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->updateStatus($request->ids, $request->boolean('status'));

            return response()->json([
                'success' => true,
                'message' => 'Homepage layout status updated successfully!',
            ]);

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function setAsDefault(Request $request, MobileHomepageLayout $homepageLayout)
    {
        try {
            $this->service->setAsDefault($homepageLayout->id);
            return redirect()->back()->with('success','Homepage layout set as default successfully!');

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Homepage layout set as default successfully!',
            //     'redirect_url' => route('mobileconfiguration.homepage-layouts.index'),
            // ]);

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}