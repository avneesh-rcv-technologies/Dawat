<?php

namespace Modules\Payments\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Acl\Models\Admin;
use Modules\Payments\Models\Refund;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;

class RefundAccess
{
    public static function visibleInSidebar(): bool
    {
        $actor = auth('admin')->user();

        return $actor instanceof Admin && $actor->status && (
            app(RestaurantApprovalAccess::class)->isOwner($actor) || $actor->hasPermission('admin.refunds.index')
        );
    }

    public function actor(Request $request, bool $write = false): Admin
    {
        $actor = $request->user('admin') ?? $request->user();
        abort_unless($actor instanceof Admin && $actor->status, 403);
        abort_unless($write ? $this->canUpdate($actor)
            : (app(RestaurantApprovalAccess::class)->isOwner($actor) || $actor->hasPermission('admin.refunds.index')), 403,
            $write ? 'Only authorized platform admins can update refunds.' : 'You do not have permission to view refunds.');

        return $actor;
    }

    public function canUpdate(Admin $actor): bool
    {
        return (bool) $actor->status && ! app(RestaurantApprovalAccess::class)->isOwner($actor)
            && $actor->hasPermission('admin.refunds.update');
    }

    public function query(Admin $actor): Builder
    {
        $query = Refund::query();
        if (app(RestaurantApprovalAccess::class)->isOwner($actor)) {
            $query->whereHas('order', fn ($orders) => $orders
                ->where('vendor_type', (new Restaurant)->getMorphClass())
                ->whereIn('vendor_id', Restaurant::where('admin_id', $actor->id)->select('id')));
        }

        return $query;
    }
}
