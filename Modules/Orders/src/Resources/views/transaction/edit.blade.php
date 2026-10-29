@extends('backend::layouts.app')

@section('title', 'Transaction | #'.$transaction->transaction_number)

@section('breadcrumbs')
@php
$title = 'Food Events';
$breadcrumbs = [
['label' => 'Dashboard', 'url' => route('dashboard.index')],
['label' => $title],
];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection
@section('styles')
@endsection

@section('content')

{{-- @include('admin::components.common.back-button', [
'route' => route('admin.transactions.index'),
'name' => 'Transaction #'.$transaction->transaction_number . ' / '. $transaction->status . ' / '.
$transaction->created_at->format('F j, Y')
]) --}}

@php
$metadata = is_string($transaction->metadata)
? json_decode($transaction->metadata, true)
: $transaction->metadata;

$receiptUrl = $metadata['receiptUrl'] ?? '#';

@endphp

@php

// Status determination
$order = $transaction->order;
$statusClass = 'bg-yellow-100 text-yellow-800';
$statusIcon = 'fas fa-clock';
$statusText = 'In Review';

// Calculate order summary values
$subtotal = $order->subtotal ?? $order->total;
$tax = $order->tax ?? 0;
$shippingCost = $order->shipping_cost ?? 0;
$discount = $order->discount ?? 0;
$total = $order->total;
@endphp

<!-- Transaction Header -->
<div
    class="bg-primary-100 text-amber-100 p-6 rounded-t-lg flex flex-col md:flex-row justify-between items-start md:items-center">
    <div class="mb-4 md:mb-0">
        <h1 class="text-2xl font-bold">Transaction Details</h1>
        <p class="text-white mt-1">Transaction #{{ $transaction->transaction_number }}</p>
    </div>
    <div class="bg-white bg-opacity-10 p-3 rounded-lg">
        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">{{
            ucfirst($transaction->status) }}</span>
        <p class="text-white text-sm mt-1">Processed: {{ $transaction->created_at->format('F j, Y') }}</p>
    </div>
</div>

<!-- Transaction Body -->
<div class="bg-white p-6 rounded-b-lg shadow-md">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Left Column - Transaction Details -->
        <div class="lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Transaction Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Transaction ID</p>
                    <p class="font-semibold">{{ $transaction->transaction_number }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Reference Number</p>
                    <p class="font-semibold">{{ $transaction->order?->order_number ?? '-' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Transaction Date</p>
                    <p class="font-semibold">{{ $transaction->created_at->format('F j, Y, H:i T') }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Payment Method</p>
                    <p class="font-semibold">{{ $order->payment->method->merchant_name ?? 'unknown' }}</p>
                </div>
            </div>

            <!-- Customer Information -->
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Customer Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Customer Name</p>
                    <p class="font-semibold">{{ $user?->name ?? $transaction->customer?->name ?? 'Guest Customer' }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $user?->email ?? $transaction->customer?->email ?? 'No
                        email provided' }}</p>
                    @if($user?->phone)
                    <p class="text-sm text-gray-600 mt-1">{{ $user->phone }}</p>
                    @endif
                </div>
            </div>

            {{-- Billing & Shipping Addresses --}}
            <h2 class="text-lg font-semibold text-gray-800 mt-6 mb-4">Addresses</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Billing --}}
                <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Billing Address</p>
                    @if(!empty($order->billing_address))
                    {!! \Modules\Customers\Helper\AddressHelper::format($order->billing_address, 'html') !!}
                    @else
                    <dd class="mt-1 text-sm text-gray-500">No billing information available</dd>
                    @endif
                </div>

                {{-- Shipping --}}
                <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Shipping Address</p>
                    @if(!empty($order->shipping_address) || !empty($order->billing_address))
                    {!! \Modules\Customers\Helper\AddressHelper::format($order->shipping_address ??
                    $order->billing_address, 'html') !!}
                    @else
                    <dd class="mt-1 text-sm text-gray-500">No shipping information available</dd>
                    @endif

                </div>
            </div>

            <h2 class="text-lg font-semibold text-gray-800 mb-4 mt-4">Order Details</h2>
            @include('orders::transaction.components.order_items')

            <!-- Order Summary Below Products -->
            <div class="mt-8 border-t border-gray-200 mb-4 pt-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h4>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Subtotal</dt>
                        <dd class="text-sm text-gray-900">{{ fn_get_currency_data($transaction->order->currency)->symbol
                            }}{{ number_format($subtotal, 2) }}</dd>
                    </div>

                    @if($tax > 0)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Tax</dt>
                        <dd class="text-sm text-gray-900">{{ fn_get_currency_data($transaction->order->currency)->symbol
                            }}{{ number_format($tax, 2) }}</dd>
                    </div>
                    @endif

                    @if($shippingCost > 0)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Shipping</dt>
                        <dd class="text-sm text-gray-900">{{ fn_get_currency_data($transaction->order->currency)->symbol
                            }}{{ number_format($shippingCost, 2) }}</dd>
                    </div>
                    @endif

                    @if($discount > 0)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Discount</dt>
                        <dd class="text-sm text-green-600">-{{
                            fn_get_currency_data($transaction->order->currency)->symbol }}{{ number_format($discount, 2)
                            }}</dd>
                    </div>
                    @endif

                    <div class="flex justify-between pt-2 border-t border-gray-200">
                        <dt class="text-base font-medium text-gray-900">Total</dt>
                        <dd class="text-base font-bold text-gray-900">{{
                            fn_get_currency_data($transaction->order->currency)->symbol }}{{ number_format($total, 2) }}
                        </dd>
                    </div>
                </dl>
            </div>


        </div>

        <!-- Right Column - Status & Actions -->
        <div class="lg:col-span-1">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Transaction Status</h2>
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-gray-600">Status:</span>
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">{{
                        ucfirst($transaction->status) }}</span>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-gray-600">Amount:</span>
                    <span class="font-semibold">{{ fn_get_currency_data($transaction->order->currency)->symbol }}{{
                        number_format($transaction->amount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Currency:</span>
                    <span class="font-semibold">{{ strtoupper($transaction->currency ?? 'USD') }}</span>
                </div>
            </div>

            @php
            $companyName = fn_get_setting('general.company.name');
            $companyEmail = fn_get_setting('general.company.mail');
            $companyStreet = fn_get_setting('general.company.address_street');
            $companyCity = fn_get_setting('general.company.address_city');
            $companyState = fn_get_setting('general.company.address_state');
            $companyPostal = fn_get_setting('general.company.address_postal');
            $companyCountry = fn_get_setting('general.company.country');
            @endphp

            <!-- Company Information -->
            <h2 class="text-lg font-semibold text-gray-800 mb-4 mt-4">Company Information</h2>
            <div class="flex flex-col gap-6">
                <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Company Name</p>
                    <p class="font-semibold">{{ $companyName ?? 'Looplynks' }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $companyEmail ?? 'info@looplynks.com' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Company Address</p>
                    @if($companyStreet || $companyCity || $companyState || $companyPostal || $companyCountry)
                    @if($companyStreet)<p class="font-semibold">{{ $companyStreet }}</p>@endif
                    <p class="text-sm text-gray-600">
                        {{ $companyCity }}{{ $companyCity && $companyState ? ',' : '' }}
                        {{ $companyState }} {{ $companyPostal }}
                    </p>
                    @if($companyCountry)<p class="text-sm text-gray-600">{{ $companyCountry }}</p>@endif
                    @else
                    <p class="text-sm text-gray-600">No Company Address set</p>
                    @endif
                </div>
            </div>

            <h2 class="text-lg font-semibold text-gray-800 mb-4">Actions</h2>
            <div class="flex flex-col gap-2">
                <a href="{{ $receiptUrl }}" target="_blank"
                    class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                    <i class="fas fa-file-download mr-2"></i>
                    Download Receipt
                </a>
                <button
                    class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                    <i class="fas fa-redo mr-2"></i>
                    Refund Transaction
                </button>
            </div>

            <h2 class="text-lg font-semibold text-gray-800 mb-4">Payment Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <pre>{!! json_encode($metadata, JSON_PRETTY_PRINT) !!}</pre>
            </div>
        </div>

    </div>
    <h2 class="text-lg font-semibold text-gray-800 mt-6 mb-4">Update Status</h2>
    <form action="{{ route('admin.transactions.update', $transaction->id) }}" method="POST" class="form-ajax mb-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="order_id" value="{{ $transaction->order_id }}">
        <input type="hidden" name="transaction_number" value="{{ $transaction->transaction_number }}">

        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Mark as completed</label>
            <select name="status" id="status"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="completed" {{ $transaction->status === 'completed' ? 'selected' : '' }}>Completed
                </option>
            </select>
        </div>
        <div class="mb-4">
            <div class="flex items-center">
                <input type="hidden" name="notify_to_customer" value="N">
                <input type="checkbox" name="notify_to_customer" id="notify_to_customer" value="Y"
                    class="w-full px-1 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <label for="notify_to_customer" class="ml-2 block text-sm text-gray-700">
                    Send notification to customer
                </label>
            </div>
            <p class="mt-1 text-xs text-gray-500">Customer will receive an email about this status update.</p>
        </div>
        <div class="mb-4">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
            <textarea name="notes" id="notes" rows="3"
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"
                placeholder="Add any additional notes here..."></textarea>
        </div>


        <x-button type="submit" class="primary" label="Update Status" icon='' name="button" />
    </form>

</div>
@endsection