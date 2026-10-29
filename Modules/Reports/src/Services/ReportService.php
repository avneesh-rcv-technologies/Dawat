<?php

namespace Modules\Reports\Services;

use Modules\Reports\Models\Report;
use Modules\Reports\Repositories\ReportRepository;
use Illuminate\Support\Str;
use Exception;

class ReportService
{
    protected ReportRepository $repository;

    public function __construct(ReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new report.
     */
    public function create(array $data, $user): Report
    {
        // Generate slug if not provided
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . time();
        }

        // Ensure slug is unique
        $data['slug'] = $this->ensureUniqueSlug($data['slug']);

        // Set creator using polymorphic relationship
        $data['created_by_id'] = $user->id ?? $user;
        $data['created_by_type'] = get_class($user) ?? 'Modules\Acl\Models\Admin';

        // Validate data source
        $this->validateDataSource($data);

        // Validate columns and filters
        $this->validateConfiguration($data);

        return $this->repository->create($data);
    }

    /**
     * Update a report.
     */
    public function update($id, array $data): bool
    {
        // Validate if updating data source or configuration
        if (isset($data['data_source']) || isset($data['columns']) || isset($data['filters'])) {
            $this->validateConfiguration($data);
        }

        return $this->repository->update($id, $data);
    }

    /**
     * Delete a report.
     */
    public function delete($id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Duplicate a report.
     */
    public function duplicate($id, $user): ?Report
    {
        return $this->repository->duplicate($id, $user);
    }

    /**
     * Toggle report active status.
     */
    public function toggleActive($id): bool
    {
        $report = $this->repository->find($id);
        if (!$report) {
            return false;
        }

        return $this->repository->update($id, ['is_active' => !$report->is_active]);
    }

    /**
     * Share report with users or roles.
     */
    public function shareReport($id, array $users = [], array $roles = []): bool
    {
        return $this->repository->update($id, [
            'allowed_users' => $users,
            'allowed_roles' => $roles,
        ]);
    }

    /**
     * Make report public.
     */
    public function makePublic($id): bool
    {
        return $this->repository->update($id, ['is_public' => true]);
    }

    /**
     * Make report private.
     */
    public function makePrivate($id): bool
    {
        return $this->repository->update($id, ['is_public' => false]);
    }

    /**
     * Get report statistics.
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Validate data source exists.
     */
    protected function validateDataSource(array $data): void
    {
        if (!isset($data['data_source'])) {
            throw new Exception('Data source is required');
        }

        $type = $data['data_source_type'] ?? 'table';

        if ($type === 'custom_query') {
            if (!isset($data['custom_query']) || empty($data['custom_query'])) {
                throw new Exception('Custom query is required for custom_query type');
            }
            // Basic SQL injection prevention
            $this->validateCustomQuery($data['custom_query']);
        }
    }

    /**
     * Validate custom SQL query (basic security check).
     */
    protected function validateCustomQuery(string $query): void
    {
        $query = strtolower($query);
        
        // Block dangerous SQL commands
        $dangerousKeywords = ['drop', 'truncate', 'delete', 'insert', 'update', 'alter', 'create'];
        
        foreach ($dangerousKeywords as $keyword) {
            if (str_contains($query, $keyword)) {
                throw new Exception("Custom query cannot contain '{$keyword}' command");
            }
        }

        // Must start with SELECT
        if (!str_starts_with(trim($query), 'select')) {
            throw new Exception('Custom query must be a SELECT statement');
        }
    }

    /**
     * Validate report configuration.
     */
    protected function validateConfiguration(array $data): void
    {
        // Validate columns format
        if (isset($data['columns']) && !is_array($data['columns'])) {
            throw new Exception('Columns must be an array');
        }

        // Validate filters format
        if (isset($data['filters']) && !is_array($data['filters'])) {
            throw new Exception('Filters must be an array');
        }

        // Validate chart type
        if (isset($data['chart_type'])) {
            $validChartTypes = ['table', 'stat', 'comparison', 'bar', 'line', 'pie', 'area', 'donut', 'scatter'];
            if (!in_array($data['chart_type'], $validChartTypes)) {
                throw new Exception('Invalid chart type');
            }
        }

        // Validate filter logic
        if (isset($data['filter_logic'])) {
            $validLogics = ['AND', 'OR'];
            if (!in_array(strtoupper($data['filter_logic']), $validLogics)) {
                throw new Exception('Filter logic must be AND or OR');
            }
        }
    }

    /**
     * Ensure slug is unique.
     */
    protected function ensureUniqueSlug(string $slug, int $attempt = 0): string
    {
        $testSlug = $attempt > 0 ? $slug . '-' . $attempt : $slug;
        
        if ($this->repository->findBySlug($testSlug)) {
            return $this->ensureUniqueSlug($slug, $attempt + 1);
        }

        return $testSlug;
    }

    /**
     * Get suggested columns based on data source.
     */
    public function getSuggestedColumns(string $dataSource, string $type = 'table'): array
    {
        $executionService = new ReportExecutionService();
        return $executionService->getDataSourceColumns($dataSource, $type);
    }

    /**
     * Validate user access to report.
     */
    public function canUserAccess(Report $report, int $userId, array $userRoles = []): bool
    {
        return $report->canBeAccessedBy($userId, $userRoles);
    }
}
