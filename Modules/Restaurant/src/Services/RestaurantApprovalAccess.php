<?php

namespace Modules\Restaurant\Services;

use Modules\Acl\Models\Admin;

class RestaurantApprovalAccess
{
    public function isOwner(?Admin $admin): bool
    {
        if (!$admin) return false;
        $roleName = strtolower(trim(preg_replace('/[_\-\s]+/', ' ', (string) $admin->role?->name)));

        return (int) $admin->role_id === 2 || in_array($roleName, ['restaurant owner', 'restaurantowner'], true);
    }

    public function scope($query, ?Admin $admin, string $column = 'admin_id')
    {
        if (!$admin) {
            $query->whereRaw('1 = 0');
        } elseif ($this->isOwner($admin)) {
            $query->where($column, $admin->id);
        }

        return $query;
    }
}
