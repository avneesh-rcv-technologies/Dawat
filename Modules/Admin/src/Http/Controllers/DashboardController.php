<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Acl\Models\Admin;
use Modules\Admin\Services\AdminDashboardService;
use Modules\Admin\Services\AdminDashboardFinanceService;
use Modules\Admin\Support\DashboardFilters;
use Modules\GeneralSetting\Services\SettingService;
use Modules\Restaurant\Services\RestaurantApprovalAccess;

class DashboardController extends Controller
{
    public function index(Request $request, AdminDashboardService $dashboard)
    {
        $actor = $request->user('admin');
        abort_unless($actor instanceof Admin && $actor->status && $actor->hasPermission('dashboard.index'), 403);
        $data = ['userName' => $actor->name, 'dashboard' => null, 'finance' => null];
        $data['isOwnerDashboard'] = app(RestaurantApprovalAccess::class)->isOwner($actor);
        $data['ownerDashboard'] = null;
        $settings = app(SettingService::class);
            $timezone = $settings->getValidatedTimezone() ?? config('app.timezone', 'UTC');
            $defaultCurrency = (string) $settings->get('general.currency', 'USD');
            $filters = DashboardFilters::from($request->query(), $timezone, $defaultCurrency ?: 'USD');
            $data += ['filters' => $filters];
            if ($data['isOwnerDashboard']) {
                $data['ownerDashboard'] = app(\Modules\Admin\Services\OwnerDashboardService::class)->summarize($actor, $filters);
            } else {
            $data['dashboard'] = $dashboard->summarize($filters);
            if ($actor->hasPermission('admin.order-commissions.index')) {
                $data['finance'] = app(AdminDashboardFinanceService::class)->summarize($filters);
            }
            }

        return response()->view('admin::pages.dashboard.index', $data)->header('Cache-Control', 'private, no-store');
    }
}
