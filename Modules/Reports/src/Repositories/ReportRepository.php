<?php

namespace Modules\Reports\Repositories;

use Modules\Reports\Models\Report;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportRepository
{
    protected Report $model;

    public function __construct()
    {
        $this->model = new Report();
    }

    /**
     * Get all reports.
     */
    public function getAll(): Collection
    {
        return $this->model->with('createdBy')->latest()->get();
    }

    /**
     * Get paginated reports.
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['createdBy', 'latestExecution'])
                          ->latest()
                          ->paginate($perPage);
    }

    /**
     * Find report by ID.
     */
    public function find($id): ?Report
    {
        return $this->model->with(['createdBy', 'schedules', 'executions' => function ($query) {
            $query->latest()->limit(10);
        }])->find($id);
    }

    /**
     * Find report by slug.
     */
    public function findBySlug(string $slug): ?Report
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Create a new report.
     */
    public function create(array $data): Report
    {
        return $this->model->create($data);
    }

    /**
     * Update a report.
     */
    public function update($id, array $data): bool
    {
        $report = $this->find($id);
        return $report ? $report->update($data) : false;
    }

    /**
     * Delete a report.
     */
    public function delete($id): bool
    {
        $report = $this->find($id);
        return $report ? $report->delete() : false;
    }

    /**
     * Get active reports.
     */
    public function getActive(): Collection
    {
        return $this->model->active()->with('createdBy')->latest()->get();
    }

    /**
     * Get scheduled reports.
     */
    public function getScheduled(): Collection
    {
        return $this->model->scheduled()->with(['createdBy', 'schedules'])->get();
    }

    /**
     * Get reports accessible by user.
     */
    public function getAccessibleByUser(int $userId, array $userRoles = []): Collection
    {
        return $this->model->accessibleBy($userId, $userRoles)
                          ->with('createdBy')
                          ->latest()
                          ->get();
    }

    /**
     * Get reports by data source.
     */
    public function getByDataSource(string $dataSource): Collection
    {
        return $this->model->byDataSource($dataSource)->get();
    }

    /**
     * Search reports.
     */
    public function search(string $query): Collection
    {
        return $this->model->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%")
              ->orWhere('data_source', 'like', "%{$query}%");
        })->with('createdBy')->get();
    }

    /**
     * Get report statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->active()->count(),
            'scheduled' => $this->model->scheduled()->count(),
            'by_chart_type' => $this->model->selectRaw('chart_type, COUNT(*) as count')
                                          ->groupBy('chart_type')
                                          ->pluck('count', 'chart_type')
                                          ->toArray(),
            'by_data_source' => $this->model->selectRaw('data_source, COUNT(*) as count')
                                           ->groupBy('data_source')
                                           ->pluck('count', 'data_source')
                                           ->toArray(),
        ];
    }

    /**
     * Duplicate a report.
     */
    public function duplicate($id, $user): ?Report
    {
        $original = $this->find($id);
        
        if (!$original) {
            return null;
        }

        $duplicate = $original->replicate();
        $duplicate->name = $original->name . ' (Copy)';
        $duplicate->slug = $original->slug . '-copy-' . time();
        $duplicate->created_by_id = $user->id ?? $user;
        $duplicate->created_by_type = get_class($user) ?? 'Modules\Acl\Models\Admin';
        $duplicate->execution_count = 0;
        $duplicate->last_executed_at = null;
        $duplicate->save();

        return $duplicate;
    }
}
