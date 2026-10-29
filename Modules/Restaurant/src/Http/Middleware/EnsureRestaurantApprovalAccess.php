<?php

namespace Modules\Restaurant\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Acl\Models\Admin;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;

class EnsureRestaurantApprovalAccess
{
    public function __construct(private RestaurantApprovalAccess $access) {}

    public function handle(Request $request, Closure $next)
    {
        $admin = $request->user('admin');
        abort_unless($admin instanceof Admin && $admin->status, 403);
        $permission = $request->route()->getName();
        abort_unless($admin->hasPermission($permission), 403);
        if ($permission !== 'admin.restaurant.approvals.index') {
            abort_unless($admin->hasPermission('admin.restaurant.approvals.show'), 403);
        }

        if ($id = $request->route('id')) {
            $this->access->scope(Restaurant::query(), $admin)->whereKey($id)->firstOrFail();
        }

        return $next($request);
    }
}
