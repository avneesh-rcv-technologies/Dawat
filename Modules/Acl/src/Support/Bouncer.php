<?php

namespace Modules\Acl\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class Bouncer
{
    /**
     * The guard to use for authentication.
     *
     * @var string
     */
    protected $guard;

    /**
     * Create a new bouncer instance.
     *
     * @param string|null $guard
     * @return void
     */
    public function __construct(?string $guard = null)
    {
        $this->guard = $guard ?: $this->resolveDefaultGuard();
    }

    /**
     * Check if the authenticated user has the given permission.
     *
     * @param string|array $permission
     * @param string|null $guard
     * @return bool
     */
    public function hasPermission($permission, ?string $guard = null): bool
    {
        $guardToUse = $guard ?: $this->guard;
        $user = $this->getAuthenticatedUser($guardToUse);
        
        if (! $user) {
            return false;
        }

        // If user doesn't have a role, deny access
        if (!$user->role) {
            return false;
        }

        // If role has 'all' permission type, allow everything
        if ($user->role->permission_type === 'all') {
            return true;
        }

        if (is_array($permission)) {
            return $this->hasAnyPermission($permission, $user);
        }

        // Check if permission is a route name or custom permission
        $userPermissions = $user->role->permissions ?? [];
        
        if (is_array($userPermissions)) {
            return in_array($permission, $userPermissions);
        }

        return $user->hasPermission($permission);
    }

    /**
     * Check if the user has any of the given permissions.
     *
     * @param array $permissions
     * @param mixed $user
     * @return bool
     */
    protected function hasAnyPermission(array $permissions, $user): bool
    {
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the authenticated user has the given permission.
     *
     * @param string|array $permission
     * @param string|null $guard
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public static function allow($permission, ?string $guard = null): void
    {
        $bouncer = new static($guard);
        $user = $bouncer->getAuthenticatedUser($guard);

        if (! $user || ! $bouncer->hasPermission($permission, $guard)) {
            abort(403, 'This action is unauthorized.');
        }
    }

    /**
     * Get the authenticated user for the given guard.
     *
     * @param string|null $guard
     * @return mixed
     */
    protected function getAuthenticatedUser(?string $guard = null)
    {
        $guard = $guard ?: $this->guard;
        return Auth::guard($guard)->user();
    }

    /**
     * Resolve the default guard from config.
     *
     * @return string
     */
    protected function resolveDefaultGuard(): string
    {
        return Config::get('auth.defaults.guard', 'web');
    }

    /**
     * Set the guard for the bouncer.
     *
     * @param string $guard
     * @return $this
     */
    public function guard(string $guard): self
    {
        $this->guard = $guard;
        return $this;
    }

    /**
     * Create a new bouncer instance for the given guard.
     *
     * @param string $guard
     * @return static
     */
    public static function forGuard(string $guard): self
    {
        return new static($guard);
    }
}
