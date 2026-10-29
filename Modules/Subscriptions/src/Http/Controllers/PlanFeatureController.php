<?php

namespace Modules\Subscriptions\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Subscriptions\Models\Plan;
use Modules\Subscriptions\Models\Feature;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class PlanFeatureController extends Controller
{
    /**
     * Show form for mapping features to a plan.
     */
    public function edit($planId)
    {
        $plan = Plan::findOrFail($planId);
        
        // Get all features (active and inactive)
        $features = Feature::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get();

        if ($features->isEmpty()) {
            // If no features exist, return with empty data
            $planFeatures = collect();
            $groupedFeatures = collect();
        } else {
            // Get features already mapped to this plan
            $planFeatures = $plan->planFeatures()
                ->get()
                ->keyBy('id');

            // Group features by group - handle null groups
            $groupedFeatures = $features->groupBy(function($feature) {
                return $feature->group ?? 'Uncategorized';
            });
        }

        return view('subscriptions::plan-features.edit', compact(
            'plan',
            'features',
            'planFeatures',
            'groupedFeatures'
        ));
    }

    /**
     * Update feature mappings for a plan.
     */
    public function update(Request $request, $planId)
    {
        try {
            $plan = Plan::findOrFail($planId);
            $featureData = $request->input('features', []);

            // Validate feature data
            foreach ($featureData as $featureId => $data) {
                $feature = Feature::findOrFail($featureId);
                
                // Validate value based on feature type
                $this->validateFeatureValue($feature, $data['value'] ?? null);
            }

            // Clear existing features and re-attach
            $plan->planFeatures()->detach();

            // Attach new features
            foreach ($featureData as $featureId => $data) {
                if (isset($data['value']) && $data['value'] !== '') {
                    $plan->planFeatures()->attach($featureId, [
                        'value' => $data['value'],
                        'is_highlighted' => $data['is_highlighted'] ?? false,
                        'sort_order' => $data['sort_order'] ?? 0,
                        'notes' => $data['notes'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Plan features updated successfully.',
                    'redirect_url' => route('subscriptions.plans.show', $plan->id),
                ]);
            }

            return redirect()->route('subscriptions.plans.show', $plan->id)
                ->with('success', 'Plan features updated successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Remove a feature from a plan.
     */
    public function destroy($planId, $featureId)
    {
        try {
            $plan = Plan::findOrFail($planId);
            $plan->planFeatures()->detach($featureId);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature removed from plan.',
                ]);
            }

            return redirect()->back()
                ->with('success', 'Feature removed from plan.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Validate feature value based on feature type.
     */
    protected function validateFeatureValue(Feature $feature, $value)
    {
        if (!$value) {
            return true;
        }

        if ($feature->type === 'boolean') {
            if (!in_array($value, ['true', 'false', '1', '0', 'yes', 'no', 'enabled', 'disabled'])) {
                throw new \Exception("Invalid boolean value for {$feature->display_name}");
            }
        } elseif ($feature->type === 'limit') {
            if (!is_numeric($value) && !in_array(strtolower($value), ['unlimited', '-1'])) {
                throw new \Exception("Invalid numeric value for {$feature->display_name}");
            }
        } elseif ($feature->type === 'package') {
            $options = $feature->metadata['options'] ?? [];
            if (!empty($options) && !in_array($value, $options)) {
                throw new \Exception("Invalid package value for {$feature->display_name}");
            }
        }

        return true;
    }
}
