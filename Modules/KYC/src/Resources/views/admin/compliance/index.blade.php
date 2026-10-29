@extends('backend::layouts.app')

@section('title', 'Compliance Records')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Compliance Records'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Compliance Records'" />
@endsection

@section('content')
@include('kyc::admin.partials.kyc-styles')

@include('kyc::admin.partials.stat-grid', [
    'stats' => [
        ['label' => 'Total records', 'value' => $statistics['total'], 'icon' => 'verified_user', 'tone' => 'indigo'],
        ['label' => 'Active', 'value' => $statistics['active'], 'icon' => 'check_circle', 'tone' => 'emerald'],
        ['label' => 'Expiring soon', 'value' => $statistics['expiring_soon'], 'icon' => 'schedule', 'tone' => 'violet'],
        ['label' => 'Expired', 'value' => $statistics['expired'], 'icon' => 'error', 'tone' => 'cyan'],
    ],
])

<x-data-view
    :data="$lists"
    title="Compliance tracking"
    subtitle="Monitor expiry dates and send renewal reminders"
/>
@endsection
