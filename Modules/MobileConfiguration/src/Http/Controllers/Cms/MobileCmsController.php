<?php

namespace Modules\MobileConfiguration\Http\Controllers\Cms;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\MobileConfiguration\Models\MobileCms;
use Modules\MobileConfiguration\DataView\MobileCmsGrid;
use Modules\MobileConfiguration\Services\MobileCmsService;
use Throwable;

class MobileCmsController extends Controller
{
    protected MobileCmsService $service;

    public function __construct(MobileCmsService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $lists = fn_datagrid(MobileCmsGrid::class)->process();
        return view('mobileconfiguration::cms.index', compact('lists'));
    }

    public function create()
    {
        return view('mobileconfiguration::cms.form');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'cms_page_id' => 'required|integer|exists:cms_page_settings,id',
                'status' => 'required|boolean',
                'position' => 'nullable|integer|min:0',
                'image' => 'nullable|image|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->create($request);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mobile CMS created successfully!',
                    'redirect_url' => route('mobileconfiguration.cms.index'),
                ]);
            }

            return redirect()
                ->route('mobileconfiguration.cms.index')
                ->with('success', 'Mobile CMS created successfully!');
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function edit(MobileCms $cm)
    {
        return view('mobileconfiguration::cms.form', ['mobileCms' => $cm]);
    }

    public function update(Request $request, MobileCms $cm)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'cms_page_id' => 'required|integer|exists:cms_page_settings,id',
                'status' => 'required|boolean',
                'position' => 'nullable|integer|min:0',
                'image' => 'nullable|image|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->update($request, $cm);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mobile CMS updated successfully!',
                    'redirect_url' => route('mobileconfiguration.cms.index'),
                ]);
            }

            return redirect()
                ->route('mobileconfiguration.cms.index')
                ->with('success', 'Mobile CMS updated successfully!');
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy(MobileCms $cm)
    {
        try {
            $this->service->delete($cm);

            return response()->json([
                'success' => true,
                'message' => 'Mobile CMS deleted successfully!',
                'redirect_url' => route('mobileconfiguration.cms.index'),
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
                'ids.*' => 'integer|exists:mobile_cms,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => 'Mobile CMS deleted successfully!',
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
                'ids.*' => 'integer|exists:mobile_cms,id',
                'status' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 200);
            }

            $this->service->updateStatus($request->ids, $request->boolean('status'));

            return response()->json([
                'success' => true,
                'message' => 'Mobile CMS status updated successfully!',

            ]);
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
