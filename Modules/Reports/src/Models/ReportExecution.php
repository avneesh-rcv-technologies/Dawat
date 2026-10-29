<?php

namespace Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReportExecution extends Model
{
    protected $fillable = [
        'report_id',
        'executed_by_id',
        'executed_by_type',
        'schedule_id',
        'status',
        'rows_returned',
        'execution_time_ms',
        'error_message',
        'applied_filters',
        'result_hash',
        'is_cached',
        'cache_expires_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'applied_filters' => 'array',
        'is_cached' => 'boolean',
        'cache_expires_at' => 'datetime',
    ];

    /**
     * Get the report that was executed.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the user who executed the report.
     */
    public function user(): MorphTo
    {
        return $this->morphTo('executed_by');
    }

    /**
     * Alias for executed_by relation.
     */
    public function executedBy(): MorphTo
    {
        return $this->user();
    }

    /**
     * Get the schedule that triggered this execution.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ReportSchedule::class);
    }

    /**
     * Scope to get completed executions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get failed executions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get cached executions.
     */
    public function scopeCached($query)
    {
        return $query->where('is_cached', true)
                    ->where('cache_expires_at', '>', now());
    }

    /**
     * Scope to filter by report.
     */
    public function scopeForReport($query, int $reportId)
    {
        return $query->where('report_id', $reportId);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, Model|int $user, ?string $userType = null)
    {
        if ($user instanceof Model) {
            $userType = $userType ?: $user->getMorphClass();
            $userId = $user->getKey();
        } else {
            $userId = $user;
        }

        $query->where('executed_by_id', $userId);

        if ($userType) {
            $query->where('executed_by_type', $userType);
        }

        return $query;
    }

    /**
     * Mark execution as completed.
     */
    public function markAsCompleted(int $rowsReturned, int $executionTime): void
    {
        $this->update([
            'status' => 'completed',
            'rows_returned' => $rowsReturned,
            'execution_time_ms' => $executionTime,
        ]);
    }

    /**
     * Mark execution as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Check if execution is still cached.
     */
    public function isCacheValid(): bool
    {
        return $this->is_cached && $this->cache_expires_at && $this->cache_expires_at->isFuture();
    }

    /**
     * Get execution time in seconds.
     */
    public function getExecutionTimeSecondsAttribute(): ?float
    {
        return $this->execution_time_ms ? round($this->execution_time_ms / 1000, 2) : null;
    }

    /**
     * Get status label with badge color.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'completed' => ['text' => 'Completed', 'color' => 'green'],
            'failed' => ['text' => 'Failed', 'color' => 'red'],
            'running' => ['text' => 'Running', 'color' => 'blue'],
            'pending' => ['text' => 'Pending', 'color' => 'yellow'],
            default => ['text' => 'Unknown', 'color' => 'gray'],
        };
    }
}
