<?php

namespace Modules\MobileConfiguration\Http\Controllers\Banner;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\MobileConfiguration\Models\MobileBanner;
use Modules\MobileConfiguration\DataView\MobileBannerGrid;
use Modules\MobileConfiguration\Services\MobileBannerService;
use Throwable;

class BannersController extends Controller
{
    protected MobileBannerService $service;

    public function __construct(MobileBannerService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $lists = fn_datagrid(MobileBannerGrid::class)->process();
        return view('mobileconfiguration::banners.index', compact('lists'));
    }

    public function create()
    {
        return view('mobileconfiguration::banners.form');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => 'required|in:product,category,external_link,no_link',
                'type_value' => 'nullable|string|max:255',
                'status' => 'required|boolean',
                'position' => 'nullable|integer|min:0',
                'image' => 'nullable|image|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Additional validation based on type
            if ($request->type === 'product' && $request->type_value) {
                $validator->after(function ($validator) use ($request) {
                    if (!\DB::table('products')->where('id', $request->type_value)->exists()) {
                        $validator->errors()->add('type_value', 'Selected product does not exist.');
                    }
                });
            }

            if ($request->type === 'category' && $request->type_value) {
                $validator->after(function ($validator) use ($request) {
                    if (!\DB::table('categories')->where('id', $request->type_value)->exists()) {
                        $validator->errors()->add('type_value', 'Selected category does not exist.');
                    }
                });
            }

            if ($request->type === 'external_link' && !$request->type_value) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('type_value', 'External link URL is required.');
                });
            }

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->create($request);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Banner created successfully!',
                    'redirect_url' => route('mobileconfiguration.banners.index'),
                ]);
            }

            return redirect()
                ->route('mobileconfiguration.banners.index')
                ->with('success', 'Banner created successfully!');

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function edit(MobileBanner $banner)
    {
        return view('mobileconfiguration::banners.form', compact('banner'));
    }

    public function update(Request $request, MobileBanner $banner)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => 'required|in:product,category,external_link,no_link',
                'type_value' => 'nullable|string|max:255',
                'status' => 'required|boolean',
                'position' => 'nullable|integer|min:0',
                'image' => 'nullable|image|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->update($request, $banner);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Banner updated successfully!',
                    'redirect_url' => route('mobileconfiguration.banners.index'),
                ]);
            }

            return redirect()
                ->route('mobileconfiguration.banners.index')
                ->with('success', 'Banner updated successfully!');

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy(MobileBanner $banner)
    {
        try {
            $this->service->delete($banner);

            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully!',
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
                'ids.*' => 'integer|exists:mobile_banners,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => 'Banners deleted successfully!',
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
                'ids.*' => 'integer|exists:mobile_banners,id',
                'status' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->updateStatus($request->ids, $request->boolean('status'));

            return response()->json([
                'success' => true,
                'message' => 'Banner status updated successfully!',
            ]);

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}