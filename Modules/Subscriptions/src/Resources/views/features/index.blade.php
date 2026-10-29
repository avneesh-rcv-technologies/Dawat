@extends('backend::layouts.app')

@section('title', 'Features')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Subscriptions', 'url' => route('subscriptions.index')],
    ['label' => 'Features', 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Features'" />
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <!-- Total Features -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Total</span>
                <span class="material-icons-outlined text-blue-500">dashboard</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
        </div>

        <!-- Active Features -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Active</span>
                <span class="material-icons-outlined text-green-500">check_circle</span>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</div>
        </div>

        <!-- Inactive Features -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Inactive</span>
                <span class="material-icons-outlined text-gray-500">cancel</span>
            </div>
            <div class="text-2xl font-bold text-gray-600">{{ $stats['inactive'] }}</div>
        </div>

        <!-- Boolean Features -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Boolean</span>
                <span class="material-icons-outlined text-blue-500">toggle_on</span>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['boolean'] }}</div>
        </div>

        <!-- Limit Features -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Limit</span>
                <span class="material-icons-outlined text-green-500">speed</span>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['limit'] }}</div>
        </div>

        <!-- Package Features -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Package</span>
                <span class="material-icons-outlined text-purple-500">inventory_2</span>
            </div>
            <div class="text-2xl font-bold text-purple-600">{{ $stats['package'] }}</div>
        </div>
    </div>

    <!-- Groups Overview -->
    @if(!empty($groupedCount))
    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Features by Group</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($groupedCount as $group => $count)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                    <span class="material-icons-outlined text-sm mr-1">folder</span>
                    {{ $group ?: 'Uncategorized' }}: <span class="ml-1 font-bold">{{ $count }}</span>
                </span>
            @endforeach
        </div>
    </div>
    @endif

    <!-- DataView Component -->
    <x-data-view :data="$lists" title="" />
@endsection
