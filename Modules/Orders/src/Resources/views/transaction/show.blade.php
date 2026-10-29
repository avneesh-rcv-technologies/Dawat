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
    @include('orders::transaction.components.details')
@endsection
