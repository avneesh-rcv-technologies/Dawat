@extends('backend::layouts.app')

@section('title', 'Report & Analytics')

@section('breadcrumbs')
@php
$breadcrumbs = [
['label' => 'Dashboard', 'url' => route('dashboard.index')],
['label' => 'System Settings', 'url' => route('settings.index') ],
['label' => 'Report & Analytics', 'url' => null ],
];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Report & Analytics'" />
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    {{-- <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">

        @if(bouncer($guard)->hasPermission('reports.create'))
        <div class="flex gap-2">
            <a href="{{ route('reports.builder.index') }}"
                class="inline-flex items-center px-4 py-2.5 bg-black text-white rounded-lg hover:bg-purple-700 shadow-sm transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                Report Builder
            </a>
        </div>
        @endif
    </div> --}}

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="reportStats">
        <div class="bg-white px-2 py-4 rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Reports</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="totalReports">-</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white px-2 py-4 rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Reports</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="activeReports">-</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white px-2 py-4 rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Scheduled</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="scheduledReports">-</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white px-2 py-4 rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Chart Types</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="chartTypes">-</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Table -->

    <x-data-view :data="$lists" title="" url="" />

</div>

@section('scripts')
<script>
    async function fetchReportStatistics() {
        try {
            const response = await fetch('{{ route("reports.statistics") }}');
            const result = await response.json();
            
            if (result.success) {
                const stats = result.data;
                
                document.getElementById('totalReports').textContent = stats.total || 0;
                document.getElementById('activeReports').textContent = stats.active || 0;
                document.getElementById('scheduledReports').textContent = stats.scheduled || 0;
                document.getElementById('chartTypes').textContent = Object.keys(stats.by_chart_type || {}).length || 0;
            }
        } catch (error) {
            console.error('Error fetching statistics:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchReportStatistics();
    });
</script>
@endsection
@endsection