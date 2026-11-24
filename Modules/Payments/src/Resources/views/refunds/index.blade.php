@extends('backend::layouts.app')

@section('title', 'Refund requests')

@section('breadcrumbs')
    <x-breadcrumbs :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Refund requests', 'url' => null],
    ]" name="Refund requests" />
@endsection

@section('content')
    <p class="mb-6 text-sm text-gray-500">{{ $canUpdate ? 'Review cancelled order payments and record refunds completed through your gateway or manually.' : 'View refund requests and payment updates for your restaurants. Refund processing is managed by platform admins.' }} Search by order number or use filters to narrow the results.</p>
    @php
        $cards = [
            ['label' => 'Total requests', 'field' => null, 'icon' => 'receipt_long', 'background' => '#eff6ff', 'color' => '#2563eb', 'help' => 'Matching refund requests'],
            ['label' => 'Requested amount', 'field' => 'requested_amount', 'icon' => 'account_balance_wallet', 'background' => '#f5f3ff', 'color' => '#7c3aed', 'help' => 'Across all matching requests'],
            ['label' => 'Refunded amount', 'field' => 'refunded_amount', 'icon' => 'task_alt', 'background' => '#ecfdf5', 'color' => '#059669', 'help' => 'Successfully completed refunds'],
            ['label' => 'Pending amount', 'field' => 'pending_amount', 'icon' => 'schedule', 'background' => '#fffbeb', 'color' => '#d97706', 'help' => number_format($summary['pending_records']) . ' requests awaiting processing'],
            ['label' => 'Processing amount', 'field' => 'processing_amount', 'icon' => 'sync', 'background' => '#fff7ed', 'color' => '#ea580c', 'help' => number_format($summary['processing_records']) . ' requests in progress'],
        ];
    @endphp
    @include('backend::partials.kpi-cards', ['overviewLabel' => 'Refund overview'])
    <x-data-view :data="$lists" title="Refund requests" />
@endsection
