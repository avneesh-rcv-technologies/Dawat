<?php

namespace Modules\Reports\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Reports\Models\Report;
use Illuminate\Support\Facades\Auth;
use Modules\Reports\Services\ReportService;
use Modules\Reports\Services\ReportExportService;
use Modules\Reports\Repositories\ReportRepository;
use Modules\Reports\Services\ReportExecutionService;
use Modules\Reports\Jobs\SendReportNowJob;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class ReportController extends Controller
{
    protected ReportRepository $repository;
    protected ReportService $service;
    protected ReportExecutionService $executionService;
    protected ReportExportService $exportService;

    public function __construct(
        ReportRepository $repository,
        ReportService $service,
        ReportExecutionService $executionService,
        ReportExportService $exportService
    ) {
        $this->repository = $repository;
        $this->service = $service;
        $this->executionService = $executionService;
        $this->exportService = $exportService;
    }

    /**
     * Get users based on guard.
     */
    protected function getUsersByGuard(string $guard)
    {
        $guardConfig = config("acl::guards.guards.{$guard}");
        $provider = $guardConfig['provider'] ?? null;

        if (!$provider) {
            return collect([]);
        }

        $providerConfig = config("acl::guards.providers.{$provider}");
        $modelClass = $providerConfig['model'] ?? null;

        if (!$modelClass || !class_exists($modelClass)) {
            return collect([]);
        }

        return $modelClass::select('id', 'name', 'email')->get();
    }

    /**
     * Get guard configuration for dynamic loading.
     */
    public function getGuardConfig(Request $request)
    {
        $guard = $request->get('guard');

        if (!$guard) {
            return response()->json(['success' => false, 'error' => 'Guard is required']);
        }

        $guardConfig = config("acl::guards.guards.{$guard}");
        $provider = $guardConfig['provider'] ?? null;

        if (!$provider) {
            return response()->json(['success' => false, 'error' => 'Invalid guard']);
        }

        $providerConfig = config("acl::guards.providers.{$provider}");
        $modelClass = $providerConfig['model'] ?? null;

        // Determine table name from model
        $usersTable = Auth::getDefaultDriver(); // default
        if ($modelClass && class_exists($modelClass)) {
            try {
                $instance = new $modelClass();
                $usersTable = $instance->getTable();
            } catch (\Exception $e) {
                // Fallback: use provider name + 's'
                $usersTable = $provider . 's';
            }
        } else {
            // Fallback: use provider name + 's'
            $usersTable = $provider . 's';
        }

        return response()->json([
            'success' => true,
            'guard' => $guard,
            'provider' => $provider,
            'model' => $modelClass,
            'users_table' => $usersTable,
        ]);
    }

    /**
     * Display a listing of reports.
     */
    public function index()
    {
        $lists = fn_datagrid(\Modules\Reports\DataView\ReportsGrid::class)->process();
        return view('reports::index', compact('lists'));
    }

    /**
     * Show the form for creating a new report.
     */
    public function create(Request $request)
    {
        $dataSources = $this->executionService->getAvailableDataSources();

        // For create, set default guard to admin
        $guard = Auth::getDefaultDriver();
        $report = null;

        return view('reports::form', compact('dataSources', 'guard', 'report'));
    }

    /**
     * Store a newly created report.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'guards' => 'required|array',
                'guards.*' => 'string',
                'data_source' => 'required|string',
                'data_source_type' => 'required|in:table,model,custom_query',
                'custom_query' => 'nullable|string',
                'columns' => 'nullable|array',
                'filters' => 'nullable|array',
                'filter_logic' => 'nullable|in:AND,OR',
                'aggregations' => 'nullable|array',
                'group_by' => 'nullable|array',
                'order_by' => 'nullable|array',
                'limit' => 'nullable|integer|min:1',
                'chart_type' => 'required|in:table,stat,comparison,bar,line,pie,area,donut,scatter',
                'chart_config' => 'nullable|array',
                'is_public' => 'nullable|boolean',
                'allowed_roles' => 'nullable|array',
                'allowed_users_data' => 'nullable|json',
            ]);

            // Parse allowed_users_data from JSON
            if (isset($validated['allowed_users_data']) && $validated['allowed_users_data'] !== null) {
                $decoded = json_decode($validated['allowed_users_data'], true);
                $validated['allowed_users'] = is_array($decoded) ? $decoded : [];
            } else {
                $validated['allowed_users'] = [];
            }
            unset($validated['allowed_users_data']);

            $currentGuard = Auth::getDefaultDriver();
            $currentUser = Auth::guard($currentGuard)->user();

            $report = $this->service->create($validated, $currentUser);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report created successfully!',
                    'report_id' => $report->id,
                ], 201);
            }
            return redirect()
                ->route('reports.edit', $report->id)
                ->with('success', 'Report created successfully!');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Display the specified report.
     */
    public function show($id, Request $request)
    {
        $report = $this->repository->find($id);

        if (!$report) {
            return redirect()
                ->route('reports.index')
                ->with('error', 'Report not found');
        }

        // Execute report with runtime filters if provided
        $runtimeFilters = $request->input('filters', []);
        $result = $this->executionService->execute($report, $runtimeFilters, Auth::user());

        // Get available columns for filtering
        $availableColumns = $this->executionService->getDataSourceColumns(
            $report->data_source,
            $report->data_source_type
        );

        return view('reports::show', compact('report', 'result', 'availableColumns'));
    }

    /**
     * Show the form for editing the specified report.
     */
    public function edit($id)
    {
        $report = $this->repository->find($id);

        if (!$report) {
            return redirect()
                ->route('reports.index')
                ->with('error', 'Report not found');
        }

        // Check if user owns the report
        $guard = Auth::getDefaultDriver();
        $currentUserId = Auth::guard($guard)->id();
        $currentUser = Auth::guard($guard)->user();

        if ($report->created_by_id !== $currentUserId || $report->created_by_type !== get_class($currentUser)) {
            return redirect()
                ->route('reports.index')
                ->with('error', 'You do not have permission to edit this report');
        }

        $dataSources = $this->executionService->getAvailableDataSources();
        $availableColumns = $this->executionService->getDataSourceColumns(
            $report->data_source,
            $report->data_source_type
        );

        return view('reports::form', compact('report', 'dataSources', 'availableColumns', 'guard'));
    }

    /**
     * Update the specified report.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'guards' => 'required|array',
                'guards.*' => 'string',
                'custom_query' => 'nullable|string',
                'columns' => 'nullable|array',
                'filters' => 'nullable|array',
                'filter_logic' => 'nullable|in:AND,OR',
                'aggregations' => 'nullable|array',
                'group_by' => 'nullable|array',
                'order_by' => 'nullable|array',
                'limit' => 'nullable|integer|min:1',
                'chart_type' => 'required|in:table,stat,comparison,bar,line,pie,area,donut,scatter',
                'chart_config' => 'nullable|array',
                'is_public' => 'nullable|boolean',
                'allowed_roles' => 'nullable|array',
                'allowed_users_data' => 'nullable|json',
                'is_active' => 'nullable|boolean',
            ]);

            // Parse allowed_users_data from JSON
            if (isset($validated['allowed_users_data']) && $validated['allowed_users_data'] !== null && !$validated['is_public']) {
                $decoded = json_decode($validated['allowed_users_data'], true);
                $validated['allowed_users'] = is_array($decoded) ? $decoded : [];
            } else {
                $validated['allowed_users'] = [];
            }
            unset($validated['allowed_users_data']);
            $this->service->update($id, $validated);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report updated successfully!',
                    'report_id' => $id,
                ], 201);
            }
            return redirect()
                ->route('reports.edit', $id)
                ->with('success', 'Report updated successfully!');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Remove the specified report.
     */
    public function destroy($id)
    {
        try {
            $report = $this->repository->find($id);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'error' => 'Report not found',
                ], 404);
            }

            // Check if user owns the report
            $currentGuard = Auth::getDefaultDriver();
            $currentUserId = Auth::guard($currentGuard)->id();
            $currentUser = Auth::guard($currentGuard)->user();

            if ($report->created_by_id !== $currentUserId || $report->created_by_type !== get_class($currentUser)) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete this report',
                ], 403);
            }

            $deleted = $this->service->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to delete report',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Report deleted successfully',
                'redirect_url' => route('reports.index'),
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Restore a trashed report.
     */
    public function restore(Request $request, $id)
    {
        try {
            $report = Report::withTrashed()->find($id);

            if (!$report) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Report not found',
                    ], 404);
                }
                return redirect()
                    ->back()
                    ->with('error', 'Report not found');
            }

            // Check if user owns the report
            $currentGuard = Auth::getDefaultDriver();
            $currentUserId = Auth::guard($currentGuard)->id();
            $currentUser = Auth::guard($currentGuard)->user();

            if ($report->created_by_id !== $currentUserId || $report->created_by_type !== get_class($currentUser)) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'You do not have permission to restore this report',
                    ], 403);
                }
                return redirect()
                    ->back()
                    ->with('error', 'You do not have permission to restore this report');
            }

            if (!$report->deleted_at) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'This report is not trashed',
                    ], 400);
                }
                return redirect()
                    ->back()
                    ->with('error', 'This report is not trashed');
            }

            $report->restore();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report restored successfully',
                    'redirect_url' => route('reports.index'),
                ]);
            }
            return redirect()
                ->back()
                ->with('success', 'Report restored successfully');
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Duplicate a report.
     */
    public function duplicate($id)
    {
        try {
            $newReport = $this->service->duplicate($id, Auth::user());

            return redirect()
                ->route('reports.edit', $newReport->id)
                ->with('success', 'Report duplicated successfully!');
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Toggle report active status.
     */
    public function toggleActive($id)
    {
        try {
            $this->service->toggleActive($id);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Execute/Regenerate report.
     */
    public function execute($id)
    {
        $report = $this->repository->find($id);

        if (!$report) {
            return redirect()
                ->route('reports.index')
                ->with('error', 'Report not found');
        }

        try {
            // Execute report
            $result = $this->executionService->execute(
                $report,
                [],
                Auth::user()
            );

            if ($result['success']) {
                return back()->with('success', 'Report regenerated successfully!');
            }

            return back()
                ->with('error', $result['error'] ?? 'Report execution failed');
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Queue immediate report sending via email.
     */
    public function sendNow($id)
    {
        $report = $this->repository->find($id);

        if (!$report) {
            return redirect()
                ->route('reports.index')
                ->with('error', 'Report not found');
        }

        try {
            SendReportNowJob::dispatch((int) $report->id);

            return back()->with('success', 'Report queued for sending.');
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Get preview data for dashboard charts.
     */
    public function previewData(Request $request, $id)
    {
        $report = $this->repository->find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'error' => 'Report not found'
            ], 404);
        }

        try {
            // Build runtime filters from request
            $runtimeFilters = [];

            if ($request->has('start_date') && $request->has('end_date')) {
                $runtimeFilters['date_range'] = [
                    'start' => $request->input('start_date'),
                    'end' => $request->input('end_date')
                ];
            }

            // Execute report with limited results for preview
            $result = $this->executionService->execute(
                $report,
                $runtimeFilters,
                Auth::user()
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to execute report'
                ], 400);
            }

            // Format data for chart
            $chartData = $this->formatChartData($result['data'], $report);
            $response = [
                'success' => true,
                'data' => $chartData,
                'chart_type' => $report->chart_type,
                'chart_config' => $report->chart_config ?? [],
                'report_name' => $report->name
            ];

            // For table reports, include full data rows (limited to 10)
            if ($report->chart_type === 'table') {
                $response['full_data'] = array_slice($result['data'], 0, 10);
            }

            // For stat reports, include full data for value extraction
            if ($report->chart_type === 'stat') {
                $response['full_data'] = array_slice($result['data'], 0, 1);
            }

            // For comparison reports, include full data for comparison calculation
            if ($report->chart_type === 'comparison') {
                $response['full_data'] = array_slice($result['data'], 0, 10);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Format report data for chart display.
     */
    protected function formatChartData(array $data, Report $report): array
    {
        if (empty($data)) {
            return ['labels' => [], 'values' => []];
        }

        // Get first 10 rows for preview
        $previewData = array_slice($data, 0, 10);

        // Get first row to determine available columns
        $firstRow = reset($previewData);
        if (empty($firstRow)) {
            return ['labels' => [], 'values' => []];
        }

        // Convert stdClass to array if needed
        if (is_object($firstRow)) {
            $firstRow = (array) $firstRow;
            $previewData = array_map(function ($row) {
                return is_object($row) ? (array) $row : $row;
            }, $previewData);
        }

        $availableColumns = array_keys($firstRow);

        // Try to extract column names from report configuration
        $columns = $report->columns ?? [];
        $labelColumn = null;
        $valueColumn = null;

        // Handle different column structures
        if (!empty($columns)) {
            // Check if columns is array of strings or objects
            if (is_string($columns[0])) {
                $labelColumn = $columns[0];
                $valueColumn = $columns[1] ?? null;
            } elseif (is_array($columns[0])) {
                // Column is an object/array with 'name', 'column', or 'alias'
                // Prefer 'name' as it's the actual database column
                $labelColumn = $columns[0]['name'] ?? $columns[0]['alias'] ?? $columns[0]['column'] ?? null;
                if (isset($columns[1])) {
                    $valueColumn = $columns[1]['name'] ?? $columns[1]['alias'] ?? $columns[1]['column'] ?? null;
                }
            }
        }

        // Fallback: use first two columns from actual data
        if (!$labelColumn || !in_array($labelColumn, $availableColumns)) {
            $labelColumn = $availableColumns[0] ?? 'label';
        }
        if (!$valueColumn || !in_array($valueColumn, $availableColumns)) {
            // Try to find a numeric column for value
            $numericColumn = null;
            foreach ($availableColumns as $col) {
                if ($col !== $labelColumn && isset($firstRow[$col]) && is_numeric($firstRow[$col])) {
                    $numericColumn = $col;
                    break;
                }
            }
            $valueColumn = $numericColumn ?? ($availableColumns[1] ?? $availableColumns[0] ?? 'value');
        }

        $labels = [];
        $values = [];

        foreach ($previewData as $row) {
            $label = $row[$labelColumn] ?? 'N/A';
            $value = $row[$valueColumn] ?? 0;

            // Truncate long labels
            if (is_string($label) && strlen($label) > 20) {
                $label = substr($label, 0, 17) . '...';
            }

            $labels[] = (string) $label;
            $values[] = is_numeric($value) ? (float) $value : 0;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Export report.
     */
    public function export(Request $request, $id)
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,excel,pdf',
            'filters' => 'nullable|array',
        ]);

        $report = $this->repository->find($id);

        if (!$report) {
            return back()->with('error', 'Report not found');
        }

        try {
            // Execute report
            $result = $this->executionService->execute(
                $report,
                $validated['filters'] ?? [],
                Auth::user()
            );

            if (!$result['success']) {
                return back()->with('error', $result['error'] ?? 'Report execution failed');
            }

            // Export
            $export = $this->exportService->export(
                $report,
                $validated['format'],
                $result['data'],
                Auth::user(),
                $result['execution_id'] ?? null
            );

            // Download immediately
            return $this->exportService->download($export);
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Get report statistics.
     */
    public function statistics()
    {
        try {
            $stats = $this->service->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Bulk delete reports.
     */
    /**
     * Bulk delete reports.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:reports,id',
        ]);

        try {
            $deletedCount = $failedCount = 0;
            foreach ($validated['ids'] as $id) {
                // Check if user owns the report
                $currentGuard = Auth::getDefaultDriver();
                $currentUserId = Auth::guard($currentGuard)->id();
                $currentUser = Auth::guard($currentGuard)->user();

                $report = $this->repository->find($id);
                if ($report->created_by_id === $currentUserId && $report->created_by_type === get_class($currentUser)) {
                    $this->service->delete($id);
                    $deletedCount++;
                } else {
                    $failedCount++;
                }
            }
            $message = "{$deletedCount} reports deleted successfully!";
            if ($failedCount > 0) {
                $message .= " ({$failedCount} failed or unauthorized)";
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect_url' => route('reports.index'),
                ]);
            }

            return redirect()
                ->route('reports.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Bulk restore trashed reports.
     */
    public function bulkRestore(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        try {
            $restoredCount = 0;
            $failedCount = 0;

            foreach ($validated['ids'] as $id) {
                $report = Report::withTrashed()->find($id);

                if (!$report || !$report->deleted_at) {
                    $failedCount++;
                    continue;
                }

                // Check if user owns the report
                $currentGuard = Auth::getDefaultDriver();
                $currentUserId = Auth::guard($currentGuard)->id();
                $currentUser = Auth::guard($currentGuard)->user();

                if ($report->created_by_id === $currentUserId && $report->created_by_type === get_class($currentUser)) {
                    $report->restore();
                    $restoredCount++;
                } else {
                    $failedCount++;
                }
            }

            $message = "{$restoredCount} reports restored successfully!";
            if ($failedCount > 0) {
                $message .= " ({$failedCount} failed or unauthorized)";
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect_url' => route('reports.index'),
                ]);
            }

            return redirect()
                ->route('reports.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Bulk force delete trashed reports.
     */
    public function bulkForceDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        try {
            $deletedCount = 0;
            $failedCount = 0;

            foreach ($validated['ids'] as $id) {
                $report = Report::withTrashed()->find($id);

                if (!$report || !$report->deleted_at) {
                    $failedCount++;
                    continue;
                }

                // Check if user owns the report
                $currentGuard = Auth::getDefaultDriver();
                $currentUserId = Auth::guard($currentGuard)->id();
                $currentUser = Auth::guard($currentGuard)->user();

                if ($report->created_by_id === $currentUserId && $report->created_by_type === get_class($currentUser)) {
                    $report->forceDelete();
                    $deletedCount++;
                } else {
                    $failedCount++;
                }
            }

            $message = "{$deletedCount} reports permanently deleted!";
            if ($failedCount > 0) {
                $message .= " ({$failedCount} failed or unauthorized)";
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect_url' => route('reports.index'),
                ]);
            }

            return redirect()
                ->route('reports.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Get user roles (helper method).
     */
    protected function getUserRoles(): array
    {
        // This should be implemented based on your role system
        // For now, returning empty array
        return [];
    }

    public function downloadLink($exportId)
    {
        try {
            return $this->exportService->downloadByExportId($exportId);
        } catch (\Exception $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
