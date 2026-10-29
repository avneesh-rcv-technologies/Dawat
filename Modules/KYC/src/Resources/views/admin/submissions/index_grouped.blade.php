@extends('backend::layouts.app')

@section('title', 'KYC Submissions (Restaurant Owners)')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'KYC Submissions'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'KYC Submissions'" />
@endsection

@section('content')
@include('kyc::admin.partials.kyc-styles')

@include('kyc::admin.partials.stat-grid', [
    'stats' => [
        ['label' => 'Total submissions', 'value' => $statistics['total'], 'icon' => 'description', 'tone' => 'indigo'],
        ['label' => 'Pending review', 'value' => $statistics['pending'], 'icon' => 'schedule', 'tone' => 'violet'],
        ['label' => 'Approved', 'value' => $statistics['approved'], 'icon' => 'check_circle', 'tone' => 'emerald'],
        ['label' => 'Rejected', 'value' => $statistics['rejected'], 'icon' => 'cancel', 'tone' => 'cyan'],
    ],
])

<x-data-view
    :data="$lists"
    title="Restaurant Owner KYC Overview"
    subtitle="List of restaurants and their KYC submission statuses"
/>
@endsection
