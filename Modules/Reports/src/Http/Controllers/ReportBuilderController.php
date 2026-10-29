<?php

namespace Modules\Reports\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Reports\Services\ReportExecutionService;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class ReportBuilderController extends Controller
{
    protected ReportExecutionService $executionService;

    public function __construct(ReportExecutionService $executionService)
    {
        $this->executionService = $executionService;
    }

    /**
     * Show report builder interface.
     */
    public function index()
    {
        $dataSources = $this->executionService->getAvailableDataSources();
        
        return view('reports::builder.index', compact('dataSources'));
    }

    /**
     * Get columns for a data source.
     */
    public function getColumns(Request $request)
    {
        $validated = $request->validate([
            'data_source' => 'required|string',
            'type' => 'required|in:table,model',
        ]);

        try {
            $columns = $this->executionService->getDataSourceColumns(
                $validated['data_source'],
                $validated['type']
            );

            return response()->json([
                'success' => true,
                'columns' => $columns,
            ]);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Preview report results.
     */
    public function preview(Request $request)
    {
        
        try {
            $validated = $request->validate([
                'data_source' => 'required|string',
                'data_source_type' => 'required|in:table,model,custom_query',
                'custom_query' => 'nullable|string',
                'columns' => 'nullable|array',
                'filters' => 'nullable|array',
                'filter_logic' => 'nullable|in:AND,OR',
                'aggregations' => 'nullable|array',
                'group_by' => 'nullable|array',
                'order_by' => 'nullable|array',
                'limit' => 'nullable|integer|min:1|max:1000',
            ]);
            // Create temporary report object for preview
            $tempReport = new \Modules\Reports\Models\Report($validated);
            $tempReport->chart_type = $request->input('chart_type', 'table');
            
            // Execute
            $result = $this->executionService->execute($tempReport, [], Auth::user());

            return response()->json([
                'success' => true,
                'data' => $result['data'] ?? [],
                'meta' => $result['meta'] ?? [],
            ]);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Get available aggregation functions.
     */
    public function getAggregations()
    {
        return response()->json([
            'success' => true,
            'aggregations' => [
                ['value' => 'COUNT', 'label' => 'Count'],
                ['value' => 'SUM', 'label' => 'Sum'],
                ['value' => 'AVG', 'label' => 'Average'],
                ['value' => 'MIN', 'label' => 'Minimum'],
                ['value' => 'MAX', 'label' => 'Maximum'],
            ],
        ]);
    }

    /**
     * Get available filter operators.
     */
    public function getOperators()
    {
        return response()->json([
            'success' => true,
            'operators' => [
                ['value' => 'equals', 'label' => 'Equals (=)', 'types' => ['string', 'number', 'date']],
                ['value' => 'not_equals', 'label' => 'Not Equals (≠)', 'types' => ['string', 'number', 'date']],
                ['value' => 'greater_than', 'label' => 'Greater Than (>)', 'types' => ['number', 'date']],
                ['value' => 'greater_than_or_equal', 'label' => 'Greater Than or Equal (≥)', 'types' => ['number', 'date']],
                ['value' => 'less_than', 'label' => 'Less Than (<)', 'types' => ['number', 'date']],
                ['value' => 'less_than_or_equal', 'label' => 'Less Than or Equal (≤)', 'types' => ['number', 'date']],
                ['value' => 'contains', 'label' => 'Contains', 'types' => ['string']],
                ['value' => 'not_contains', 'label' => 'Does Not Contain', 'types' => ['string']],
                ['value' => 'starts_with', 'label' => 'Starts With', 'types' => ['string']],
                ['value' => 'ends_with', 'label' => 'Ends With', 'types' => ['string']],
                ['value' => 'in', 'label' => 'In List', 'types' => ['string', 'number']],
                ['value' => 'not_in', 'label' => 'Not In List', 'types' => ['string', 'number']],
                ['value' => 'between', 'label' => 'Between', 'types' => ['number', 'date']],
                ['value' => 'is_null', 'label' => 'Is Empty', 'types' => ['string', 'number', 'date']],
                ['value' => 'is_not_null', 'label' => 'Is Not Empty', 'types' => ['string', 'number', 'date']],
            ],
        ]);
    }

    /**
     * Get available chart types.
     */
    public function getChartTypes()
    {
        return response()->json([
            'success' => true,
            'chart_types' => [
                ['value' => 'table', 'label' => 'Table', 'icon' => 'table'],
                ['value' => 'comparison', 'label' => 'Comparison', 'icon' => 'comparison-chart'],
                ['value' => 'bar', 'label' => 'Bar Chart', 'icon' => 'bar-chart'],
                ['value' => 'stat', 'label' => 'Stat', 'icon' => 'stat-chart'],
                ['value' => 'line', 'label' => 'Line Chart', 'icon' => 'line-chart'],
                ['value' => 'pie', 'label' => 'Pie Chart', 'icon' => 'pie-chart'],
                ['value' => 'area', 'label' => 'Area Chart', 'icon' => 'area-chart'],
                ['value' => 'donut', 'label' => 'Donut Chart', 'icon' => 'donut-chart'],
                ['value' => 'scatter', 'label' => 'Scatter Plot', 'icon' => 'scatter-chart'],
            ],
        ]);
    }
}
