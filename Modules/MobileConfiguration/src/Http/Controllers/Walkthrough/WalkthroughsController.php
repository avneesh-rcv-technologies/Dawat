<?php

namespace Modules\MobileConfiguration\Http\Controllers\Walkthrough;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\MobileConfiguration\Models\MobileWalkthrough;
use Modules\MobileConfiguration\DataView\MobileWalkthroughGrid;
use Modules\MobileConfiguration\Services\MobileWalkthroughService;
use Throwable;

class WalkthroughsController extends Controller
{
    protected MobileWalkthroughService $service;

    public function __construct(MobileWalkthroughService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $lists = fn_datagrid(MobileWalkthroughGrid::class)->process();
        return view('mobileconfiguration::walkthroughs.index', compact('lists'));
    }

    public function create()
    {
        return view('mobileconfiguration::walkthroughs.form');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
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
                    'message' => 'Walkthrough created successfully!',
                    'redirect_url' => route('mobileconfiguration.walkthroughs.index'),
                ]);
            }

            return redirect()
                ->route('mobileconfiguration.walkthroughs.index')
                ->with('success', 'Walkthrough created successfully!');

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function edit(MobileWalkthrough $walkthrough)
    {
        return view('mobileconfiguration::walkthroughs.form', compact('walkthrough'));
    }

    public function update(Request $request, MobileWalkthrough $walkthrough)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'status' => 'required|boolean',
                'position' => 'nullable|integer|min:0',
                'image' => 'nullable|image|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->update($request, $walkthrough);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Walkthrough updated successfully!',
                    'redirect_url' => route('mobileconfiguration.walkthroughs.index'),
                ]);
            }

            return redirect()
                ->route('mobileconfiguration.walkthroughs.index')
                ->with('success', 'Walkthrough updated successfully!');

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy(MobileWalkthrough $walkthrough)
    {
        try {
            $this->service->delete($walkthrough);

            return response()->json([
                'success' => true,
                'message' => 'Walkthrough deleted successfully!',
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
                'ids.*' => 'integer|exists:mobile_walkthrough,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => 'Walkthroughs deleted successfully!',
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
                'ids.*' => 'integer|exists:mobile_walkthrough,id',
                'status' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->updateStatus($request->ids, $request->boolean('status'));

            return response()->json([
                'success' => true,
                'message' => 'Walkthrough status updated successfully!',
            ]);

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}