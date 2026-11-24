<?php

namespace Modules\Activitylog\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\Activitylog\Http\Middleware\Concerns\InteractsWithRequestThreats;
use Modules\Activitylog\Jobs\StoreRequestActivityLogFromCacheJob;
use Modules\Activitylog\Models\RequestActivityLog;
use Modules\Activitylog\Support\ActivityLogSettings;
use Modules\Cache\Services\AppCache;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TrackRequestActivity
{
    use InteractsWithRequestThreats;

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->attributes->has('activitylog.start')) {
            $request->attributes->set('activitylog.start', microtime(true));
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! ActivityLogSettings::requestLoggingEnabled()) {
            return;
        }

        $start = (float) $request->attributes->get('activitylog.start', microtime(true));
        $isBlocked = (bool) $request->attributes->get('activitylog.is_blocked', false);
        $blockedReason = $request->attributes->get('activitylog.blocked_reason');

        $this->recordRequest($request, $response, $start, $isBlocked, is_string($blockedReason) ? $blockedReason : null);
    }

    protected function recordRequest(
        Request $request,
        Response $response,
        float $start,
        bool $isBlocked = false,
        ?string $blockedReason = null
    ): void {
        $path = trim($request->path(), '/');
        $isAdvanceLoggingRoute = Str::is('admin/activity-log/advance-logging', $path)
            || Str::is('admin/activity-log/advance-logging/*', $path);

        if ($this->requestPathMatchesSetting($request, 'general.ignore_routes') || $isAdvanceLoggingRoute) {
            return;
        }

        if ($this->shouldSkipRequestLogging($request)) {
            return;
        }

        if (! $this->shouldSampleLog($request)) {
            return;
        }

        try {
            $payload = $this->buildLogPayload($request, $response, $start, $isBlocked, $blockedReason);

            if (! $this->cacheAndQueueLog($payload)) {
                $this->persistLogNow($payload);
            }
        } catch (Throwable $e) {
            // Do not break the request lifecycle if activity logging fails.
        }
    }

    protected function shouldSampleLog(Request $request): bool
    {
        $rate = ActivityLogSettings::getInt('request_sampling_rate', 1);

        if ($rate <= 1) {
            return true;
        }

        if (! $this->requestPathMatchesSetting($request, 'general.sampling_routes')) {
            return true;
        }

        return random_int(1, $rate) === 1;
    }

    protected function buildLogPayload(
        Request $request,
        Response $response,
        float $start,
        bool $isBlocked = false,
        ?string $blockedReason = null
    ): array {
        $router = app('router');
        $route = $request->route();
        $statusCode = $response->getStatusCode();
        $isErrorResponse = $statusCode >= 400;
        $includeMiddlewareDetails = $isErrorResponse || $isBlocked;

        $aliases = $router->getMiddleware();
        $webGroup = $router->getMiddlewareGroups()['web'] ?? [];
        $routeMiddleware = $route ? $route->gatherMiddleware() : [];

        $guard = $this->resolveGuard();

        $payload = [
            'ip_address' => $this->resolveClientIp($request),
            'guard' => $guard,
            'causer_type' => Auth::guard($guard)->check() ? get_class(Auth::guard($guard)->user()) : null,
            'causer_id' => Auth::guard($guard)->check() ? Auth::guard($guard)->id() : Auth::id(),
            'method' => $request->method(),
            'url' => $this->sanitizeUrl($request),
            'path' => $request->path(),
            'route_name' => $route?->getName(),
            'route_action' => $route?->getActionName(),
            'query_params' => $this->sanitizeQueryParams($request->query()),
            'request_body' => $this->sanitizeRequestBody($request),
            'headers' => $this->extractHeaders($request),
            'user_agent' => $request->userAgent(),
            'referer' => $request->headers->get('referer'),
            'response_status' => $statusCode,
            'response_headers' => $this->extractResponseHeaders($response),
            'duration_ms' => (int) round((microtime(true) - $start) * 1000),
            'is_blocked' => $isBlocked,
            'blocked_reason' => $blockedReason,
        ];

        if ($includeMiddlewareDetails) {
            $payload['request_middleware'] = $this->resolveMiddleware($routeMiddleware, $aliases);
            $payload['web_middleware_map'] = $this->resolveMiddleware($webGroup, $aliases);
        }

        if (! ActivityLogSettings::storeResponseBodyForErrorsOnly() || $isErrorResponse || $isBlocked) {
            $payload['response_body'] = $this->extractResponseBody($response);
        }

        return $payload;
    }

    protected function cacheAndQueueLog(array $payload): bool
    {
        try {
            $cacheKey = $this->buildLogCacheKey($payload);
            $scheduleKey = $cacheKey . ':scheduled';

            AppCache::put('activity', $cacheKey, $payload, 600);

            if (AppCache::add('activity', $scheduleKey, true, 3)) {
                StoreRequestActivityLogFromCacheJob::dispatch($cacheKey)
                    ->delay(now()->addSeconds(2));
            }

            return true;
        } catch (Throwable $e) {
            AppCache::increment('activity', 'failed_cache_writes');

            return false;
        }
    }

    protected function buildLogCacheKey(array $payload): string
    {
        $fingerprint = implode('|', [
            (string) ($payload['ip_address'] ?? ''),
            (string) ($payload['guard'] ?? ''),
            (string) ($payload['causer_id'] ?? ''),
            (string) ($payload['method'] ?? ''),
            (string) ($payload['path'] ?? ''),
            (string) ($payload['response_status'] ?? ''),
            (string) ((int) ($payload['is_blocked'] ?? false)),
        ]);

        return 'request-log:' . hash('sha512', $fingerprint);
    }

    protected function persistLogNow(array $payload): void
    {
        self::storeLogPayload($payload);
    }

    public static function storeLogPayload(array $payload): void
    {
        $model = new RequestActivityLog();
        $payload = Arr::only($payload, $model->getFillable());

        if ($payload === []) {
            return;
        }

        $existing = RequestActivityLog::query()
            ->where('ip_address', $payload['ip_address'] ?? null)
            ->where('guard', $payload['guard'] ?? null)
            ->where('causer_id', $payload['causer_id'] ?? null)
            ->where('method', $payload['method'] ?? null)
            ->where('path', $payload['path'] ?? null)
            ->where('response_status', $payload['response_status'] ?? null)
            ->where('is_blocked', (bool) ($payload['is_blocked'] ?? false))
            ->where('created_at', '>=', now()->subSeconds(2))
            ->latest('id')
            ->first();

        if ($existing) {
            $existing->fill($payload);
            $existing->save();

            return;
        }

        RequestActivityLog::create($payload);
    }

    /**
     * @param array<int, string> $middleware
     * @param array<string, string> $aliases
     * @return array<int, array{alias: string, middleware: string}>
     */
    protected function resolveMiddleware(array $middleware, array $aliases): array
    {
        return array_values(array_filter(array_map(function (string $value) use ($aliases): ?array {
            $parts = explode(':', $value, 2);
            $alias = $parts[0];

            if ($alias === '') {
                return null;
            }

            return [
                'alias' => $value,
                'middleware' => $aliases[$alias] ?? $alias,
            ];
        }, $middleware)));
    }

    protected function resolveGuard(): string
    {
        foreach (['admin', 'user'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return 'admin';
    }

    protected function sanitizeRequestBody(Request $request): array
    {
        return $this->redactSensitiveValues($request->all());
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function sanitizeQueryParams(array $params): array
    {
        return $this->redactSensitiveValues($params);
    }

    protected function sanitizeUrl(Request $request): string
    {
        $query = $this->sanitizeQueryParams($request->query());

        if ($query === []) {
            return $request->url();
        }

        return $request->url() . '?' . http_build_query($query);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function redactSensitiveValues(array $values): array
    {
        $redacted = [];

        foreach ($values as $key => $value) {
            if ($this->isSensitiveKey((string) $key)) {
                $redacted[$key] = '[redacted]';
                continue;
            }

            if (is_array($value)) {
                $redacted[$key] = $this->redactSensitiveValues($value);
                continue;
            }

            $redacted[$key] = $value;
        }

        return $redacted;
    }

    protected function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach ($this->sensitiveKeys() as $sensitiveKey) {
            if ($normalized === strtolower($sensitiveKey) || str_contains($normalized, strtolower($sensitiveKey))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    protected function sensitiveKeys(): array
    {
        return [
            'password',
            'password_confirmation',
            'current_password',
            '_token',
            'token',
            'otp',
            'pin',
            'code',
            'access_token',
            'refresh_token',
            'client_secret',
            'authorization',
            'api_key',
            'apikey',
            'secret',
        ];
    }

    protected function extractHeaders(Request $request): array
    {
        return [
            'accept' => $request->headers->get('accept'),
            'content_type' => $request->headers->get('content-type'),
            'x_forwarded_for' => $request->headers->get('x-forwarded-for'),
            'x_real_ip' => $request->headers->get('x-real-ip'),
            'host' => $request->headers->get('host'),
            'origin' => $request->headers->get('origin'),
        ];
    }

    protected function extractResponseHeaders(Response $response): array
    {
        return [
            'content_type' => $response->headers->get('content-type'),
            'cache_control' => $response->headers->get('cache-control'),
            'x_rate_limit_limit' => $response->headers->get('x-ratelimit-limit'),
            'x_rate_limit_remaining' => $response->headers->get('x-ratelimit-remaining'),
            'location' => $response->headers->get('location'),
        ];
    }

    protected function extractResponseBody(Response $response): ?string
    {
        if ($response instanceof StreamedResponse) {
            return '[streamed-response]';
        }

        if ($response instanceof BinaryFileResponse) {
            return '[binary-file-response]';
        }

        try {
            $content = $response->getContent();

            return is_string($content) && $content !== ''
                ? mb_substr($content, 0, 10000)
                : null;
        } catch (Throwable $e) {
            return '[unavailable-response-content]';
        }
    }
}
