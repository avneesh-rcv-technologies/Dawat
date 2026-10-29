@extends('backend::layouts.app')

@section('title', 'KYC Requests')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'KYC Requests'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'KYC Requests'" />
@endsection

@section('content')
@include('kyc::admin.partials.kyc-styles')

@include('kyc::admin.partials.stat-grid', [
    'stats' => [
        ['label' => 'Total requests', 'value' => $statistics['total'], 'icon' => 'send', 'tone' => 'indigo'],
        ['label' => 'Upcoming', 'value' => $statistics['upcoming'], 'icon' => 'event_available', 'tone' => 'emerald'],
        ['label' => 'Overdue', 'value' => $statistics['overdue'], 'icon' => 'warning', 'tone' => 'cyan'],
        ['label' => 'Due this week', 'value' => $statistics['this_week'], 'icon' => 'date_range', 'tone' => 'violet'],
    ],
])

<div class="kyc-panel mt-4">
    <div class="kyc-panel-body">
        <x-data-view
            :data="$lists"
            title="Document requests"
            subtitle="Send and track KYC document requests to restaurant owners"
        />
    </div>
</div>
@endsection
