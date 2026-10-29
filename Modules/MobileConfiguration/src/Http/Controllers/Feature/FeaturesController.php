<?php

namespace Modules\MobileConfiguration\Http\Controllers\Feature;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\MobileConfiguration\Models\MobileFeature;
use Modules\MobileConfiguration\DataView\MobileFeatureGrid;
use Modules\MobileConfiguration\Services\MobileFeatureService;
use Throwable;

class FeaturesController extends Controller
{
    protected MobileFeatureService $service;

    public function __construct(MobileFeatureService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $lists = fn_datagrid(MobileFeatureGrid::class)->process();
        return view('mobileconfiguration::features.index', compact('lists'));
    }

    public function create()
    {
        return view('mobileconfiguration::features.form');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:mobile_features,slug',
                'used_for' => 'required|in:general,carousel',
                'type' => 'required|in:product,category',
                'selection_type' => 'required|in:newest,oldest,custom',
                'items_count' => 'nullable|integer|min:1|max:100',
                'position' => 'nullable|integer|min:0',
                'status' => 'required|boolean',
                'custom_products' => 'nullable|array',
                'custom_products.*' => 'integer|exists:products,id',
                'custom_categories' => 'nullable|array',
                'custom_categories.*' => 'integer|exists:categories,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Additional validation for items_count when not custom
            if ($request->selection_type !== 'custom' && !$request->items_count) {
                return response()->json(['errors' => ['items_count' => ['The items count field is required for newest/oldest selection.']]], 422);
            }

            // Additional validation for custom selection
            if ($request->selection_type === 'custom') {
                if ($request->type === 'product' && empty($request->custom_products)) {
                    return response()->json(['errors' => ['custom_products' => ['Please select at least one product for custom selection.']]], 422);
                }
                if ($request->type === 'category' && empty($request->custom_categories)) {
                    return response()->json(['errors' => ['custom_categories' => ['Please select at least one category for custom selection.']]], 422);
                }
            }

            $this->service->create($request);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature created successfully!',
                    'redirect_url' => route('mobileconfiguration.features.index'),
                ]);
            }

            return redirect()
                ->route('mobileconfiguration.features.index')
                ->with('success', 'Feature created successfully!');

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function edit(MobileFeature $feature)
    {
        return view('mobileconfiguration::features.form', compact('feature'));
    }

    public function update(Request $request, MobileFeature $feature)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:mobile_features,slug,' . $feature->id,
                'used_for' => 'required|in:general,carousel',
                'type' => 'required|in:product,category',
                'selection_type' => 'required|in:newest,oldest,custom',
                'items_count' => 'nullable|integer|min:1|max:100',
                'position' => 'nullable|integer|min:0',
                'status' => 'required|boolean',
                'custom_products' => 'nullable|array',
                'custom_products.*' => 'integer|exists:products,id',
                'custom_categories' => 'nullable|array',
                'custom_categories.*' => 'integer|exists:categories,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Additional validation for items_count when not custom
            if ($request->selection_type !== 'custom' && !$request->items_count) {
                return response()->json(['errors' => ['items_count' => ['The items count field is required for newest/oldest selection.']]], 422);
            }

            $this->service->update($request, $feature);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature updated successfully!',
                    'redirect_url' => route('mobileconfiguration.features.index'),
                ]);
            }

            return redirect()
                ->route('mobileconfiguration.features.index')
                ->with('success', 'Feature updated successfully!');

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy(MobileFeature $feature)
    {
        try {
            $this->service->delete($feature);

            return response()->json([
                'success' => true,
                'message' => 'Feature deleted successfully!',
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
                'ids.*' => 'integer|exists:mobile_features,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => 'Features deleted successfully!',
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
                'ids.*' => 'integer|exists:mobile_features,id',
                'status' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->updateStatus($request->ids, $request->boolean('status'));

            return response()->json([
                'success' => true,
                'message' => 'Feature status updated successfully!',
            ]);

        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}