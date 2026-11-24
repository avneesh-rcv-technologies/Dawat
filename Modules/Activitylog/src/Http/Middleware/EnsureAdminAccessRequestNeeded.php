<?php

namespace Modules\Activitylog\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Activitylog\Models\AllowedIp;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccessRequestNeeded
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->alreadyHasAdminAccess($request)) {
            return redirect()->to($this->resolveRedirectPath($request));
        }

        return $next($request);
    }

    protected function alreadyHasAdminAccess(Request $request): bool
    {
        $ipAddress = $this->resolveClientIp($request);
        $allowedIps = $this->getAllowedAdminIps();

        return in_array($ipAddress, $allowedIps, true);
    }

    protected function resolveClientIp(Request $request): string
    {
        $cfIp = trim((string) $request->headers->get('CF-Connecting-IP', ''));

        if (filter_var($cfIp, FILTER_VALIDATE_IP)) {
            return $cfIp;
        }

        $xForwardedFor = (string) $request->headers->get('X-Forwarded-For', '');

        if ($xForwardedFor !== '') {
            $parts = array_map(static fn (string $part): string => trim($part), explode(',', $xForwardedFor));

            foreach ($parts as $candidate) {
                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    return $candidate;
                }
            }
        }

        return (string) $request->ip();
    }

    /**
     * @return array<int, string>
     */
    protected function getAllowedAdminIps(): array
    {
        $dbAllowedIps = AllowedIp::query()
            ->active()
            ->pluck('ip_address')
            ->all();

        $settingAllowedIps = explode(',', (string) fn_get_setting('general.allowed_ips', ''));
        $settingAllowedIps = array_map(static fn (string $ip): string => trim($ip), $settingAllowedIps);
        $settingAllowedIps = array_filter($settingAllowedIps, static fn (string $ip): bool => $ip !== '');

        return array_values(array_unique(array_merge(['127.0.0.1', '::1'], $dbAllowedIps, $settingAllowedIps)));
    }

    protected function resolveRedirectPath(Request $request): string
    {
        $requestedPath = (string) $request->query('requested_path', '/dashboard');
        $requestedPath = trim($requestedPath);

        if ($requestedPath === '' || Str::contains($requestedPath, ["://", "\n", "\r"])) {
            return '/dashboard';
        }

        if (!Str::startsWith($requestedPath, '/')) {
            $requestedPath = '/' . ltrim($requestedPath, '/');
        }

        if (Str::startsWith($requestedPath, '//')) {
            return '/dashboard';
        }

        return $requestedPath;
    }
}
