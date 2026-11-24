<?php

namespace Modules\Activitylog\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Activitylog\Http\Middleware\Concerns\InteractsWithRequestThreats;
use Modules\Activitylog\Support\ActivityLogSettings;
use Symfony\Component\HttpFoundation\Response;

class ProtectRequestThreats
{
    use InteractsWithRequestThreats;

    public function handle(Request $request, Closure $next): Response
    {
        if (! ActivityLogSettings::threatProtectionEnabled()) {
            return $next($request);
        }

        $request->attributes->set('activitylog.start', microtime(true));
        $request->attributes->set('activitylog.ip_address', $this->resolveClientIp($request));
        $request->attributes->set('activitylog.is_blocked', false);
        $request->attributes->set('activitylog.blocked_reason', null);

        return $this->applyThreatProtection($request, $next);
    }
}
