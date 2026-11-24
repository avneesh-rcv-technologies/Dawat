<?php

namespace Modules\Activitylog\Services;

use Illuminate\Support\Facades\Log;

class SpatieAuditLogger
{
    public function enabled(): bool
    {
        return (bool) config('activitylog.spatie_audit_enabled', false)
            && class_exists(\Spatie\Activitylog\Models\Activity::class);
    }

    public function log(
        string $event,
        string $description,
        mixed $subject = null,
        array $properties = [],
        mixed $causer = null
    ): void {
        if (! $this->enabled()) {
            return;
        }

        try {
            $logger = activity($event)
                ->event($event)
                ->withProperties($properties);

            if ($subject) {
                $logger->performedOn($subject);
            }

            if ($causer) {
                $logger->causedBy($causer);
            } elseif (auth()->check()) {
                $logger->causedBy(auth()->user());
            }

            $logger->log($description);
        } catch (\Throwable $e) {
            Log::warning('Spatie audit log write failed.', [
                'event' => $event,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
