@extends('backend::layouts.app')

@section('title', 'Transaction | #' . $transaction->transaction_number)

@section('breadcrumbs')
    @php
        $title = 'Transaction Details';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Transactions', 'url' => route('admin.transactions.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
    @if(!empty($transaction->metadata['refund_id']))
        <div class="mb-5 rounded-lg border border-orange-200 bg-orange-50 p-4">
            <a href="{{ route('admin.refunds.show', $transaction->metadata['refund_id']) }}" class="font-semibold text-orange-800">View and process refund {{ $transaction->metadata['refund_number'] ?? $transaction->transaction_number }} →</a>
            <p class="mt-1 text-sm text-gray-600">Update the refund request to keep payment totals and status history consistent.</p>
        </div>
    @endif
    @include('orders::transaction.components.details')
@endsection
