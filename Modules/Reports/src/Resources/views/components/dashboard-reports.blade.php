@props(['guard' => null, 'limit' => 10])

@php
    use Modules\Reports\Models\Report;
    use Illuminate\Support\Facades\Auth;
    
    // Use provided guard or get current guard
    $currentGuard = $guard ?? Auth::getDefaultDriver();
    $currentUser = Auth::guard($currentGuard)->user();
    $currentUserId = $currentUser ? $currentUser->id : null;    
    
    // Get user's roles
    $userRoleIds = [];
    
    if ($currentUser && method_exists($currentUser, 'role')) {
        $userRoleIds = $currentUser->role->pluck('id')->toArray();
    }
    
    // Fetch reports accessible to current user
    $accessibleReports = Report::where('is_active', true)
        ->whereJsonContains('guards', $currentGuard)
        ->where(function($query) use ($userRoleIds, $currentUserId, $currentGuard) {
            // Show public reports
            $query->where('is_public', true)
                // OR show private reports with proper access
                ->orWhere(function($privateQuery) use ($userRoleIds, $currentUserId, $currentGuard) {
                    $privateQuery->where('is_public', false);
                    
                    // Must have access through roles OR user assignment
                    $privateQuery->where(function($accessQuery) use ($userRoleIds, $currentUserId, $currentGuard) {
                        // Check if user's role is in allowed_roles (OR logic for multiple roles)
                        if (!empty($userRoleIds)) {
                            foreach ($userRoleIds as $roleId) {
                                $accessQuery->orWhereJsonContains('allowed_roles', $roleId);
                            }
                        }
                        
                        // OR check if user is in allowed_users
                        // Check each element in the JSON array to see if both id and guard match
                        if ($currentUserId) {
                            $accessQuery->orWhereRaw(
                                "EXISTS (
                                    SELECT 1 FROM JSON_TABLE(
                                        allowed_users,
                                        '$[*]' COLUMNS(
                                            user_id INT PATH '$.id',
                                            user_guard VARCHAR(50) PATH '$.guard'
                                        )
                                    ) AS jt
                                    WHERE jt.user_id = ? AND jt.user_guard = ?
                                )",
                                [$currentUserId, $currentGuard]
                            );
                        }
                    });
                });
        })
        ->orderBy('name')
        ->limit($limit)
        ->get();
@endphp

@if($accessibleReports->isNotEmpty())

    <div class="p-4 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">My Dashboard</h3>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Date Range:</label>
                <input type="date" id="dashboard-start-date" class="px-2 py-1 text-sm border border-gray-300 rounded" />
                <span class="text-gray-500">to</span>
                <input type="date" id="dashboard-end-date" class="px-2 py-1 text-sm border border-gray-300 rounded" />
                <button id="apply-date-filter" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                    Apply
                </button>
                <button id="clear-date-filter" class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200 transition-colors">
                    Clear
                </button>
            </div>
            <a href="{{ route('reports.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                View All →
            </a>
        </div>
    </div>
    
    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($accessibleReports as $report)
                <a href="{{ route('reports.show', $report->id) }}" 
                   class="block p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-md transition-all group">
                    <div class="flex items-start justify-between mb-2">
                        <h4 class="font-semibold text-gray-900 text-sm line-clamp-1 group-hover:text-blue-600">
                            {{ $report->name }}
                        </h4>
                    </div>
                    
                    @if($report->description)
                        <p class="text-xs text-gray-600 mb-3 line-clamp-2">{{ $report->description }}</p>
                    @endif
                    
                    <!-- Stat Card for stat chart type -->
                    @if($report->chart_type === 'stat')
                        <div id="stat-{{ $report->id }}" class="stat-container-{{ $report->id }}">
                            <div class="text-center text-gray-500 py-8">Loading...</div>
                        </div>
                    @elseif($report->chart_type === 'comparison')
                        <div id="comparison-{{ $report->id }}" class="comparison-container-{{ $report->id }}">
                            <div class="text-center text-gray-500 py-8">Loading...</div>
                        </div>
                    @elseif(in_array($report->chart_type, ['bar', 'line', 'pie', 'donut', 'area', 'scatter']))
                        <div class="mb-3 bg-gray-50 rounded p-2 chart-container-{{ $report->id }}" style="min-height: 120px; max-height: 300px; height: auto;">
                            <canvas id="chart-{{ $report->id }}" data-chart-type="{{ $report->chart_type }}" style="width: 100%; height: 100%;"></canvas>
                        </div>
                    @elseif($report->chart_type === 'table')
                        <div class="mb-3 bg-gray-50 rounded p-2 table-container-{{ $report->id }}" style="min-height: 80px; max-height: 400px; height: auto; overflow-y: auto;">
                            <div id="table-{{ $report->id }}" class="text-xs">
                                <div class="text-center text-gray-500 py-4">Loading...</div>
                            </div>
                        </div>
                    @endif
                    
                </a>
            @endforeach
        </div>
        
        @if($accessibleReports->count() >= $limit)
            <div class="mt-4 text-center">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                    View All Reports
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        @endif
    </div>


<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const charts = {};
    const reportIds = @json($accessibleReports->pluck('id')->toArray());
    const reportTypes = @json($accessibleReports->pluck('chart_type', 'id')->toArray());
    
    // Color palette
    const colors = [
        { bg: 'rgba(59, 130, 246, 0.2)', border: 'rgba(59, 130, 246, 1)' },
        { bg: 'rgba(16, 185, 129, 0.2)', border: 'rgba(16, 185, 129, 1)' },
        { bg: 'rgba(245, 158, 11, 0.2)', border: 'rgba(245, 158, 11, 1)' },
        { bg: 'rgba(239, 68, 68, 0.2)', border: 'rgba(239, 68, 68, 1)' },
        { bg: 'rgba(139, 92, 246, 0.2)', border: 'rgba(139, 92, 246, 1)' },
        { bg: 'rgba(236, 72, 153, 0.2)', border: 'rgba(236, 72, 153, 1)' },
        { bg: 'rgba(14, 165, 233, 0.2)', border: 'rgba(14, 165, 233, 1)' },
        { bg: 'rgba(132, 204, 22, 0.2)', border: 'rgba(132, 204, 22, 1)' }
    ];
    
    function loadChartData(reportId, startDate = null, endDate = null) {
        const chartType = reportTypes[reportId];
        const canvas = document.getElementById('chart-' + reportId);
        const tableDiv = document.getElementById('table-' + reportId);
        const statDiv = document.getElementById('stat-' + reportId);
        const comparisonDiv = document.getElementById('comparison-' + reportId);
        
        if (!canvas && !tableDiv && !statDiv && !comparisonDiv) return;
        
        // Show loading state
        if (canvas) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#9CA3AF';
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Loading...', canvas.width / 2, canvas.height / 2);
        } else if (tableDiv) {
            tableDiv.innerHTML = '<div class="text-center text-gray-500 py-4">Loading...</div>';
        } else if (statDiv) {
            statDiv.innerHTML = '<div class="text-center text-gray-500 py-8">Loading...</div>';
        } else if (comparisonDiv) {
            comparisonDiv.innerHTML = '<div class="text-center text-gray-500 py-8">Loading...</div>';
        }
        
        // Build URL with filters
        let url = '/api/reports/' + reportId + '/preview-data';
        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (params.toString()) url += '?' + params.toString();
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
            }
            return response.json();
        })
        .then(result => {
            if (result.success && result.data) {
                if (result.chart_type === 'stat') {
                    renderStat(reportId, result.data, result.chart_config || {});
                } else if (result.chart_type === 'comparison') {
                    renderComparison(reportId, result.data, result.full_data || [], result.chart_config || {});
                } else if (result.chart_type === 'table') {
                    renderTable(reportId, result.data, result.full_data || []);
                } else if (result.data.labels && result.data.labels.length > 0) {
                    renderChart(reportId, result.data, result.chart_type);
                } else {
                    if (canvas) showError(canvas, 'No data available');
                    if (tableDiv) tableDiv.innerHTML = '<div class="text-center text-gray-500 py-4">No data available</div>';
                    if (statDiv) statDiv.innerHTML = '<div class="text-center text-gray-500 py-8">No data available</div>';
                    if (comparisonDiv) comparisonDiv.innerHTML = '<div class="text-center text-gray-500 py-8">No data available</div>';
                }
            } else {
                if (canvas) showError(canvas, result.error || 'No data');
                if (tableDiv) tableDiv.innerHTML = '<div class="text-center text-red-500 py-4">' + (result.error || 'No data') + '</div>';
                if (statDiv) statDiv.innerHTML = '<div class="text-center text-red-500 py-8">' + (result.error || 'No data') + '</div>';
                if (comparisonDiv) comparisonDiv.innerHTML = '<div class="text-center text-red-500 py-8">' + (result.error || 'No data') + '</div>';
            }
        })
        .catch(error => {
            if (canvas) showError(canvas, 'Error: ' + error.message);
            if (tableDiv) tableDiv.innerHTML = '<div class="text-center text-red-500 py-4">Error: ' + error.message + '</div>';
            if (statDiv) statDiv.innerHTML = '<div class="text-center text-red-500 py-8">Error: ' + error.message + '</div>';
            if (comparisonDiv) comparisonDiv.innerHTML = '<div class="text-center text-red-500 py-8">Error: ' + error.message + '</div>';
        });
    }
    
    function renderTable(reportId, data, fullData) {
        const tableDiv = document.getElementById('table-' + reportId);
        if (!tableDiv) return;
        
        // Use full_data if available, otherwise use formatted data
        const tableData = fullData.length > 0 ? fullData : [];
        
        if (tableData.length === 0 && data.labels && data.values) {
            // Fallback: construct from labels and values
            tableData.push(...data.labels.map((label, i) => ({
                label: label,
                value: data.values[i]
            })));
        }
        
        if (tableData.length === 0) {
            tableDiv.innerHTML = '<div class="text-center text-gray-500 py-4">No data available</div>';
            return;
        }
        
        // Get column headers from first row
        const firstRow = tableData[0];
        const headers = Object.keys(firstRow);
        
        // Build table HTML
        let tableHTML = '<table class="w-full text-xs">';
        tableHTML += '<thead class="bg-gray-100 sticky top-0"><tr>';
        headers.forEach(header => {
            tableHTML += '<th class="px-2 py-1 text-left font-semibold text-gray-700">' + header + '</th>';
        });
        tableHTML += '</tr></thead><tbody>';
        
        // Show first 5 rows for preview
        tableData.slice(0, 5).forEach((row, index) => {
            tableHTML += '<tr class="' + (index % 2 === 0 ? 'bg-white' : 'bg-gray-50') + ' border-b border-gray-100">';
            headers.forEach(header => {
                const value = row[header] !== null && row[header] !== undefined ? row[header] : '-';
                tableHTML += '<td class="px-2 py-1 text-gray-600">' + value + '</td>';
            });
            tableHTML += '</tr>';
        });
        
        tableHTML += '</tbody></table>';
        
        if (tableData.length > 5) {
            tableHTML += '<div class="text-center text-gray-500 py-1 text-xs">+' + (tableData.length - 5) + ' more rows</div>';
        }
        
        tableDiv.innerHTML = tableHTML;
        
        // Adjust container height based on row count
        const container = document.querySelector('.table-container-' + reportId);
        if (container) {
            const rowCount = Math.min(tableData.length, 5);
            const headerHeight = 32;
            const rowHeight = 28;
            const footerHeight = tableData.length > 5 ? 24 : 0;
            const padding = 16;
            const calculatedHeight = headerHeight + (rowCount * rowHeight) + footerHeight + padding;
            container.style.height = Math.min(calculatedHeight, 400) + 'px';
        }
    }
    
    function renderStat(reportId, data, chartConfig) {
        const statDiv = document.getElementById('stat-' + reportId);
        if (!statDiv) return;
        
        // Extract the stat value - could be in different formats
        let value = 0;
        if (data.values && data.values.length > 0) {
            value = data.values[0];
        } else if (data.full_data && data.full_data.length > 0) {
            const firstRow = data.full_data[0];
            const keys = Object.keys(firstRow);
            value = firstRow[keys[0]];
        }
        
        // Get config
        const icon = chartConfig.icon || 'trending-up';
        const color = chartConfig.color || '#3B82F6';
        const prefix = chartConfig.prefix || '';
        const suffix = chartConfig.suffix || '';
        const format = chartConfig.format || 'number';
        
        // Format value
        let formattedValue = value;
        if (format === 'currency') {
            formattedValue = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value);
        } else if (format === 'number') {
            formattedValue = new Intl.NumberFormat('en-US').format(value);
        }
        
        // Get icon SVG
        const iconSVG = getIconSVG(icon, color);
        
        // Build stat card HTML
        const statHTML = `
            <div class="py-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: ${color}20;">
                        ${iconSVG}
                    </div>
                </div>
                <div class="space-y-1">
                    <div class="text-3xl font-bold text-gray-900">
                        ${prefix}${formattedValue}${suffix}
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-0.5 rounded-full" style="background-color: ${color}15; color: ${color};">
                            <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </span>
                        <span class="text-xs text-gray-500">Updated now</span>
                    </div>
                </div>
            </div>
        `;
        
        statDiv.innerHTML = statHTML;
    }
    
    function getIconSVG(iconName, color) {
        const icons = {
            'users': `<svg class="w-6 h-6" style="color: ${color};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>`,
            'shield': `<svg class="w-6 h-6" style="color: ${color};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>`,
            'trending-up': `<svg class="w-6 h-6" style="color: ${color};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>`,
            'activity': `<svg class="w-6 h-6" style="color: ${color};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>`,
            'clock': `<svg class="w-6 h-6" style="color: ${color};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
            'dollar-sign': `<svg class="w-6 h-6" style="color: ${color};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
        };
        
        return icons[iconName] || icons['trending-up'];
    }
    
    function renderComparison(reportId, data, fullData, chartConfig) {
        const comparisonDiv = document.getElementById('comparison-' + reportId);
        if (!comparisonDiv) return;
        
        // Get comparison type
        const comparisonType = chartConfig.comparison_type || 'side_by_side';
        const showPercentage = chartConfig.show_percentage !== false;
        const showDifference = chartConfig.show_difference !== false;
        const colors = chartConfig.colors || ['#10B981', '#EF4444'];
        
        // Extract comparison values
        let currentValue = 0;
        let previousValue = 0;
        let label = 'Comparison';
        
        if (fullData && fullData.length > 0) {
            // Get data from full_data
            const firstRow = fullData[0];
            const keys = Object.keys(firstRow);
            
            if (keys.length >= 3) {
                label = firstRow[keys[0]] || 'Comparison';
                currentValue = parseFloat(firstRow[keys[1]]) || 0;
                previousValue = parseFloat(firstRow[keys[2]]) || 0;
            }
        } else if (data.values && data.values.length >= 2) {
            // Fallback to formatted data
            currentValue = parseFloat(data.values[0]) || 0;
            previousValue = parseFloat(data.values[1]) || 0;
            label = data.labels[0] || 'Comparison';
        }
        
        // Calculate difference and percentage
        const difference = currentValue - previousValue;
        const percentageChange = previousValue !== 0 ? ((difference / previousValue) * 100) : 0;
        const isPositive = difference >= 0;
        
        // Format numbers
        const formattedCurrent = new Intl.NumberFormat('en-US').format(currentValue);
        const formattedPrevious = new Intl.NumberFormat('en-US').format(previousValue);
        const formattedDifference = new Intl.NumberFormat('en-US', {
            signDisplay: 'always'
        }).format(difference);
        const formattedPercentage = percentageChange.toFixed(1);
        
        // Build comparison HTML
        const comparisonHTML = `
            <div class="py-4 space-y-4">
                <!-- Current vs Previous -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-3">
                        <div class="text-xs text-blue-600 font-medium mb-1">Current</div>
                        <div class="text-2xl font-bold text-blue-900">${formattedCurrent}</div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-3">
                        <div class="text-xs text-gray-600 font-medium mb-1">Previous</div>
                        <div class="text-2xl font-bold text-gray-900">${formattedPrevious}</div>
                    </div>
                </div>
                
                <!-- Change Indicator -->
                <div class="bg-${isPositive ? 'green' : 'red'}-50 rounded-lg p-3 border border-${isPositive ? 'green' : 'red'}-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-${isPositive ? 'green' : 'red'}-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-${isPositive ? 'green' : 'red'}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${isPositive ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6'}"></path>
                                </svg>
                            </div>
                            <div>
                                ${showDifference ? `<div class="text-sm font-bold text-${isPositive ? 'green' : 'red'}-700">${formattedDifference}</div>` : ''}
                                ${showPercentage ? `<div class="text-xs text-${isPositive ? 'green' : 'red'}-600">${isPositive ? '+' : ''}${formattedPercentage}%</div>` : ''}
                            </div>
                        </div>
                        <div class="text-xs text-gray-500">
                            ${comparisonType === 'year_over_year' ? 'YoY' : comparisonType === 'month_over_month' ? 'MoM' : 'vs Previous'}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        comparisonDiv.innerHTML = comparisonHTML;
    }
    
    function renderChart(reportId, data, chartType) {
        const canvas = document.getElementById('chart-' + reportId);
        if (!canvas) return;
        
        // Destroy existing chart
        if (charts[reportId]) {
            charts[reportId].destroy();
        }
        
        const ctx = canvas.getContext('2d');
        const type = chartType === 'donut' ? 'doughnut' : chartType;
        
        // Process data
        const labels = data.labels || [];
        const values = data.values || [];
        
        if (labels.length === 0 || values.length === 0) {
            showError(canvas, 'No data available');
            return;
        }
        
        const backgroundColors = values.map((_, i) => colors[i % colors.length].bg);
        const borderColors = values.map((_, i) => colors[i % colors.length].border);
        
        // Adjust container height based on data points
        const container = document.querySelector('.chart-container-' + reportId);
        if (container) {
            const dataPointCount = labels.length;
            let calculatedHeight = 120;
            
            if (['bar', 'line', 'area'].includes(chartType)) {
                // More data points need more height for readability
                calculatedHeight = Math.max(120, Math.min(dataPointCount * 15, 300));
            } else if (['pie', 'donut'].includes(chartType)) {
                // Pie/donut charts need consistent height
                calculatedHeight = Math.max(150, Math.min(dataPointCount * 12, 250));
            }
            
            container.style.height = calculatedHeight + 'px';
        }
        
        const config = {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 2,
                    fill: type === 'area'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        displayColors: true
                    }
                },
                scales: ['bar', 'line', 'area', 'scatter'].includes(chartType) ? {
                    y: {
                        display: false,
                        beginAtZero: true
                    },
                    x: {
                        display: false
                    }
                } : {}
            }
        };
        
        charts[reportId] = new Chart(ctx, config);
    }
    
    function showError(canvas, message) {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#EF4444';
        ctx.font = '11px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(message, canvas.width / 2, canvas.height / 2);
    }
    
    function refreshAllCharts() {
        const startDate = document.getElementById('dashboard-start-date').value;
        const endDate = document.getElementById('dashboard-end-date').value;
        
        reportIds.forEach(reportId => {
            loadChartData(reportId, startDate, endDate);
        });
    }
    
    // Initial load
    refreshAllCharts();
    
    // Date filter buttons
    document.getElementById('apply-date-filter').addEventListener('click', function() {
        const startDate = document.getElementById('dashboard-start-date').value;
        const endDate = document.getElementById('dashboard-end-date').value;
        
        if (!startDate || !endDate) {
            showToast('Please select both start and end dates', 'info');
            return;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            showToast('Start date must be before end date', 'info');
            return;
        }
        
        refreshAllCharts();
    });
    
    document.getElementById('clear-date-filter').addEventListener('click', function() {
        document.getElementById('dashboard-start-date').value = '';
        document.getElementById('dashboard-end-date').value = '';
        refreshAllCharts();
    });
});
</script>
@endif
