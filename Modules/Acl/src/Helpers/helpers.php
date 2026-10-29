<?php

use Modules\Acl\Models\Role;
use Modules\Acl\Support\Bouncer;

if (! function_exists('bouncer')) {
    /**
     * Get a Bouncer instance for permission checking.
     * Can be used with a single guard name (string) or an array of guards.
     * If an array is passed, the returned object will expose a hasPermission($permission)
     * method which returns true if any guard authorizes the permission.
     *
     * @param string|array|null $guard The guard to use (default: 'admin') or an array of guards
     * @return Bouncer|object
     *
     * Examples:
     * // Single guard (default)
     * bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('users.index');
     *
     * // Multiple guards — returns an object with hasPermission implemented across guards
     * bouncer(['web','admin'])->hasPermission('dashboard.view');
     */
    function bouncer($guard = null)
    {
        // If an array of guards is provided, return a small wrapper that checks any guard
        $guard = $guard ?? config('acl::guards.current_user_guard') ?? array_keys(config('acl::guards.guards')) ?? 'admin';
        if (is_array($guard)) {
            return new class($guard) {
                private $guards = [];

                public function __construct(array $guards)
                {
                    $this->guards = $guards;
                }

                /**
                 * Check permission across any of the configured guards.
                 * Returns true on first success.
                 *
                 * @param string $permission
                 * @return bool
                 */
                public function hasPermission(string $permission): bool
                {
                    foreach ($this->guards as $g) {
                        try {
                            $b = new Bouncer($g ?? 'admin');
                            if ($b->hasPermission($permission)) {
                                return true;
                            }
                        } catch (\Throwable $e) {
                            // ignore and try next guard
                            continue;
                        }
                    }

                        if (!function_exists('fn_admin_portal_role_id')) {
                            /**
                             * Get a role id for the admin portal by role slug or name.
                             * Accepts values like "restaurant_owner" or "Restaurant Owner" and returns the matching role id for the given guard.
                             *
                             * @param string $roleIdentifier
                             * @param string $guard
                             * @return int|null
                             */
                            function fn_admin_portal_role_id(string $roleIdentifier, string $guard = 'admin'): ?int
                            {
                                try {
                                    // Normalize input: convert snake_case or kebab-case to Title Case (Restaurant Owner)
                                    $name = trim($roleIdentifier);
                                    if (strpos($name, '_') !== false || strpos($name, '-') !== false) {
                                        $name = str_replace(['_', '-'], ' ', $name);
                                        $name = ucwords($name);
                                    }

                                    $role = Role::where('guard', $guard)
                                        ->where(function ($q) use ($roleIdentifier, $name) {
                                            $q->where('name', $name)
                                                ->orWhere('name', $roleIdentifier);
                                        })->first();

                                    return $role ? (int) $role->id : null;
                                } catch (\Throwable $e) {
                                    return null;
                                }
                            }
                        }

                    return false;
                }
            };
        }

        // Single guard (string or null) — return normal Bouncer instance
        return new Bouncer($guard ?? 'admin');
    }
}


if (!function_exists('fn_get_usergroups')) {
    function fn_get_usergroups($filters = []): mixed
    {
        $query = Role::query();

        // Filter by id or ids
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        if (!empty($filters['ids']) && is_array($filters['ids'])) {
            $query->whereIn('id', $filters['ids']);
        }

        // Guard filter (string or array)
        if (isset($filters['guard'])) {
            if (is_array($filters['guard'])) {
                $query->whereIn('guard', $filters['guard']);
            } else {
                $query->where('guard', $filters['guard']);
            }
        }

        // Name (partial match)
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        // Search across name/description
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qexp) use ($q) {
                $qexp->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        // Permission type exact match
        if (!empty($filters['permission_type'])) {
            $query->where('permission_type', $filters['permission_type']);
        }

        // Permissions - supports string or array; permission_mode = 'any'|'all'
        if (!empty($filters['permission'])) {
            $mode = $filters['permission_mode'] ?? 'any';
            $perms = $filters['permission'];
            if (!is_array($perms)) {
                // single permission
                $query->whereJsonContains('permissions', $perms);
            } else {
                if ($mode === 'all') {
                    foreach ($perms as $p) {
                        $query->whereJsonContains('permissions', $p);
                    }
                } else {
                    // any
                    $query->where(function ($qperm) use ($perms) {
                        foreach ($perms as $p) {
                            $qperm->orWhereJsonContains('permissions', $p);
                        }
                    });
                }
            }
        }

        // Ordering
        if (!empty($filters['order_by'])) {
            $dir = $filters['order_dir'] ?? 'asc';
            $query->orderBy($filters['order_by'], $dir);
        }

        // Limit / paginate
        if (!empty($filters['per_page']) || !empty($filters['paginate'])) {
            $perPage = $filters['per_page'] ?? 15;
            return $query->paginate($perPage);
        }

        if (!empty($filters['limit'])) {
            $query->limit((int) $filters['limit']);
        }

        return $query->get();
    }

    if (! function_exists('decode_html_entities')) {
    function decode_html_entities($value)
    {
        $decoded = html_entity_decode(
                $value,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
        return $decoded;
    }

    // get payment method codeby id
    if (! function_exists('fn_get_payment_method_code')) {
        function fn_get_payment_method_code($paymentMethodId)
        {
            $paymentMethod = \Modules\Payments\Models\PaymentMethod::find($paymentMethodId);
            return $paymentMethod ? $paymentMethod->code : null;
        }
    }

}

}