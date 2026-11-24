<?php

namespace Modules\Activitylog\Http\Middleware\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\Activitylog\Jobs\StoreBlockedIpFromCacheJob;
use Modules\Activitylog\Models\BlockedIp;
use Modules\Activitylog\Support\ActivityLogSettings;
use Modules\Activitylog\Support\ThreatProtectionCache;
use Modules\Cache\Services\AppCache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

trait InteractsWithRequestThreats
{
    protected function applyThreatProtection(Request $request, callable $next): Response
    {
        try {
            $ipAddress = $this->resolveClientIp($request);

            if (ThreatProtectionCache::isIpBlocked($ipAddress)) {
                $reason = 'Your IP address has been blocked for security reasons.';

                return $this->markBlocked(
                    $request,
                    $this->blockedResponse($request, $ipAddress, $reason, 403),
                    $reason
                );
            }

            if ($this->isAdminRequest($request) && ! $this->isAllowedAdminIp($ipAddress) && ! $this->isAllowedAdminRoute($request)) {
                $reason = 'Your IP address is not allowed to access the admin area.';
                $response = $this->adminDeniedResponse($request, $ipAddress, $reason);

                return $this->markBlocked($request, $response, $reason);
            }

            if ($this->shouldSkipThreatProtection($ipAddress)) {
                return $next($request);
            }

            if ($this->isClearlySuspiciousRequest($request) && ! $this->isAllowedAdminIp($ipAddress) && ! $this->isAllowedAdminRoute($request)) {
                $reason = 'Suspicious request detected. Access has been blocked for security reasons.';
                $this->autoBlockIp($ipAddress, $reason, ActivityLogSettings::getInt('block_minutes_on_brute_force', 240));

                return $this->markBlocked(
                    $request,
                    $this->blockedResponse($request, $ipAddress, $reason, 403),
                    $reason
                );
            }

            if ($this->isRateLimitExceeded($request) && ! $this->isAllowedAdminIp($ipAddress) && ! $this->isAllowedAdminRoute($request)) {
                $reason = 'Too many requests in a short time. Please try again later.';
                $this->autoBlockIp($ipAddress, $reason, ActivityLogSettings::getInt('block_minutes_on_rate_limit', 60));

                return $this->markBlocked(
                    $request,
                    $this->blockedResponse($request, $ipAddress, $reason, 429),
                    $reason
                );
            }
        } catch (Throwable $e) {
            // Fail open on protection infrastructure errors so users see the app,
            // not a generic Server Error page.
            Log::error('Activitylog threat protection failed', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'url' => $request->url(),
                'ip' => $request->ip(),
            ]);

            return $next($request);
        }

        $response = $next($request);

        try {
            $ipAddress = $this->resolveClientIp($request);

            if (
                $this->isPotentialBruteForce($request, $response)
                && $this->isBruteForceThresholdExceeded($request)
                && ! $this->isAllowedAdminIp($ipAddress)
                && ! $this->isAllowedAdminRoute($request)
            ) {
                $this->autoBlockIp($ipAddress, 'Brute force pattern detected', ActivityLogSettings::getInt('block_minutes_on_brute_force', 240));
            }
        } catch (Throwable $e) {
            Log::warning('Activitylog brute-force tracking failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
        }

        return $response;
    }

    protected function markBlocked(Request $request, Response $response, string $reason): Response
    {
        $request->attributes->set('activitylog.is_blocked', true);
        $request->attributes->set('activitylog.blocked_reason', $reason);

        return $response;
    }

    protected function blockedResponse(Request $request, string $ipAddress, string $reason, int $status): Response
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $reason,
                'ip_address' => $ipAddress,
            ], $status);
        }

        return response()->view('activitylog::blocked', [
            'ipAddress' => $ipAddress,
            'blockedReason' => $reason,
        ], $status);
    }

    protected function adminDeniedResponse(Request $request, string $ipAddress, string $reason): Response
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $reason,
                'ip_address' => $ipAddress,
            ], 403);
        }

        return response()->view('activitylog::denied-admin-access', [
            'ipAddress' => $ipAddress,
            'requestedPath' => '/' . ltrim($request->path(), '/'),
            'actionText' => 'Access Request',
            'modalTitle' => 'Access Request',
            'buttonClass' => 'bg-blue-600 text-white hover:bg-blue-700',
        ], 403);
    }

    protected function shouldSkipThreatProtection(string $ipAddress): bool
    {
        $settingAllowedIps = explode(',', (string) fn_get_setting('general.allowed_ips', ''));
        $settingAllowedIps = array_map(static fn (string $ip): string => trim($ip), $settingAllowedIps);
        $settingAllowedIps = array_filter($settingAllowedIps, static fn (string $ip): bool => $ip !== '');

        return in_array($ipAddress, array_merge(['127.0.0.1', '::1'], $settingAllowedIps), true);
    }

    protected function isAllowedAdminRoute(Request $request): bool
    {
        $path = trim($request->path(), '/');

        if (Str::is('admin-access-request', $path) || Str::is('admin-access-request/*', $path)) {
            return true;
        }

        return $this->requestPathMatchesSetting($request, 'general.allowed_routes');
    }

    protected function requestPathMatchesSetting(Request $request, string $settingKey): bool
    {
        $path = trim(trim($request->path()), '/');

        if ($path === '') {
            return false;
        }

        foreach ($this->getRoutePatterns($settingKey) as $pattern) {
            $matches = $pattern === $path || Str::is($pattern, $path);

            if (! $matches && Str::endsWith($pattern, '/*')) {
                $basePattern = rtrim(substr($pattern, 0, -2), '/');
                $matches = $basePattern !== ''
                    && ($path === $basePattern || Str::startsWith($path, $basePattern . '/'));
            }

            if ($matches) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    protected function getRoutePatterns(string $settingKey): array
    {
        $rawPatterns = preg_split('/[\r\n,]+/', (string) fn_get_setting($settingKey, '')) ?: [];

        $patterns = array_map(static function (string $pattern): string {
            $normalized = trim($pattern);

            if ($normalized === '' || $normalized === '/') {
                return '';
            }

            return trim($normalized, '/');
        }, $rawPatterns);

        return array_values(array_unique(array_filter($patterns, static fn (string $pattern): bool => $pattern !== '')));
    }

    protected function isAdminRequest(Request $request): bool
    {
        return Auth::guard('admin')->check();
    }

    protected function shouldSkipRequestLogging(Request $request): bool
    {
        return $this->isBotRequest($request);
    }

    protected function isBotRequest(Request $request): bool
    {
        return self::isBotUserAgent((string) $request->userAgent());
    }

    public static function isBotUserAgent(string $userAgent): bool
    {
        $ua = Str::lower(trim($userAgent));

        if ($ua === '') {
            return false;
        }

        foreach (self::botUserAgentPatterns() as $pattern) {
            if (str_contains($ua, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    protected static function botUserAgentPatterns(): array
    {
        return [
            'bot',
            'crawler',
            'spider',
            'slurp',
            'mediapartners-google',
            'facebookexternalhit',
            'whatsapp',
            'telegrambot',
            'linkedinbot',
            'twitterbot',
            'pinterest',
            'bingpreview',
            'yandex',
            'baiduspider',
            'duckduckbot',
            'semrush',
            'ahrefs',
            'petalbot',
            'bytespider',
            'headlesschrome',
        ];
    }

    protected function isAllowedAdminIp(string $ipAddress): bool
    {
        $allowedIps = ThreatProtectionCache::allowedAdminIpAddresses();

        if ($allowedIps === []) {
            return true;
        }

        return in_array($ipAddress, $allowedIps, true);
    }

    protected function isRateLimitExceeded(Request $request): bool
    {
        $key = sprintf('activitylog:request-rate:%s', $this->resolveClientIp($request));
        $limit = ActivityLogSettings::getInt('request_throttle_limit', ActivityLogSettings::getInt('max_requests_per_minute', 60));
        $decay = ActivityLogSettings::getInt('request_throttle_decay', 60);
        $tooMany = RateLimiter::tooManyAttempts($key, $limit);

        if (! $tooMany) {
            RateLimiter::hit($key, $decay);
        }

        return $tooMany;
    }

    protected function isPotentialBruteForce(Request $request, Response $response): bool
    {
        $path = Str::lower(trim($request->path(), '/'));
        $statusCode = $response->getStatusCode();
        $authPathKeywords = [
            'login',
            'signin',
            'auth',
            'otp',
            'password',
            'forgot-password',
            'reset-password',
            'admin/login',
            'admin/forgot-password',
            'admin/reset-password',
        ];

        $isAuthPath = false;

        foreach ($authPathKeywords as $keyword) {
            if (Str::contains($path, $keyword)) {
                $isAuthPath = true;
                break;
            }
        }

        return $isAuthPath && in_array($statusCode, [401, 403, 419, 422, 429], true);
    }

    protected function isBruteForceThresholdExceeded(Request $request): bool
    {
        $key = sprintf('activitylog:bruteforce:%s', $this->resolveClientIp($request));
        $maxAttempts = ActivityLogSettings::getInt('max_failed_auth_attempts', 12);
        $tooMany = RateLimiter::tooManyAttempts($key, $maxAttempts);

        if (! $tooMany) {
            RateLimiter::hit($key, $maxAttempts * 60);
        }

        return $tooMany;
    }

    protected function isClearlySuspiciousRequest(Request $request): bool
    {
        $path = Str::lower(trim($request->path(), '/'));
        $userAgent = Str::lower((string) $request->userAgent());
        $signatures = [
            '.env',
            '.git',
            '.svn',
            '.hg',
            '.DS_Store',
            'composer.json',
            'composer.lock',
            'package.json',
            'package-lock.json',
            'yarn.lock',
            'storage/logs',
            'storage/framework',
            'wp-admin',
            'wp-login',
            'xmlrpc.php',
            'wordpress',
            'phpmyadmin',
            'pma',
            'myadmin',
            'vendor/phpunit',
            'eval-stdin.php',
            'autoload.php',
            'artisan',
            'server-status',
            'server-info',
            'hudson',
            'jenkins',
            'boaform',
            'w00tw00t',
            'cgi-bin',
            'cgi-sys',
            'shell',
            'cmd.php',
            'r57',
            'c99',
            'phpinfo.php',
            'debug/default/view',
            'telescope',
        ];

        $matched = false;

        foreach ($signatures as $signature) {
            if (Str::contains($path, $signature)) {
                $matched = true;
                break;
            }
        }

        if (! $matched && $userAgent !== '') {
            $matched = Str::contains($userAgent, ['sqlmap', 'nmap', 'nikto', 'wpscan', 'python-requests']);
        }

        return $matched;
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

    protected function autoBlockIp(string $ipAddress, string $reason, int $minutes): void
    {
        $payload = [
            'ip_address' => $ipAddress,
            'reason' => $reason,
            'is_active' => true,
            'expires_at' => now()->addMinutes($minutes),
        ];

        if (! $this->cacheAndQueueBlockedIp($payload)) {
            self::storeBlockedIpPayload($payload);
        }

        ThreatProtectionCache::forgetBlockedIps();
    }

    protected function cacheAndQueueBlockedIp(array $payload): bool
    {
        try {
            $cacheKey = $this->buildBlockedIpCacheKey($payload);
            $scheduleKey = $cacheKey . ':scheduled';

            AppCache::put('activity', $cacheKey, $payload, 600);

            if (AppCache::add('activity', $scheduleKey, true, 3)) {
                StoreBlockedIpFromCacheJob::dispatch($cacheKey)
                    ->delay(now()->addSeconds(2));
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function buildBlockedIpCacheKey(array $payload): string
    {
        return 'blocked-ip:' . hash(
            'sha512',
            (string) ($payload['ip_address'] ?? '')
        );
    }

    public static function storeBlockedIpPayload(array $payload): void
    {
        $model = new BlockedIp();
        $payload = Arr::only($payload, $model->getFillable());

        if (empty($payload['ip_address'])) {
            return;
        }

        BlockedIp::updateOrCreate(
            ['ip_address' => $payload['ip_address']],
            [
                'reason' => $payload['reason'] ?? null,
                'is_active' => (bool) ($payload['is_active'] ?? true),
                'expires_at' => $payload['expires_at'] ?? null,
                'blocked_by_id' => $payload['blocked_by_id'] ?? null,
                'blocked_by_type' => $payload['blocked_by_type'] ?? null,
            ]
        );

        ThreatProtectionCache::forgetBlockedIps();
    }
}
