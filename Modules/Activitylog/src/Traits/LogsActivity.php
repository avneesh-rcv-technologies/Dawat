<?php

namespace Modules\Activitylog\Traits;

use Modules\Activitylog\Services\ActivityLoggerService;
use Spatie\Activitylog\Traits\LogsActivity as SpatieLogsActivity;
use Spatie\Activitylog\LogOptions;

trait LogsActivity
{
    use SpatieLogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->getLogAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => $this->getActivityDescription($eventName));
    }

    protected function getLogAttributes(): array
    {
        // Override in your models to specify which attributes to log
        return ['*'];
    }

    protected function getActivityDescription(string $eventName): string
    {
        $modelName = class_basename($this);
        $userName = auth()->user()->name ?? 'System';
        
        return match($eventName) {
            'created' => "{$userName} created {$modelName}: {$this->getDisplayName()}",
            'updated' => "{$userName} updated {$modelName}: {$this->getDisplayName()}",
            'deleted' => "{$userName} deleted {$modelName}: {$this->getDisplayName()}",
            default => "{$userName} performed {$eventName} on {$modelName}: {$this->getDisplayName()}"
        };
    }

    protected function getDisplayName(): string
    {
        // Override in your models to provide a meaningful display name
        return $this->name ?? $this->title ?? $this->id ?? 'Unknown';
    }

    // Custom logging methods
    public function logCustomActivity(string $description, array $properties = []): void
    {
        ActivityLoggerService::log($description, $this, $properties);
    }

    public function logCriticalActivity(string $description, array $properties = []): void
    {
        ActivityLoggerService::logCritical($description, $this, $properties);
    }
}