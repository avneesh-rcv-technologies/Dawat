@extends('backend::layouts.app')

@section('title', 'KYC Submissions')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'KYC Submissions', 'url' => route('admin.kyc.submissions.index')],
        ['label' => 'Restaurant Owner Documents'],
    ];
@endphp
<div class="flex items-center gap-4">
    <a href="{{ route('admin.kyc.submissions.index') }}" class="text-gray-500 hover:text-gray-700 bg-white border border-gray-200 rounded-lg p-1.5 shadow-sm transition-colors">
        <span class="material-icons-outlined">arrow_back</span>
    </a>
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Restaurant Owner Documents'" />
</div>
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
    title="Submissions"
    subtitle="Review and manage restaurant_owner KYC document submissions"
/>
@endsection
