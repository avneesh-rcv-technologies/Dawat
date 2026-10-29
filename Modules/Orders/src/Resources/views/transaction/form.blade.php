@extends('backend::layouts.app')

@section('title', 'Create Transaction')

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
@section('content')

{{-- @include('admin::components.common.back-button', ['route' => route('admin.orders.index'), 'name' => 'Create Transaction for Order #'.$order->order_number]) --}}

<form id="createTransactionForm" class="form-ajax" method="POST" action="{{ route('admin.transactions.store') }}">
    @csrf
    <input type="hidden" name="order_id" value="{{ $order->id }}">

    <div class="mb-6">
        <!-- Order Summary -->
        <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-700">Order Total:</span>
                    <span class="ml-2">{{ fn_convert_currency($order->total, $order->currency) }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Customer:</span>
                    <span class="ml-2">{{ $order->user->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>


        <!-- Payment Method -->
        <div class="mb-6">
            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">
                Payment Method
            </label>
            <select id="payment_method" name="payment_method"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Select Payment Method</option>
                @foreach(fn_get_payments() as $payment)
                    <option value="{{$payment->payment_method_id}}" data-payment-code="{{$payment->payment_method_id}}">{{$payment->merchant_name}}</option>
                @endforeach
            </select>
        </div>

        <!-- Payment Method -->
        <div class="mb-6">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                Status
            </label>
            <select id="status" name="status"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Select Status</option>
                <option value="paid" data-payment-code="paid">Paid</option>
                <option value="unpaid" data-payment-code="unpaid">Unpaid</option>
                <option value="refunded" data-payment-code="refunded">Refunded</option>
            </select>
        </div>

        <!-- Transaction Amount -->
        <div class="mb-4">
            <label for="transaction_amount" class="block text-sm font-medium text-gray-700 mb-2">
                Transaction Amount *
            </label>
            <input type="number" name="transaction_amount" id="transaction_amount" step="0.01" min="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                value="{{ $order->total }}">
            <p class="text-xs text-gray-500 mt-1">Enter the amount to be processed in this transaction</p>
        </div>

        <!-- payment reference id -->
        <div class="mb-4">
            <label for="payment_reference_id" class="block text-sm font-medium text-gray-700 mb-2">
                Payment Reference ID *
            </label>
            <input type="text" name="payment_reference_id" id="payment_reference_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                value="{{ $order->payment_reference_id }}">
            <p class="text-xs text-gray-500 mt-1">Enter the payment reference id</p>
        </div>

        <!-- notify to customer -->
        <div class="mb-4">
            <label for="notify_to_customer" class="block text-sm font-medium text-gray-700 mb-1">
                Notify to Customer
            </label>
            <input type="hidden" name="notify_to_customer" value="N">
            <input type="checkbox" name="notify_to_customer" id="notify_to_customer" value="Y"
                class="w-full px-1 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
        </div>

        <!-- Transaction Notes -->
        <div class="mb-4">
            <label for="transaction_notes" class="block text-sm font-medium text-gray-700 mb-2">
                Notes (Optional)
            </label>
            <textarea name="transaction_notes" id="transaction_notes" rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Add any notes for this transaction"></textarea>
        </div>
    </div>

    <!-- Form Actions -->

    <!-- Form Actions -->
    <div class="flex justify-end space-x-3">

        <x-button type="submit"
            class="primary"
            label="Create Transaction"
            icon=''
            name="button" />

    </div>
</form>
@endsection