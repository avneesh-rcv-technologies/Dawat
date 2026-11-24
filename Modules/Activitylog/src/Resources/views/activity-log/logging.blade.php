@extends('backend::layouts.app')

@section('title', 'System Logs - Activity Logs')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index') ],
    ['label' => 'System Logs', 'url' => null ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'System Logs'" />
@endsection

@section('content')

<div class="space-y-6">
    <form method="GET" action="{{ route('activity-log.advance-logging') }}" class="flex flex-wrap items-end gap-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div>
            <label for="date_from" class="block text-sm font-medium text-slate-700">From</label>
            <input type="date" id="date_from" name="date_from" value="{{ $dateFrom ?? '' }}" class="mt-1 rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="date_to" class="block text-sm font-medium text-slate-700">To</label>
            <input type="date" id="date_to" name="date_to" value="{{ $dateTo ?? '' }}" class="mt-1 rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>
        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Apply</button>
        <div class="flex flex-wrap gap-2 text-xs">
            @php $baseUrl = route('activity-log.advance-logging'); @endphp
            <a href="{{ $baseUrl }}?date_from={{ now()->subDays(7)->toDateString() }}&date_to={{ now()->toDateString() }}" class="rounded border border-slate-300 px-2 py-1 text-slate-600 hover:bg-slate-50">Last 7 days</a>
            <a href="{{ $baseUrl }}?date_from={{ now()->subDays(30)->toDateString() }}&date_to={{ now()->toDateString() }}" class="rounded border border-slate-300 px-2 py-1 text-slate-600 hover:bg-slate-50">Last 30 days</a>
            <a href="{{ $baseUrl }}?date_from={{ now()->subDays(90)->toDateString() }}&date_to={{ now()->toDateString() }}" class="rounded border border-slate-300 px-2 py-1 text-slate-600 hover:bg-slate-50">Last 90 days</a>
        </div>
        <p class="text-xs text-slate-500">Defaults to the last 7 days. Large exports are disabled on this grid.</p>
    </form>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4 xl:grid-cols-6">
        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 shadow-sm">
            <p class="text-sm font-medium text-blue-700">Total Requests</p>
            <p class="mt-2 text-2xl font-bold text-blue-900">{{ $stats['total_requests'] ?? 0 }}</p>
            <p class="text-xs text-blue-600">All tracked requests</p>
        </div>

        <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4 shadow-sm">
            <p class="text-sm font-medium text-emerald-700">Today</p>
            <p class="mt-2 text-2xl font-bold text-emerald-900">{{ $stats['today_requests'] ?? 0 }}</p>
            <p class="text-xs text-emerald-600">Requests created today</p>
        </div>

        <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 shadow-sm">
            <p class="text-sm font-medium text-amber-700">Blocked</p>
            <p class="mt-2 text-2xl font-bold text-amber-900">{{ $stats['blocked_requests'] ?? 0 }}</p>
            <p class="text-xs text-amber-600">Blocked by protection rules</p>
        </div>

        <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4 shadow-sm">
            <p class="text-sm font-medium text-indigo-700">Unique IPs</p>
            <p class="mt-2 text-2xl font-bold text-indigo-900">{{ $stats['unique_ips'] ?? 0 }}</p>
            <p class="text-xs text-indigo-600">Distinct IP addresses</p>
        </div>

        <div class="rounded-lg border border-rose-100 bg-rose-50 p-4 shadow-sm">
            <p class="text-sm font-medium text-rose-700">Error Responses</p>
            <p class="mt-2 text-2xl font-bold text-rose-900">{{ $stats['error_requests'] ?? 0 }}</p>
            <p class="text-xs text-rose-600">Status code 400 and above</p>
        </div>

        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm">
            <p class="text-sm font-medium text-slate-700">Avg Duration</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['avg_duration_ms'] ?? 0 }} ms</p>
            <p class="text-xs text-slate-600">Average response time</p>
        </div>
    </div>

    <x-data-view :data="$lists" title="" url="" />
</div>
   
@endsection

