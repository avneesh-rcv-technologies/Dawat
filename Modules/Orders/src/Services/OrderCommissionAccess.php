<?php

namespace Modules\Orders\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Acl\Models\Admin;
use Modules\Orders\Models\OrderCommission;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;

class OrderCommissionAccess
{
    public static function visibleInSidebar(): bool
    {
        $actor = auth('admin')->user();

        return $actor instanceof Admin && $actor->status && (
            app(RestaurantApprovalAccess::class)->isOwner($actor) || $actor->hasPermission('admin.order-commissions.index')
        );
    }

    public function actor(Request $request, bool $write = false): Admin
    {
        $actor = $request->user('admin') ?? $request->user();
        abort_unless($actor instanceof Admin && $actor->status, 403);
        abort_unless($write ? $this->canUpdate($actor)
            : (app(RestaurantApprovalAccess::class)->isOwner($actor) || $actor->hasPermission('admin.order-commissions.index')), 403);

        return $actor;
    }

    public function canUpdate(Admin $actor): bool
    {
        // Settlement is an administrative action; owners can inspect their own records.
        return (bool) $actor->status && ! app(RestaurantApprovalAccess::class)->isOwner($actor) && $actor->hasPermission('admin.order-commissions.update');
    }

    public function query(Admin $actor): Builder
    {
        $query = OrderCommission::query();
        if (app(RestaurantApprovalAccess::class)->isOwner($actor)) {
            $query->whereIn('restaurant_id', Restaurant::where('admin_id', $actor->id)->select('id'));
        }

        return $query;
    }
}
