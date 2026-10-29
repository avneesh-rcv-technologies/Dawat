<?php

namespace Modules\Subscriptions\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Models\Feature;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class SubscriptionFeatureController extends Controller
{
    /**
     * Show subscription features.
     */
    public function index($subscriptionId)
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        
        // Get subscription features grouped by group
        $features = $subscription->subscriptionFeatures()
            ->get()
            ->groupBy(function ($item) {
                return $item->group ?? 'Other';
            });

        $assignedIds = $subscription->subscriptionFeatures()->pluck('features.id');
        $availableFeatures = Feature::whereNotIn('id', $assignedIds)->where('is_active', true)->orderBy('group')->orderBy('sort_order')->get();

        // Statistics
        $stats = [
            'total_features' => $subscription->subscriptionFeatures()->count(),
            'active_features' => $subscription->activeFeatures()->count(),
            'limit_features' => $subscription->subscriptionFeatures()
                ->where('type', 'limit')
                ->count(),
            'boolean_features' => $subscription->subscriptionFeatures()
                ->where('type', 'boolean')
                ->count(),
        ];

        return view('subscriptions::subscription-features.index', compact(
            'subscription',
            'features',
            'stats',
            'availableFeatures'
        ));
    }

    /**
     * Attach a new feature to the subscription.
     */
    public function store(Request $request, $subscriptionId)
    {
        try {
            $request->validate([
                'feature_id' => 'required|exists:features,id',
                'value' => 'nullable|string',
            ]);

            $subscription = Subscription::findOrFail($subscriptionId);
            $feature = Feature::findOrFail($request->input('feature_id'));

            // Prevent duplicates
            if ($subscription->subscriptionFeatures()->where('features.id', $feature->id)->exists()) {
                return redirect()->back()->with('error', 'Feature already assigned to this subscription.');
            }

            $plan = $subscription->getEffectivePlan();
            $billingCycle = $plan?->billing_cycle ?? 'monthly';
            $usageResetAt = match($billingCycle) {
                'daily' => now()->addDay(),
                'weekly' => now()->addWeek(),
                'quarterly' => now()->addMonths(3),
                'yearly' => now()->addYear(),
                default => now()->addMonth(),
            };

            $value = $request->input('value', $feature->default_value ?? ($feature->type === 'boolean' ? 'false' : null));

            $subscription->subscriptionFeatures()->attach($feature->id, [
                'value' => $value,
                'used' => 0,
                'usage_reset_at' => $feature->type === 'limit' ? $usageResetAt : null,
                'is_custom' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Feature added to subscription.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Show usage for a specific feature.
     */
    public function show($subscriptionId, $featureId)
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        $feature = $subscription->subscriptionFeatures()
            ->where('feature_id', $featureId)
            ->firstOrFail();

        // Calculate usage percentage
        $usagePercent = 0;
        if ($feature->type === 'limit') {
            $limit = (float) $feature->pivot->value;
            if ($limit > 0 && $limit !== -1) {
                $usagePercent = min(100, round((float) $feature->pivot->used / $limit * 100));
            }
        }

        return view('subscriptions::subscription-features.show', compact(
            'subscription',
            'feature',
            'usagePercent'
        ));
    }

    /**
     * Update a subscription feature (custom override).
     */
    public function update(Request $request, $subscriptionId, $featureId)
    {
        try {
            $subscription = Subscription::findOrFail($subscriptionId);
            $feature = $subscription->subscriptionFeatures()
                ->where('feature_id', $featureId)
                ->firstOrFail();

            $value = $request->input('value');
            $customNotes = $request->input('custom_notes');

            $subscription->subscriptionFeatures()->updateExistingPivot($featureId, [
                'value' => $value,
                'is_custom' => true,
                'custom_notes' => $customNotes,
                'updated_at' => now(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature override updated successfully.',
                ]);
            }

            return redirect()->back()
                ->with('success', 'Feature override updated successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Reset feature usage.
     */
    public function resetUsage($subscriptionId, $featureId)
    {
        try {
            $subscription = Subscription::findOrFail($subscriptionId);
            $subscription->resetFeatureUsage($subscription->subscriptionFeatures()
                ->where('feature_id', $featureId)
                ->first()
                ->name);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature usage reset successfully.',
                ]);
            }

            return redirect()->back()
                ->with('success', 'Feature usage reset successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Toggle feature active status.
     */
    public function toggle($subscriptionId, $featureId)
    {
        try {
            $subscription = Subscription::findOrFail($subscriptionId);
            $feature = $subscription->subscriptionFeatures()
                ->where('feature_id', $featureId)
                ->firstOrFail();

            $newStatus = !$feature->pivot->is_active;

            $subscription->subscriptionFeatures()->updateExistingPivot($featureId, [
                'is_active' => $newStatus,
                'updated_at' => now(),
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature status updated successfully.',
                    'is_active' => $newStatus,
                ]);
            }

            return redirect()->back()
                ->with('success', 'Feature status updated successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
