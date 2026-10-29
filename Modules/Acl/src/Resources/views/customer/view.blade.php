{{-- Version: 2.0 - Updated with proper tabs like restaurant view --}}
@extends('backend::layouts.app')

@section('title', 'View Customer')

@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Customers', 'url' => route('admin.customer.index')],
            ['label' => 'View Customer'],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'View Customer'" />
@endsection

@section('content')
    <div class="container mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-t-lg border-b border-gray-200 py-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.customer.index') }}"
                        class="p-2 rounded-md hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $customer->name }}</h2>
                        <p class="text-sm text-gray-500">Customer Details</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @if(bouncer('admin')->hasPermission('admin.customer.edit'))
                        <x-modal
                            buttonText='<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> Edit Customer'
                            modalTitle="Edit Customer"
                            id="customer_view_edit_modal"
                            ajaxUrl="{{ route('admin.customer.edit', $customer->id) }}"
                            buttonClass="inline-flex items-center px-4 py-2 bg-[var(--color-black)] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700"
                            modalSize="2xl" />
                    @endif
                </div>
            </div>
        </div>

        <!-- Analytics Summary Cards (Always Visible) -->
        @if(isset($analytics))
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Customer Analytics</h3>
                <form method="GET" class="flex items-center gap-2">
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors flex items-center gap-1 shadow-sm">
                        <i class="fas fa-filter"></i> <span>Filter</span>
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Orders</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $analytics['orders']['total'] }}</p>
                            <p class="text-xs text-green-600 mt-1">{{ $analytics['orders']['completed'] }} completed</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Spent</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $analytics['spending']['total_formatted'] ?? fn_convert_currency($analytics['spending']['total'] ?? 0, $analytics['spending']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                            <p class="text-xs text-gray-600 mt-1">Avg: {{ $analytics['spending']['average_order_value_formatted'] ?? fn_convert_currency($analytics['spending']['average_order_value'] ?? 0, $analytics['spending']['currency'] ?? fn_get_setting('general.currency', 'USD')) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pending Orders</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $analytics['orders']['pending'] }}</p>
                            <p class="text-xs text-yellow-600 mt-1">Active orders</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Cancelled</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $analytics['orders']['cancelled'] }}</p>
                            <p class="text-xs text-red-600 mt-1">Cancelled orders</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="flex border-b border-gray-200">
                <button class="tab-btn tab-active" data-tab="details">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Details
                </button>
                @if(isset($analytics))
                <button class="tab-btn" data-tab="orders">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Orders
                </button>
                <button class="tab-btn" data-tab="restaurants">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    Restaurants
                </button>
                <button class="tab-btn" data-tab="transactions">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                        </path>
                    </svg>
                    Transactions
                </button>
                @endif
            </div>
        </div>

        <!-- Tab Contents -->
        <div class="tab-content" id="tab-details">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                @include('acl::customer.partials.details', ['customer' => $customer])
            </div>
        </div>

        @if(isset($analytics))
        <div class="tab-content hidden" id="tab-orders">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                @include('acl::customer.partials.orders', ['analytics' => $analytics, 'customer' => $customer])
            </div>
        </div>

        <div class="tab-content hidden" id="tab-restaurants">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                @include('acl::customer.partials.restaurants', ['analytics' => $analytics])
            </div>
        </div>

        <div class="tab-content hidden" id="tab-transactions">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                @include('acl::customer.partials.transactions', ['analytics' => $analytics])
            </div>
        </div>
        @endif
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    /* ================= TAB INIT ================= */
    document.querySelectorAll('.tab-content')
        .forEach(c => c.classList.add('hidden'));

    document.getElementById('tab-details')
        ?.classList.remove('hidden');

    document.querySelectorAll('.tab-btn')
        .forEach(b => b.classList.remove('tab-active'));

    document.querySelector('.tab-btn[data-tab="details"]')
        ?.classList.add('tab-active');

    /* ================= TAB SWITCH ================= */
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;

            document.querySelectorAll('.tab-content')
                .forEach(c => c.classList.add('hidden'));

            document.getElementById('tab-' + tab)
                ?.classList.remove('hidden');

            document.querySelectorAll('.tab-btn')
                .forEach(b => b.classList.remove('tab-active'));

            btn.classList.add('tab-active');
        });
    });
});
</script>

<style>
    /* Tab Styles */
    .tab-btn {
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        font-weight: 500;
        color: #6b7280;
        border-bottom: 2px solid transparent;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        background: none;
        border: none;
        border-radius: 0;
    }

    .tab-btn:hover {
        color: #374151;
        background-color: #f9fafb;
    }

    .tab-btn.tab-active {
        color: #9333EA;
        border-bottom-color: #9333EA;
        background-color: #faf5ff;
    }

    .tab-content {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection
