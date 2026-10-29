@extends('backend::layouts.app')

@section('title', 'Orders')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Orders', 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Orders'" />
@endsection

@section('content')
<!-- Analytics Summary Section -->
<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-900">Orders Analytics</h2>
        <form method="GET" class="flex items-center gap-2">
            <input type="date" name="date_from" id="date_from" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white" value="{{ request('date_from', date('Y-m-d', strtotime('-30 days'))) }}">
            <input type="date" name="date_to" id="date_to" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white" value="{{ request('date_to', date('Y-m-d')) }}">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors flex items-center gap-1 shadow-sm">
                <i class="fas fa-filter"></i> <span>Filter</span>
            </button>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div id="analytics-summary" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-orders">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Revenue</p>
                    <p class="text-xl font-bold text-gray-900" id="total-revenue">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Paid Payments</p>
                    <p class="text-2xl font-bold text-gray-900" id="paid-payments">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-circle-check text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Failed Payments</p>
                    <p class="text-2xl font-bold text-gray-900" id="failed-payments">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-circle-xmark text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Pending Payments</p>
                    <p class="text-2xl font-bold text-gray-900" id="pending-payments">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-sky-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics Tabs -->
    {{-- <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <div class="flex space-x-1 p-2">
                <button class="tab-btn tab-active px-4 py-2 text-sm font-medium rounded-lg transition-colors" data-tab="status-tab">
                    <i class="fas fa-chart-pie mr-1"></i> By Status
                </button>
                <button class="tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors" data-tab="revenue-tab">
                    <i class="fas fa-money-bill-wave mr-1"></i> Revenue
                </button>
                <button class="tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors" data-tab="trends-tab">
                    <i class="fas fa-chart-line mr-1"></i> Trends
                </button>
                <button class="tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors" data-tab="customers-tab">
                    <i class="fas fa-users mr-1"></i> Customers
                </button>
            </div>
        </div>

        <div class="p-6">
            <div id="status-tab" class="tab-content">
                <h3 class="text-lg font-semibold mb-4">Orders by Status</h3>
                <div id="status-breakdown" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>

            <div id="revenue-tab" class="tab-content hidden">
                <h3 class="text-lg font-semibold mb-4">Revenue Analytics</h3>
                <div id="revenue-breakdown" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>

            <div id="trends-tab" class="tab-content hidden">
                <h3 class="text-lg font-semibold mb-4">Daily Performance (Last 7 Days)</h3>
                <div id="trends-breakdown">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>

            <div id="customers-tab" class="tab-content hidden">
                <h3 class="text-lg font-semibold mb-4">Customer Insights</h3>
                <div id="customers-breakdown" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
</div>

<!-- Orders List -->
<x-data-view :data="$lists" title="Orders" url="" />   
@endsection

@section('scripts')
<style>
    .tab-btn {
        background-color: transparent;
        color: #6B7280;
        border-bottom: 2px solid transparent;
    }

    .tab-btn:hover {
        background-color: #F3F4F6;
    }

    .tab-btn.tab-active {
        background-color: #EFF6FF;
        color: #2563EB;
        border-bottom-color: #2563EB;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('tab-active');
            });
            
            // Add active class to clicked button
            this.classList.add('tab-active');
            
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show target tab content
            document.getElementById(targetTab).classList.remove('hidden');
        });
    });

    // Load analytics data from server
    @if(isset($analyticsData) && $analyticsData->success)
        updateOrdersAnalytics({!! json_encode($analyticsData->data) !!});
    @endif
});

function updateOrdersAnalytics(data) {
    // Update summary cards
    document.getElementById('total-orders').textContent = data.orders.total.toLocaleString();
    document.getElementById('total-revenue').textContent = data.revenue.total_formatted;
    document.getElementById('paid-payments').textContent = data.payments.paid_formatted;
    document.getElementById('failed-payments').textContent = data.payments.failed_formatted;
    document.getElementById('pending-payments').textContent = data.payments.pending_formatted;

    // Update status breakdown
    let statusHtml = '';
    if (Object.keys(data.orders.by_status).length > 0) {
        Object.values(data.orders.by_status).forEach(status => {
            statusHtml += `
                <div class="bg-gray-50 rounded-lg p-4 border-l-4 hover:shadow-md transition-shadow" style="border-color: ${status.color}">
                    <p class="text-sm text-gray-600 mb-1">${status.name}</p>
                    <p class="text-2xl font-bold text-gray-900">${status.count}</p>
                    <p class="text-xs text-gray-500 mt-1">Code: ${status.code}</p>
                </div>
            `;
        });
    } else {
        statusHtml = '<div class="col-span-full text-center text-gray-500 py-8">No status data available</div>';
    }
    document.getElementById('status-breakdown').innerHTML = statusHtml;

    // Update revenue breakdown
    let revenueHtml = `
        <div class="space-y-3">
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <span class="text-gray-700 font-medium">Total Revenue</span>
                <span class="font-bold text-green-600 text-lg">${data.revenue.total_formatted}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <span class="text-gray-700 font-medium">Average Order</span>
                <span class="font-bold text-gray-900 text-lg">${data.revenue.average_order_value_formatted}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <span class="text-gray-700 font-medium">Highest Order</span>
                <span class="font-bold text-green-600 text-lg">${data.revenue.highest_order_formatted}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <span class="text-gray-700 font-medium">Lowest Order</span>
                <span class="font-bold text-gray-900 text-lg">${data.revenue.lowest_order_formatted}</span>
            </div>
        </div>
        <div class="space-y-3">
            <h4 class="font-semibold text-gray-900 mb-3 text-lg">Payment Methods</h4>
    `;
    
    if (Object.keys(data.payment_method_breakdown).length > 0) {
        Object.entries(data.payment_method_breakdown).forEach(([method, info]) => {
            revenueHtml += `
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <span class="text-gray-700 font-medium">${info.name}</span>
                    <div class="text-right">
                        <div class="font-bold text-gray-900">${info.count} orders</div>
                        <div class="text-sm text-gray-600">${info.revenue_formatted ?? info.revenue}</div>
                    </div>
                </div>
            `;
        });
    } else {
        revenueHtml += '<div class="text-center text-gray-500 py-4">No payment method data available</div>';
    }
    
    revenueHtml += '</div>';
    document.getElementById('revenue-breakdown').innerHTML = revenueHtml;

    // Update trends
    let trendsHtml = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">';
    
    if (data.daily_trend && data.daily_trend.length > 0) {
        data.daily_trend.forEach(day => {
            trendsHtml += `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${day.date}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${day.total_orders}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">${day.completed_orders}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">$${parseFloat(day.revenue).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                </tr>
            `;
        });
    } else {
        trendsHtml += '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No trend data available</td></tr>';
    }
    
    trendsHtml += '</tbody></table></div>';
    document.getElementById('trends-breakdown').innerHTML = trendsHtml;

    // Update customers
    let customersHtml = `
        <div class="bg-gray-50 rounded-lg p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <i class="fas fa-users text-blue-500 text-2xl"></i>
            </div>
            <p class="text-sm text-gray-600 mb-1">Unique Customers</p>
            <p class="text-3xl font-bold text-gray-900">${data.customers.unique}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <i class="fas fa-redo text-green-500 text-2xl"></i>
            </div>
            <p class="text-sm text-gray-600 mb-1">Repeat Customers</p>
            <p class="text-3xl font-bold text-gray-900">${data.customers.repeat}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <i class="fas fa-percentage text-purple-500 text-2xl"></i>
            </div>
            <p class="text-sm text-gray-600 mb-1">Repeat Rate</p>
            <p class="text-3xl font-bold text-gray-900">${data.customers.repeat_rate}%</p>
        </div>
    `;
    document.getElementById('customers-breakdown').innerHTML = customersHtml;
}
</script>
@endsection
