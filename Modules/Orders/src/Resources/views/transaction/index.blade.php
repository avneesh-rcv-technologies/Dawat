@extends('backend::layouts.app')

@section('title', 'Transactions')

@section('breadcrumbs')
    @php
        $title = 'Transactions';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
<!-- Analytics Summary Section -->
<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-900">Transactions Analytics</h2>
        <form method="GET" class="flex items-center gap-2">
            <input type="date" name="date_from" id="trans_date_from" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white" value="{{ request('date_from', date('Y-m-d', strtotime('-30 days'))) }}">
            <input type="date" name="date_to" id="trans_date_to" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white" value="{{ request('date_to', date('Y-m-d')) }}">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors flex items-center gap-1 shadow-sm">
                <i class="fas fa-filter"></i> <span>Filter</span>
            </button>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div id="trans-analytics-summary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Transactions</p>
                    <p class="text-2xl font-bold text-gray-900" id="trans-total">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Amount</p>
                    <p class="text-2xl font-bold text-gray-900" id="trans-total-amount">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Completed</p>
                    <p class="text-2xl font-bold text-gray-900" id="trans-completed">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Success Rate</p>
                    <p class="text-2xl font-bold text-gray-900" id="trans-success-rate">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-percentage text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics Tabs -->
    {{-- <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <div class="flex space-x-1 p-2">
                <button class="trans-tab-btn trans-tab-active px-4 py-2 text-sm font-medium rounded-lg transition-colors" data-tab="trans-status-tab">
                    <i class="fas fa-chart-pie mr-1"></i> By Status
                </button>
                <button class="trans-tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors" data-tab="trans-type-tab">
                    <i class="fas fa-list mr-1"></i> By Type
                </button>
                <button class="trans-tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors" data-tab="trans-trends-tab">
                    <i class="fas fa-chart-line mr-1"></i> Trends
                </button>
                <button class="trans-tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors" data-tab="trans-summary-tab">
                    <i class="fas fa-info-circle mr-1"></i> Summary
                </button>
            </div>
        </div>

        <div class="p-6">
            <div id="trans-status-tab" class="trans-tab-content">
                <h3 class="text-lg font-semibold mb-4">Transactions by Status</h3>
                <div id="trans-status-breakdown" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>

            <div id="trans-type-tab" class="trans-tab-content hidden">
                <h3 class="text-lg font-semibold mb-4">Transactions by Type</h3>
                <div id="trans-type-breakdown" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>

            <div id="trans-trends-tab" class="trans-tab-content hidden">
                <h3 class="text-lg font-semibold mb-4">Daily Performance (Last 7 Days)</h3>
                <div id="trans-trends-breakdown">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>

            <div id="trans-summary-tab" class="trans-tab-content hidden">
                <h3 class="text-lg font-semibold mb-4">Transaction Summary</h3>
                <div id="trans-summary-breakdown" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
</div>

<!-- Transactions List -->
<x-data-view :data="$lists" title="Transactions" url="" />   
@endsection


@section('scripts')
<style>
    .trans-tab-btn {
        background-color: transparent;
        color: #6B7280;
        border-bottom: 2px solid transparent;
    }

    .trans-tab-btn:hover {
        background-color: #F3F4F6;
    }

    .trans-tab-btn.trans-tab-active {
        background-color: #EFF6FF;
        color: #2563EB;
        border-bottom-color: #2563EB;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality for transactions
    document.querySelectorAll('.trans-tab-btn').forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all buttons
            document.querySelectorAll('.trans-tab-btn').forEach(btn => {
                btn.classList.remove('trans-tab-active');
            });
            
            // Add active class to clicked button
            this.classList.add('trans-tab-active');
            
            // Hide all tab contents
            document.querySelectorAll('.trans-tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show target tab content
            document.getElementById(targetTab).classList.remove('hidden');
        });
    });

    // Load analytics data from server
    @if(isset($analyticsData) && $analyticsData->success)
        updateTransactionsAnalytics({!! json_encode($analyticsData->data) !!});
    @endif
});

function updateTransactionsAnalytics(data) {
    // Update summary cards
    document.getElementById('trans-total').textContent = data.transactions.total.toLocaleString();
    document.getElementById('trans-total-amount').textContent = data.transactions.total_amount_formatted ?? data.transactions.total_amount;
    document.getElementById('trans-completed').textContent = data.transactions.completed.toLocaleString();
    document.getElementById('trans-success-rate').textContent = data.transactions.success_rate + '%';

    // Update status breakdown
    let statusHtml = '';
    if (Object.keys(data.transactions.by_status).length > 0) {
        Object.entries(data.transactions.by_status).forEach(([status, info]) => {
            const colors = {
                'completed': '#10B981',
                'pending': '#F59E0B',
                'failed': '#EF4444',
                'refunded': '#8B5CF6'
            };
            const color = colors[status.toLowerCase()] || '#6B7280';
            
            statusHtml += `
                <div class="bg-gray-50 rounded-lg p-4 border-l-4 hover:shadow-md transition-shadow" style="border-color: ${color}">
                    <p class="text-sm text-gray-600 mb-1">${info.name}</p>
                    <p class="text-2xl font-bold text-gray-900">${info.count}</p>
                    <p class="text-xs text-gray-500 mt-1">${info.amount}</p>
                </div>
            `;
        });
    } else {
        statusHtml = '<div class="col-span-full text-center text-gray-500 py-8">No status data available</div>';
    }
    document.getElementById('trans-status-breakdown').innerHTML = statusHtml;

    // Update type breakdown
    let typeHtml = '<div class="space-y-3">';
    if (Object.keys(data.transactions.by_type).length > 0) {
        Object.entries(data.transactions.by_type).forEach(([type, info]) => {
            typeHtml += `
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div>
                        <span class="text-gray-700 font-medium">${info.name}</span>
                        <p class="text-xs text-gray-500">${info.count} transactions</p>
                    </div>
                    <span class="font-bold text-gray-900 text-lg">${info.amount}</span>
                </div>
            `;
        });
    } else {
        typeHtml += '<div class="text-center text-gray-500 py-4">No type data available</div>';
    }
    typeHtml += '</div>';
    document.getElementById('trans-type-breakdown').innerHTML = typeHtml;

    // Update trends
    let trendsHtml = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">';
    
    if (data.daily_trend && data.daily_trend.length > 0) {
        data.daily_trend.forEach(day => {
            trendsHtml += `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${day.date}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${day.total_transactions}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">${day.completed_transactions}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">${day.amount}</td>
                </tr>
            `;
        });
    } else {
        trendsHtml += '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No trend data available</td></tr>';
    }
    
    trendsHtml += '</tbody></table></div>';
    document.getElementById('trans-trends-breakdown').innerHTML = trendsHtml;

    // Update summary
    let summaryHtml = `
        <div class="space-y-3">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Average Transaction</p>
                <p class="text-2xl font-bold text-gray-900">${data.transactions.average_amount_formatted ?? data.transactions.average_amount}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Highest Transaction</p>
                <p class="text-2xl font-bold text-green-600">${data.transactions.highest_amount_formatted ?? data.transactions.highest_amount}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Lowest Transaction</p>
                <p class="text-2xl font-bold text-gray-900">${data.transactions.lowest_amount_formatted ?? data.transactions.lowest_amount}</p>
            </div>
        </div>
        <div class="space-y-3">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Failed Transactions</p>
                <p class="text-2xl font-bold text-red-600">${data.transactions.failed}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Refunded</p>
                <p class="text-2xl font-bold text-purple-600">${data.transactions.refunded}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Pending</p>
                <p class="text-2xl font-bold text-yellow-600">${data.transactions.pending}</p>
            </div>
        </div>
    `;
    document.getElementById('trans-summary-breakdown').innerHTML = summaryHtml;
}
</script>
@endsection
