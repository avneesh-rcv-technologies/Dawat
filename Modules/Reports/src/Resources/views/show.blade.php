@extends('backend::layouts.app')

@section('title', $report->name)

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index')],
    ['label' => 'Reports', 'url' => route('reports.index')],
    ['label' => $report->name, 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$report->name" />
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-topbar rounded-lg shadow-lg p-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-3xl font-bold">{{ $report->name }}</h1>
                    @if(!$report->is_active)
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-red-500 text-white">Inactive</span>
                    @endif
                    @if($report->is_public)
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-500 text-white">Public</span>
                    @else
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-yellow-500 text-white">Private</span>
                    @endif
                </div>
                @if($report->description)
                    <p class="text-blue-100 text-lg">{{ $report->description }}</p>
                @endif
                <div class="flex items-center gap-6 mt-4 text-sm text-blue-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                        <span>{{ ucfirst($report->data_source) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>{{ ucfirst($report->chart_type) }} Chart</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Executed {{ $report->execution_count }} times</span>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                @if(bouncer($guard)->hasPermission('reports.execute'))
                <form action="{{ route('reports.execute', $report->id) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="bg-black px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 shadow-sm transition-colors inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Regenerate
                    </button>
                </form>
                @endif
                @if(bouncer($guard)->hasPermission('reports.edit'))
                <a href="{{ route('reports.edit', $report->id) }}" class="bg-black px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 shadow-sm transition-colors inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                @endif
                @if(bouncer($guard)->hasPermission('reports.export'))
                <form action="{{ route('reports.export', $report->id) }}" method="POST">
                    @csrf
                    <div class="flex items-center gap-3">
                        <div>                            
                            <select name="format" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="csv">CSV</option>
                                <option value="excel">Excel (XLSX)</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                        <div class="flex gap-3 justify-end">                        
                            <button type="submit" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-blue-700">
                                Export
                            </button>
                        </div>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Execution Statistics -->
    @if($result['success'])
    <div class="grid grid-cols-1 md:grid-cols-4  gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">Total Rows</div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($result['meta']['total_rows'] ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">Execution Time</div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($result['meta']['execution_time_ms'] ?? 0) }} ms</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">Status</div>
            <div class="text-2xl font-bold text-green-600">{{ $result['meta']['is_cached'] ? 'Cached' : 'Fresh' }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">Last Updated</div>
            <div class="text-lg font-bold text-gray-900">{{ now()->format('H:i:s') }}</div>
        </div>
    </div>
    @endif

    <!-- Error Display -->
    @if(!$result['success'])
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-lg font-semibold text-red-900 mb-2">Report Execution Failed</h3>
                <p class="text-red-700">{{ $result['error'] ?? 'An unknown error occurred' }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Chart Visualization -->
    @if($result['success'] && $report->chart_type !== 'table' && !empty($result['data']))
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Visualization</h2>
        <div style="position: relative; height: 400px;">
            <canvas id="reportChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Data Table -->
    @if($result['success'] && !empty($result['data']))
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Report Data</h2>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <span>Showing {{ count($result['data']) }} rows</span>
                    @if($report->limit && count($result['data']) >= $report->limit)
                        <span class="text-yellow-600">(Limited to {{ $report->limit }})</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach(array_keys((array)$result['data'][0]) as $column)
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ ucwords(str_replace('_', ' ', $column)) }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($result['data'] as $row)
                    @php
                        $rowArray = is_object($row) ? (array)$row : $row;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        @foreach($rowArray as $value)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if(is_numeric($value))
                                {{ number_format($value, is_float($value) ? 2 : 0) }}
                            @elseif(is_bool($value))
                                <span class="px-2 py-1 text-xs rounded-full {{ $value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $value ? 'Yes' : 'No' }}
                                </span>
                            @elseif(is_null($value))
                                <span class="text-gray-400 italic">NULL</span>
                            @else
                                {{ $value }}
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif($result['success'] && empty($result['data']))
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Data Available</h3>
        <p class="text-gray-600">This report returned no results. Try adjusting your filters or data source.</p>
    </div>
    @endif

    <!-- Report Configuration -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Report Configuration</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Data Source</h3>
                <p class="text-sm text-gray-900">{{ $report->data_source }} ({{ $report->data_source_type }})</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Chart Type</h3>
                <p class="text-sm text-gray-900">{{ ucfirst($report->chart_type) }}</p>
            </div>
            @if($report->filters && count($report->filters) > 0)
            <div class="md:col-span-2">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Filters Applied</h3>
                <div class="space-y-2">
                    @foreach($report->filters as $filter)
                    @php
                        $rawValue = $filter['value'] ?? null;

                        if (is_array($rawValue)) {
                            $displayValue = implode(', ', array_map(static fn($item) => is_scalar($item) ? (string) $item : json_encode($item), $rawValue));
                        } elseif (is_scalar($rawValue) || $rawValue === null) {
                            $displayValue = $rawValue ?? '';
                        } else {
                            $displayValue = json_encode($rawValue);
                        }

                        if (($filter['operator'] ?? '') === 'between') {
                            $displayValue = trim(($filter['value_min'] ?? '') . ' - ' . ($filter['value_max'] ?? ''));
                        }
                    @endphp
                    <div class="flex items-center gap-2 text-sm text-gray-900 bg-gray-50 rounded px-3 py-2">
                        <span class="font-medium">{{ $filter['column'] ?? '' }}</span>
                        <span class="text-gray-500">{{ $filter['operator'] ?? '' }}</span>
                        <span class="font-medium">{{ $displayValue }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Created By</h3>
                <p class="text-sm text-gray-900">{{ $report->createdBy->name ?? 'Unknown' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Last Executed</h3>
                <p class="text-sm text-gray-900">{{ $report->last_executed_at ? $report->last_executed_at->diffForHumans() : 'Never' }}</p>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($result['success'] && $report->chart_type !== 'table' && !empty($result['data']))
    const chartData = @json($result['data']);
    const chartType = '{{ $report->chart_type }}';
    const chartConfig = @json($report->chart_config ?? []);
    
    // Prepare data for Chart.js
    const prepareChartData = () => {
        const firstRow = chartData[0];
        const keys = Object.keys(firstRow);
        
        // Determine label and data columns
        const labelColumn = keys[0]; // First column as labels
        const dataColumns = keys.slice(1); // Rest as data
        
        const labels = chartData.map(row => row[labelColumn]);
        
        // Prepare datasets
        const datasets = dataColumns.map((column, index) => {
            const colors = [
                'rgba(147, 51, 234, 0.8)', // Purple
                'rgba(59, 130, 246, 0.8)', // Blue
                'rgba(16, 185, 129, 0.8)', // Green
                'rgba(245, 158, 11, 0.8)', // Orange
                'rgba(239, 68, 68, 0.8)',  // Red
                'rgba(236, 72, 153, 0.8)', // Pink
                'rgba(139, 92, 246, 0.8)', // Violet
            ];
            
            const color = chartConfig.colors?.[index] || colors[index % colors.length];
            
            return {
                label: column.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                data: chartData.map(row => parseFloat(row[column]) || 0),
                backgroundColor: chartType === 'pie' || chartType === 'donut' ? 
                    colors.slice(0, chartData.length) : color,
                borderColor: color.replace('0.8', '1'),
                borderWidth: 2,
                fill: chartType === 'area' ? true : false,
                tension: chartType === 'line' || chartType === 'area' ? 0.4 : 0,
            };
        });
        
        return { labels, datasets };
    };
    
    const { labels, datasets } = prepareChartData();
    
    // Map chart types
    const chartTypeMap = {
        'bar': 'bar',
        'line': 'line',
        'pie': 'pie',
        'donut': 'doughnut',
        'area': 'line',
    };
    
    const ctx = document.getElementById('reportChart');
    if (ctx) {
        new Chart(ctx, {
            type: chartTypeMap[chartType] || 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: '{{ $report->name }}',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-US', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 2
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: chartType !== 'pie' && chartType !== 'donut' ? {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('en-US', {
                                    notation: 'compact',
                                    compactDisplay: 'short'
                                }).format(value);
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                } : {},
                // Special config for donut
                cutout: chartType === 'donut' ? '60%' : undefined,
            }
        });
    }
    @endif
});
</script>
@endsection
@endsection
