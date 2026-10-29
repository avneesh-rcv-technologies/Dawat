@extends('backend::layouts.app')

@section('title', 'Customers')

@section('breadcrumbs')
    @php
        $title = 'Customers';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection


@section('content')
<div class="customer-page">
<section class="customer-analytics-section" aria-labelledby="customer-analytics-title">
    <div class="customer-analytics-heading">
        <div>
            <h2 id="customer-analytics-title" class="text-xl font-semibold text-gray-900">Analytics overview</h2>
            <p class="customer-analytics-caption">A summary of customer activity and revenue</p>
        </div>
        <form method="GET" class="customer-date-filter">
            <label class="sr-only" for="date_from">From date</label>
            <input type="date" name="date_from" id="date_from" aria-label="From date" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white" value="{{ request('date_from', date('Y-m-d', strtotime('-30 days'))) }}">
            <span class="customer-date-separator" aria-hidden="true">to</span>
            <label class="sr-only" for="date_to">To date</label>
            <input type="date" name="date_to" id="date_to" aria-label="To date" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white" value="{{ request('date_to', date('Y-m-d')) }}">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors flex items-center gap-1 shadow-sm">
                <i class="fas fa-filter"></i> <span>Filter</span>
            </button>
        </form>
    </div>
    
    <div id="analytics-summary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="customer-metric-card customer-metric-card--customers bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Customers</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-customers">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="customer-metric-icon w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl" aria-hidden="true"></i>
                </div>
            </div>
        </div>

        <div class="customer-metric-card customer-metric-card--active bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Active Customers</p>
                    <p class="text-2xl font-bold text-gray-900" id="active-customers">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="customer-metric-icon w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600 text-xl" aria-hidden="true"></i>
                </div>
            </div>
        </div>

        <div class="customer-metric-card customer-metric-card--revenue bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-revenue">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="customer-metric-icon w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-purple-600 text-xl" aria-hidden="true"></i>
                </div>
            </div>
        </div>

        <div class="customer-metric-card customer-metric-card--rate bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Active Rate</p>
                    <p class="text-2xl font-bold text-gray-900" id="active-rate">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </p>
                </div>
                <div class="customer-metric-icon w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-percent text-amber-600 text-xl" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics Tabs -->
    {{-- <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <div class="flex space-x-1 p-2">
                <button class="tab-btn tab-active px-4 py-2 text-sm font-medium rounded-lg transition-colors" data-tab="trend-tab">
                    <i class="fas fa-chart-line mr-1"></i> Registration Trend
                </button>
                <button class="tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors" data-tab="top-orders-tab">
                    <i class="fas fa-trophy mr-1"></i> Top by Orders
                </button>
                <button class="tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors" data-tab="top-spending-tab">
                    <i class="fas fa-dollar-sign mr-1"></i> Top by Spending
                </button>
            </div>
        </div>

        <div class="p-6">
            <div id="trend-tab" class="tab-content">
                <h3 class="text-lg font-semibold mb-4">Registration Trend (Last 7 Days)</h3>
                <div id="trend-breakdown">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>

            <div id="top-orders-tab" class="tab-content hidden">
                <h3 class="text-lg font-semibold mb-4">Top Customers by Orders</h3>
                <div id="top-orders-breakdown">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>

            <div id="top-spending-tab" class="tab-content hidden">
                <h3 class="text-lg font-semibold mb-4">Top Customers by Spending</h3>
                <div id="top-spending-breakdown">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
</section>

<section class="customer-list-section" aria-labelledby="customer-list-title">
    <div class="customer-list-heading">
        <div>
            <h2 id="customer-list-title">Customer directory</h2>
            <p>Search and manage customer accounts.</p>
        </div>
    </div>
<x-data-view :data="$lists" title="Customers" url="" />
</section>
</div>
@endsection

@push('styles')
<style>
    .customer-page { --customer-ink: #102f40; --customer-muted: #647789; --customer-line: #e2e9ee; color: var(--customer-ink); }
    .customer-page .customer-analytics-section { margin-bottom: 32px; }
    .customer-page .customer-analytics-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 18px; }
    .customer-page .customer-analytics-heading h2,
    .customer-page .customer-list-heading h2 { margin: 0; color: var(--customer-ink); font-size: 20px; line-height: 1.35; font-weight: 700; letter-spacing: -.015em; }
    .customer-page .customer-analytics-caption { margin: 5px 0 0; color: var(--customer-muted); font-size: 13px; }
    .customer-page .customer-date-filter { display: flex; align-items: center; gap: 9px; }
    .customer-page .customer-date-filter input { min-height: 40px; border-color: #d5e0e6; border-radius: 9px; color: var(--customer-ink); }
    .customer-page .customer-date-filter input:focus { border-color: #2563eb; outline: 3px solid rgba(37, 99, 235, .12); }
    .customer-page .customer-date-separator { color: var(--customer-muted); font-size: 13px; }
    .customer-page .customer-date-filter button { min-height: 40px; padding-inline: 16px; border-radius: 9px; font-weight: 600; }
    .customer-page #analytics-summary { gap: 14px; margin-bottom: 0; }
    .customer-page .customer-metric-card { position: relative; overflow: hidden; min-height: 96px; padding: 18px 20px; border: 1px solid var(--customer-line); border-radius: 12px; box-shadow: 0 2px 7px rgba(16, 47, 64, .045); transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease; }
    .customer-page .customer-metric-card::before { position: absolute; inset: 0 auto 0 0; width: 3px; content: ''; background: var(--metric-accent); opacity: .8; }
    .customer-page .customer-metric-card--customers { --metric-accent: #2563eb; }
    .customer-page .customer-metric-card--active { --metric-accent: #00a86b; }
    .customer-page .customer-metric-card--revenue { --metric-accent: #8b5cf6; }
    .customer-page .customer-metric-card--rate { --metric-accent: #e69a00; }
    .customer-page .customer-metric-card:hover { transform: translateY(-2px); border-color: #cfdae2; box-shadow: 0 8px 18px rgba(16, 47, 64, .08); }
    .customer-page .customer-metric-card p:first-child { color: var(--customer-muted); font-weight: 500; }
    .customer-page .customer-metric-card p.text-2xl { color: var(--customer-ink); letter-spacing: -.02em; }
    .customer-page .customer-metric-icon { position: relative; z-index: 1; flex: 0 0 44px; width: 44px; height: 44px; border: 1px solid rgba(16, 47, 64, .04); border-radius: 12px; }
    .customer-page .customer-metric-icon i { font-size: 18px; }
    .customer-page .customer-list-heading { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; padding-bottom: 12px; border-bottom: 1px solid #edf1f4; }
    .customer-page .customer-list-heading p { margin: 4px 0 0; color: var(--customer-muted); font-size: 13px; }
    .customer-page .customer-list-section > .pb-1 { padding-top: 0; }
    @media (max-width: 700px) {
        .customer-page .customer-analytics-heading { align-items: stretch; flex-direction: column; }
        .customer-page .customer-date-filter { flex-wrap: wrap; }
        .customer-page .customer-date-filter input { flex: 1 1 145px; min-width: 0; }
        .customer-page .customer-date-filter button { flex: 1 1 100%; justify-content: center; }
        .customer-page .customer-analytics-section { margin-bottom: 24px; }
    }
</style>
@endpush

@section('scripts')
{{-- <style>
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
</style> --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load analytics data from server
    @if(isset($analyticsData) && $analyticsData->success)
        updateCustomerAnalytics({!! json_encode($analyticsData->data) !!});
    @endif
});

function updateCustomerAnalytics(data) {
    // Update summary cards
    document.getElementById('total-customers').textContent = data.customers.total.toLocaleString();
    document.getElementById('active-customers').textContent = data.customers.active.toLocaleString();
    document.getElementById('total-revenue').textContent = data.customers.total_revenue_formatted;
    document.getElementById('active-rate').textContent = data.customers.active_rate + '%';

}
</script>
@endsection
