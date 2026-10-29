@extends('backend::layouts.app')

@section('title', 'Subscriptions')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Subscriptions', 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Subscriptions'" />
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-8 gap-4 mb-6">
        <!-- Total Subscriptions -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Total</span>
                <span class="material-icons-outlined text-blue-500">dashboard</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
        </div>

        <!-- Active Subscriptions -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Active</span>
                <span class="material-icons-outlined text-green-500">check_circle</span>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</div>
        </div>

        <!-- Trialing Subscriptions -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Trialing</span>
                <span class="material-icons-outlined text-blue-500">schedule</span>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['trialing'] }}</div>
        </div>

        <!-- Paused Subscriptions -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Paused</span>
                <span class="material-icons-outlined text-yellow-500">pause_circle</span>
            </div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['paused'] }}</div>
        </div>

        <!-- Canceled Subscriptions -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Canceled</span>
                <span class="material-icons-outlined text-orange-500">cancel</span>
            </div>
            <div class="text-2xl font-bold text-orange-600">{{ $stats['canceled'] }}</div>
        </div>

        <!-- Past Due Subscriptions -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Past Due</span>
                <span class="material-icons-outlined text-red-500">error</span>
            </div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['past_due'] }}</div>
        </div>

        <!-- Expired Subscriptions -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Expired</span>
                <span class="material-icons-outlined text-gray-500">event_busy</span>
            </div>
            <div class="text-2xl font-bold text-gray-600">{{ $stats['expired'] }}</div>
        </div>

        <!-- Ending Soon -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Ending Soon</span>
                <span class="material-icons-outlined text-purple-500">notification_important</span>
            </div>
            <div class="text-2xl font-bold text-purple-600">{{ $stats['cancel_at_period_end'] }}</div>
        </div>
    </div>

    <x-data-view :data="$lists" title="" />
@endsection
