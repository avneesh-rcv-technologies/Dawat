<?php

namespace Modules\DietaryManagment\Services;

use Modules\Acl\Models\Admin;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;

class CertificationAccess
{
    public function isOwner(Admin $admin): bool
    {
        return app(RestaurantApprovalAccess::class)->isOwner($admin)
            || Restaurant::where('admin_id', $admin->id)->exists();
    }

    public function scope($query, ?Admin $admin, string $restaurantColumn = 'restaurant_id')
    {
        if (!$admin || !$admin->status) {
            return $query->whereRaw('1 = 0');
        }
        if ($this->isOwner($admin)) {
            $query->whereIn($restaurantColumn, Restaurant::where('admin_id', $admin->id)->select('id'));
        }

        return $query;
    }
}
