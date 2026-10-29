<?php

namespace Modules\Subscriptions\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Subscriptions\Services\PlanService;
use Modules\Subscriptions\DataView\PlansGrid;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class PlanController extends Controller
{
    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(PlansGrid::class)->process();
        
        // Get statistics
        $stats = $this->planService->getPlanStatistics();
        
        return view('subscriptions::plans.index', compact('lists', 'stats'));
    }

    public function show($id)
    {
        $plan = $this->planService->getPlanById($id);
        
        // Load subscription products (product-plan mappings) with their relationships
        $subscriptionProducts = $plan->subscriptionProducts()
            ->with(['product'])
            ->paginate(20);
        
        // Get statistics for this plan
        $stats = [
            'total_products' => $plan->subscriptionProducts()->count(),
            'active_products' => $plan->subscriptionProducts()->where('is_active', true)->count(),
            'total_subscriptions' => $plan->subscriptions()->count(),
            'active_subscriptions' => $plan->activeSubscriptions()->count(),
        ];
        
        return view('subscriptions::plans.show', compact('plan', 'subscriptionProducts', 'stats'));
    }

    public function create()
    {
        return view('subscriptions::plans.form');
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $this->planService->validatePlanData($request->all());
            $plan = $this->planService->createPlan($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Plan created successfully.',
                    'redirect_url' => route('subscriptions.plans.index'),
                ]);
            }

            return redirect()->route('subscriptions.plans.index')
                ->with('success', 'Plan created successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function edit($id)
    {
        $plan = $this->planService->getPlanById($id);
        return view('subscriptions::plans.form', compact('plan'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $this->planService->validatePlanData($request->all());
            $this->planService->updatePlan($id, $validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Plan updated successfully.',
                    'redirect_url' => route('subscriptions.plans.index'),
                ]);
            }

            return redirect()->route('subscriptions.plans.index')
                ->with('success', 'Plan updated successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->planService->deletePlan($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Plan deleted successfully.',
                ]);
            }

            return redirect()->route('subscriptions.plans.index')
                ->with('success', 'Plan deleted successfully.');

        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Mass destroy plans.
     */
    public function massDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No plans selected.',
                    ], 400);
                }

                return redirect()->back()
                    ->with('error', 'No plans selected.');
            }

            $count = 0;
            foreach ($ids as $id) {
                try {
                    $this->planService->deletePlan($id);
                    $count++;
                } catch (\Exception $e) {
                    throw new \Exception('Failed to delete some plans.');
                }
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} plan(s) deleted successfully.",
                ]);
            }

            return redirect()->back()
                ->with('success', "{$count} plan(s) deleted successfully.");
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Mass activate plans.
     */
    public function massActivate(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No plans selected.',
                    ], 400);
                }
                return redirect()->back()
                    ->with('error', 'No plans selected.');
            }

            $count = 0;
            foreach ($ids as $id) {
                try {
                    $this->planService->updatePlan($id, ['is_active' => 1]);
                    $count++;
                } catch (\Exception $e) {
                    throw new \Exception('Failed to activate some plans.');
                }
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} plan(s) activated successfully.",
                ]);
            }

            return redirect()->back()
                ->with('success', "{$count} plan(s) activated successfully.");
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Mass deactivate plans.
     */
    public function massDeactivate(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No plans selected.',
                    ], 400);
                }
                return redirect()->back()
                    ->with('error', 'No plans selected.');
            }

            $count = 0;
            foreach ($ids as $id) {
                try {
                    $this->planService->updatePlan($id, ['is_active' => 0]);
                    $count++;
                } catch (\Exception $e) {
                    throw new \Exception('Failed to deactivate some plans.');
                }
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} plan(s) deactivated successfully.",
                ]);
            }

            return redirect()->back()
                ->with('success', "{$count} plan(s) deactivated successfully.");
                
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
