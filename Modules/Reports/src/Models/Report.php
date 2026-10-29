<?php

namespace Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'guards',
        'data_source',
        'data_source_type',
        'custom_query',
        'columns',
        'filters',
        'filter_logic',
        'aggregations',
        'group_by',
        'order_by',
        'limit',
        'chart_type',
        'chart_config',
        'created_by_id',
        'created_by_type',
        'is_public',
        'allowed_roles',
        'allowed_users',
        'is_active',
        'is_scheduled',
        'execution_count',
        'last_executed_at',
        'metadata',
    ];

    protected $casts = [
        'guards' => 'array',
        'columns' => 'array',
        'filters' => 'array',
        'aggregations' => 'array',
        'group_by' => 'array',
        'order_by' => 'array',
        'chart_config' => 'array',
        'allowed_roles' => 'array',
        'allowed_users' => 'array',
        'metadata' => 'array',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'is_scheduled' => 'boolean',
        'last_executed_at' => 'datetime',
    ];

    /**
     * Get the creator of the report (polymorphic).
     */
    public function createdBy(): MorphTo
    {
        return $this->morphTo('created_by');
    }

    /**
     * Get the report schedules.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(ReportSchedule::class);
    }

    /**
     * Get the report executions.
     */
    public function executions(): HasMany
    {
        return $this->hasMany(ReportExecution::class);
    }

    /**
     * Get the report exports.
     */
    public function exports(): HasMany
    {
        return $this->hasMany(ReportExport::class);
    }

    /**
     * Get the saved filters for this report.
     */
    public function savedFilters(): HasMany
    {
        return $this->hasMany(ReportSavedFilter::class);
    }

    /**
     * Get the latest execution.
     */
    public function latestExecution()
    {
        return $this->hasOne(ReportExecution::class)->latestOfMany();
    }

    /**
     * Scope to get active reports.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get scheduled reports.
     */
    public function scopeScheduled($query)
    {
        return $query->where('is_scheduled', true);
    }

    /**
     * Scope to get public reports.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter by data source.
     */
    public function scopeByDataSource($query, string $dataSource)
    {
        return $query->where('data_source', $dataSource);
    }

    /**
     * Scope to filter by chart type.
     */
    public function scopeByChartType($query, string $chartType)
    {
        return $query->where('chart_type', $chartType);
    }

    /**
     * Scope to get reports accessible by a user.
     */
    public function scopeAccessibleBy($query, $userId, array $userRoles = [])
    {
        return $query->where(function ($q) use ($userId, $userRoles) {
            $q->where('is_public', true)
              ->orWhere('created_by', $userId)
              ->orWhereJsonContains('allowed_users', (string) $userId)
              ->orWhere(function ($subQuery) use ($userRoles) {
                  foreach ($userRoles as $role) {
                      $subQuery->orWhereJsonContains('allowed_roles', $role);
                  }
              });
        });
    }

    /**
     * Check if user can access this report.
     */
    public function canBeAccessedBy($userId, array $userRoles = []): bool
    {
        if ($this->is_public || $this->created_by == $userId) {
            return true;
        }

        // Check if user is in allowed_users (structured array with id, guard, model, name)
        if ($this->allowed_users && is_array($this->allowed_users)) {
            foreach ($this->allowed_users as $allowedUser) {
                if (is_array($allowedUser) && isset($allowedUser['id']) && $allowedUser['id'] == $userId) {
                    return true;
                }
            }
        }

        // Check if user's role is in allowed_roles
        if ($this->allowed_roles && is_array($this->allowed_roles) && !empty($userRoles)) {
            if (array_intersect($userRoles, $this->allowed_roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Increment execution count.
     */
    public function incrementExecutionCount(): void
    {
        $this->increment('execution_count');
        $this->update(['last_executed_at' => now()]);
    }

    /**
     * Get display name for data source.
     */
    public function getDataSourceDisplayNameAttribute(): string
    {
        return str_replace('_', ' ', ucwords($this->data_source, '_'));
    }

    /**
     * Get chart type label.
     */
    public function getChartTypeLabelAttribute(): string
    {
        return ucfirst($this->chart_type);
    }
}
