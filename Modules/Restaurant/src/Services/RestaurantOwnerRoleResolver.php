<?php

namespace Modules\Restaurant\Services;

use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Acl\Models\Role;

/**
 * Single source of truth for the Restaurant Owner role.
 *
 * Uses the "Default Restaurant Role" setting (general.default_role) and falls
 * back to the admin-guard role named "Restaurant Owner". Never hard-code the
 * role id (e.g. `role_id === 2`); use id() / isOwner() instead.
 */
class RestaurantOwnerRoleResolver
{
    public function __construct(private Role $roles)
    {
    }

    public function resolve(): Role
    {
        $role = $this->find();

        if (!$role) {
            throw ValidationException::withMessages([
                'registration' => 'The Restaurant Owner role is not configured. Please contact support.',
            ]);
        }

        return $role;
    }

    /**
     * Restaurant Owner role id, or null when no such role is configured.
     */
    public function id(): ?int
    {
        return once(fn () => $this->find()?->getKey());
    }

    public function isOwner(?Admin $admin): bool
    {
        $roleId = $this->id();

        return $admin !== null && $roleId !== null && (int) $admin->role_id === $roleId;
    }

    protected function find(): ?Role
    {
        $settingId = (int) fn_get_setting('general.default_role');

        if ($settingId > 0) {
            $role = $this->roles->newQuery()
                ->where('guard', 'admin')
                ->whereKey($settingId)
                ->first();

            if ($role) {
                return $role;
            }
        }

        return $this->roles->newQuery()
            ->where('guard', 'admin')
            ->where('name', 'Restaurant Owner')
            ->first();
    }
}
