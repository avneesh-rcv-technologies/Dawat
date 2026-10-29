@extends('backend::layouts.app')

@section('title', 'KYC Types')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'System Settings', 'url' => route('settings.index') ],
        ['label' => 'KYC Types'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'KYC Types'" />
@endsection

@section('content')
@include('kyc::admin.partials.kyc-styles')

<x-data-view
    :data="$lists"
    title="Document types"
    subtitle="Manage KYC and compliance document types and their fields"
/>
@endsection
