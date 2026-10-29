<?php

namespace Modules\Subscriptions\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Subscriptions\Models\Feature;
use Modules\Subscriptions\DataView\FeaturesGrid;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class FeatureController extends Controller
{
    /**
     * Display a listing of the features.
     */
    public function index(Request $request)
    {
        $lists = fn_datagrid(FeaturesGrid::class)->process();

        // Get statistics
        $stats = [
            'total' => Feature::count(),
            'active' => Feature::where('is_active', true)->count(),
            'inactive' => Feature::where('is_active', false)->count(),
            'boolean' => Feature::where('type', 'boolean')->count(),
            'limit' => Feature::where('type', 'limit')->count(),
            'package' => Feature::where('type', 'package')->count(),
        ];

        // Get features by group
        $groupedCount = Feature::selectRaw('`group`, COUNT(*) as count')
            ->groupBy('group')
            ->pluck('count', 'group')
            ->toArray();

        return view('subscriptions::features.index', compact('lists', 'stats', 'groupedCount'));
    }

    /**
     * Show the form for creating a new feature.
     */
    public function create()
    {
        return view('subscriptions::features.form');
    }

    /**
     * Store a newly created feature in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $this->validateFeatureData($request->all());
            
            $feature = Feature::create($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature created successfully.',
                    'redirect_url' => route('subscriptions.features.index'),
                ]);
            }

            return redirect()->route('subscriptions.features.index')
                ->with('success', 'Feature created successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Display the specified feature.
     */
    public function show($id)
    {
        $feature = Feature::findOrFail($id);
        
        // Get plans using this feature
        $plans = $feature->plans()
            ->withPivot(['value', 'notes', 'is_highlighted', 'sort_order'])
            ->orderBy('sort_order')
            ->get();

        // Get subscriptions using this feature
        $subscriptionsCount = $feature->subscriptions()->count();
        $activeSubscriptionsCount = $feature->subscriptions()
            ->whereIn('subscriptions.status', ['active', 'trialing'])
            ->count();

        return view('subscriptions::features.show', compact(
            'feature',
            'plans',
            'subscriptionsCount',
            'activeSubscriptionsCount'
        ));
    }

    /**
     * Show the form for editing the specified feature.
     */
    public function edit($id)
    {
        $feature = Feature::findOrFail($id);
        return view('subscriptions::features.form', compact('feature'));
    }

    /**
     * Update the specified feature in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $feature = Feature::findOrFail($id);
            $validatedData = $this->validateFeatureData($request->all(), $id);
            
            $feature->update($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature updated successfully.',
                    'redirect_url' => route('subscriptions.features.index'),
                ]);
            }

            return redirect()->route('subscriptions.features.index')
                ->with('success', 'Feature updated successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Remove the specified feature from storage.
     */
    public function destroy($id)
    {
        try {
            $feature = Feature::findOrFail($id);
            
            // Check if feature is being used
            $plansCount = $feature->plans()->count();
            $subscriptionsCount = $feature->subscriptions()->count();
            
            if ($plansCount > 0 || $subscriptionsCount > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot delete feature. It is being used by {$plansCount} plan(s) and {$subscriptionsCount} subscription(s).",
                    ], 400);
                }

                return redirect()->back()
                    ->with('error', "Cannot delete feature. It is being used by {$plansCount} plan(s) and {$subscriptionsCount} subscription(s).");
            }

            $feature->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature deleted successfully.',
                ]);
            }

            return redirect()->route('subscriptions.features.index')
                ->with('success', 'Feature deleted successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Mass destroy features.
     */
    public function massDestroy(Request $request)
    {
        try {
            $ids = $request->input('indices', []);

            if (empty($ids)) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No features selected.',
                    ], 400);
                }

                return redirect()->back()
                    ->with('error', 'No features selected.');
            }

            $count = 0;
            $skipped = 0;
            $errors = [];

            foreach ($ids as $id) {
                try {
                    $feature = Feature::findOrFail($id);
                    
                    // Check if feature is being used
                    $plansCount = $feature->plans()->count();
                    $subscriptionsCount = $feature->subscriptions()->count();
                    
                    if ($plansCount > 0 || $subscriptionsCount > 0) {
                        $skipped++;
                        $errors[] = "Feature '{$feature->display_name}' is being used and cannot be deleted.";
                        continue;
                    }

                    $feature->delete();
                    $count++;
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Failed to delete feature ID {$id}.";
                }
            }

            $message = "{$count} feature(s) deleted successfully.";
            if ($skipped > 0) {
                $message .= " {$skipped} feature(s) skipped (in use or error).";
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'errors' => $errors,
                ]);
            }

            $response = redirect()->back()->with('success', $message);
            if (!empty($errors)) {
                $response->with('warnings', $errors);
            }
            return $response;
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Mass activate features.
     */
    public function massActivate(Request $request)
    {
        try {
            $ids = $request->input('indices', []);

            if (empty($ids)) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No features selected.',
                    ], 400);
                }

                return redirect()->back()
                    ->with('error', 'No features selected.');
            }

            $count = Feature::whereIn('id', $ids)->update(['is_active' => true]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} feature(s) activated successfully.",
                ]);
            }

            return redirect()->back()
                ->with('success', "{$count} feature(s) activated successfully.");
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Mass deactivate features.
     */
    public function massDeactivate(Request $request)
    {
        try {
            $ids = $request->input('indices', []);

            if (empty($ids)) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No features selected.',
                    ], 400);
                }

                return redirect()->back()
                    ->with('error', 'No features selected.');
            }

            $count = Feature::whereIn('id', $ids)->update(['is_active' => false]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} feature(s) deactivated successfully.",
                ]);
            }

            return redirect()->back()
                ->with('success', "{$count} feature(s) deactivated successfully.");
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Validate feature data.
     */
    protected function validateFeatureData(array $data, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:128|unique:features,name' . ($id ? ',' . $id : ''),
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:boolean,limit,package',
            'unit' => 'nullable|string|max:50',
            'default_value' => 'nullable|string|max:255',
            'group' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];

        // Handle metadata based on type
        if (isset($data['type'])) {
            if ($data['type'] === 'limit') {
                $rules['metadata.min'] = 'nullable|numeric';
                $rules['metadata.max'] = 'nullable|numeric';
                $rules['metadata.step'] = 'nullable|numeric';
            } elseif ($data['type'] === 'package') {
                $rules['metadata.options'] = 'nullable|array';
                $rules['metadata.options.*'] = 'string';
            }
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            if (request()->ajax()) {
                throw new \Exception($validator->errors()->first());
            }
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $validated = $validator->validated();

        // Format metadata as JSON if present
        if (isset($validated['metadata']) && is_array($validated['metadata'])) {
            // Remove empty values
            $validated['metadata'] = array_filter($validated['metadata'], function($value) {
                return $value !== null && $value !== '';
            });
        }

        // Set defaults
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }
}
