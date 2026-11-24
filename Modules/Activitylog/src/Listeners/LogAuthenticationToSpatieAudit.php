<?php

namespace Modules\Activitylog\Listeners;

use Illuminate\Auth\Events\Login;
use Modules\Activitylog\Services\SpatieAuditLogger;

class LogAuthenticationToSpatieAudit
{
    public function __construct(protected SpatieAuditLogger $auditLogger) {}

    public function handle(Login $event): void
    {
        $this->auditLogger->log(
            event: 'login',
            description: 'User authenticated',
            subject: $event->user,
            properties: [
                'guard' => $event->guard,
            ]
        );
    }
}
