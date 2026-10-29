@extends('backend::layouts.app')

@section('title', 'Plans')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Subscriptions', 'url' => null],
    ['label' => 'Plans', 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Plans'" />
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
        <!-- Total Plans -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Total Plans</span>
                <span class="material-icons-outlined text-blue-500">dashboard</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
        </div>

        <!-- Active Plans -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Active</span>
                <span class="material-icons-outlined text-green-500">check_circle</span>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</div>
        </div>

        <!-- Inactive Plans -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Inactive</span>
                <span class="material-icons-outlined text-gray-400">cancel</span>
            </div>
            <div class="text-2xl font-bold text-gray-600">{{ $stats['inactive'] }}</div>
        </div>

        <!-- Popular Plans -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Popular</span>
                <span class="material-icons-outlined text-yellow-500">star</span>
            </div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['popular'] }}</div>
        </div>       
    </div>

    <x-data-view :data="$lists" title="" />
@endsection
