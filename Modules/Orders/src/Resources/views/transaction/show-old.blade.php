@extends('backend::layouts.app')

@section('title', 'Transaction | #' . $transaction->transaction_number)

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
        $labourCost = $order->labour_charge ?? 0;
        $discount = $order->discount ?? 0;
        $total = $order->total;
    @endphp

    <!-- Transaction Header -->
    <div class="bg-primary-100 text-gray-800 p-6 rounded-t-lg flex flex-col md:flex-row justify-between items-start md:items-center">
        <div class="mb-4 md:mb-0">
            <h1 class="text-2xl font-bold">Transaction Details</h1>
            <p cl
    a           ss="text-white mt-1">Transaction #{{ $transaction->transaction_number }}</p>
        </div>
        <div class="bg-white bg-opacity-10 p-3 rounded-lg">
            <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">{{ ucfirst($transaction->status) }}</span>
            <p class="text-black text-sm mt-1">Processed: {{ $transaction->created_at->format('F j, Y') }}</p>
        </div>
    </div>

    <!-- Transaction Body -->
    <div class="bg-white p-6 rounded-b-lg shadow-md">

        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Left Column - Transaction Details -->
            <div class="lg:col-span-2">


  <h2 class="text-lg font-semibold text-gray-800 mt-6 mb-4">Job Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    {{-- Billing --}}
                   
                    <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                        <p class="text-sm text-gray-500">Job Name</p>
                      
                            <p class="font-semibold">{{ fn_get_job_name($order->job_id) }}</p>
                       
                    </div>

                     <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                        <p class="text-sm text-gray-500">Technician Name</p>
                       
                             <p class="font-semibold">{{ fn_get_technician_name($order->job_id) }}</p>
                       
                    </div>
                   
                </div>



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
                        <p class="font-semibold">{{ fn_get_payment_method_name($order->payment_method)  ?? 'unknown'  }}</p>
                    </div>
                </div>

                <!-- Customer Information -->
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Customer Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                        <p class="text-sm text-gray-500">Customer Name</p>
                        <p class="font-semibold">{{ $user?->name ?? $transaction->customer?->name ?? 'Guest Customer' }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $user?->email ?? $transaction->customer?->email ?? 'No email provided' }}</p>
                        @if($user?->phone)
                            <p class="text-sm text-gray-600 mt-1">{{ $user->phone }}</p>
                        @endif
                    </div>

                    <div class="bg-gray-50 p-4 rounded border-l-4 border-indigo-500">
                        <p class="text-sm text-gray-500">Billing Address</p>
                        @if(!empty($order->billing_address))
                            {!! \Modules\Customers\Helper\AddressHelper::format($order->billing_address, 'html') !!}
                        @else
                            <dd class="mt-1 text-sm text-gray-500 ">No billing information available</dd>
                        @endif
                    </div>
                </div>

                {{-- Billing & Shipping Addresses --}}
              
                <h2 class="text-lg font-semibold text-gray-800 mb-4 mt-4">Order Details</h2>
                @include('orders::transaction.components.order_items')

                <!-- Order Summary Below Products -->
                <div class="mt-8 border-t border-gray-200 mb-4 pt-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h4>
                    <dl class="space-y-2">


                                                    <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Subtotal</dt>
                            <dd class="text-sm text-gray-900">{{ fn_get_currency_data($transaction->order->currency)->symbol }}{{ number_format($subtotal, 2) }}</dd>
                        </div>

                        @if($tax > 0)


                                                        <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Tax</dt>
                                <dd class="text-sm text-gray-900">{{ fn_get_currency_data($transaction->order->currency)->symbol }}{{ number_format($tax, 2) }}</dd>
                            </div>
                        @endif

                        @if($labourCost > 0)


                                                        <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Labour Charge</dt>
                                <dd class="text-sm text-gray-900">{{ fn_get_currency_data($transaction->order->currency)->symbol }}{{ number_format($labourCost, 2) }}</dd>
                            </div>
                        @endif

                        @if($discount > 0)


                                                    <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Discount</dt>
                                <dd class="text-sm text-green-600">-{{ fn_get_currency_data($transaction->order->currency)->symbol }}{{ number_format($discount, 2) }}</dd>
                            </div>
                        @endif

                                                <div class="flex justify-between pt-2 border-t border-gray-200">
                            <dt class="text-base font-medium text-gray-900">Total</dt>
                            <dd class="text-base font-bold text-gray-900">{{ fn_get_currency_data($transaction->order->currency)->symbol }}{{ number_format($total, 2) }}</dd>
                        </div>
                    </dl>
                </div>


            </div>

            <!-- Right Column - Status & Actions -->
            <div class="lg:col-span-1">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Transaction Status</h2>
                <div class="b
                           g-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-gray-600">Status:</span>
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">{{ ucfirst($transaction->status) }}</span>
                    </div>

                                           <div class="flex items-center justify-between mb-3">
                        <span class="text-gray-600">Amount:</span>
                        <span class="font-semibold">{{ fn_get_currency_data($transaction->order->currency)->symbol }}{{ number_format($transaction->amount, 2) }}</span>
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
                    <div class="bg-gray-50 
                        p-4 rounded border-l-4 border-indigo-500">
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
                    <a href="{{ $receiptUrl }}" target="_blank" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                        <i 
                       class="fas fa-file-download mr-2"></i>
                        Download Receipt
                    </a>
                    <button class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
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
    </div>
@endsection