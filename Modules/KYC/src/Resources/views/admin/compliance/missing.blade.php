@extends('backend::layouts.app')

@section('title', 'Missing Compliance')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Compliance Records', 'url' => route('admin.kyc.compliance.index')],
        ['label' => 'Missing uploads'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Missing compliance'" />
@endsection

@section('content')
@include('kyc::admin.partials.kyc-styles')

@include('kyc::admin.partials.stat-grid', [
    'stats' => [
        ['label' => 'Pending uploads', 'value' => $missingCount, 'icon' => 'person_off', 'tone' => 'cyan'],
    ],
])

<x-data-view
    :data="$lists"
    title="Restaurant Owners without compliance"
    subtitle="Send reminders for missing compliance documents"
/>
@endsection
