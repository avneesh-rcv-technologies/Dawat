<?php

namespace Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportSchedule extends Model
{
    protected $fillable = [
        'report_id',
        'frequency',
        'cron_expression',
        'scheduled_time',
        'scheduled_days',
        'scheduled_date',
        'recipients',
        'export_format',
        'email_subject',
        'email_body',
        'is_active',
        'last_run_at',
        'next_run_at',
        'run_count',
        'failure_count',
    ];

    protected $casts = [
        'scheduled_days' => 'array',
        'recipients' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'scheduled_time' => 'datetime:H:i:s',
    ];

    /**
     * Get the report that owns this schedule.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the executions triggered by this schedule.
     */
    public function executions(): HasMany
    {
        return $this->hasMany(ReportExecution::class, 'schedule_id');
    }

    /**
     * Scope to get active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get schedules due to run.
     */
    public function scopeDueForExecution($query)
    {
        return $query->active()
                    ->where('next_run_at', '<=', now());
    }

    /**
     * Increment run count.
     */
    public function incrementRunCount(): void
    {
        $this->increment('run_count');
        $this->update(['last_run_at' => now()]);
    }

    /**
     * Increment failure count.
     */
    public function incrementFailureCount(): void
    {
        $this->increment('failure_count');
    }

    /**
     * Calculate and update next run time.
     */
    public function calculateNextRun(): void
    {
        $nextRun = match ($this->frequency) {
            'daily' => now()->addDay()->setTimeFromTimeString($this->scheduled_time),
            'weekly' => $this->calculateNextWeeklyRun(),
            'monthly' => $this->calculateNextMonthlyRun(),
            'custom' => $this->calculateCustomRun(),
            default => now()->addDay(),
        };

        $this->update(['next_run_at' => $nextRun]);
    }

    /**
     * Calculate next weekly run.
     */
    protected function calculateNextWeeklyRun()
    {
        $currentDay = now()->dayOfWeek;
        $scheduledDays = $this->scheduled_days ?? [];
        
        sort($scheduledDays);
        
        foreach ($scheduledDays as $day) {
            if ($day > $currentDay) {
                return now()->next($day)->setTimeFromTimeString($this->scheduled_time);
            }
        }
        
        // Next week
        return now()->addWeek()->next($scheduledDays[0] ?? 1)->setTimeFromTimeString($this->scheduled_time);
    }

    /**
     * Calculate next monthly run.
     */
    protected function calculateNextMonthlyRun()
    {
        $scheduledDate = $this->scheduled_date ?? 1;
        $nextMonth = now()->addMonth();
        
        return $nextMonth->setDay(min($scheduledDate, $nextMonth->daysInMonth))
                        ->setTimeFromTimeString($this->scheduled_time);
    }

    /**
     * Calculate custom cron run.
     */
    protected function calculateCustomRun()
    {
        // TODO: Implement cron expression parser
        return now()->addDay();
    }

    /**
     * Get frequency label.
     */
    public function getFrequencyLabelAttribute(): string
    {
        return ucfirst($this->frequency);
    }

    /**
     * Get recipients as comma-separated string.
     */
    public function getRecipientsStringAttribute(): string
    {
        return implode(', ', $this->recipients ?? []);
    }
}
