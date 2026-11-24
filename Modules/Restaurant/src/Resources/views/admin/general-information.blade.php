@extends('backend::layouts.app')

@section('title', 'Settings | General Information')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Settings', 'url' => route('settings.index')],
        ['label' => 'General Information'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'General Information'" />
@endsection

@section('content')
@include('restaurant::admin.partials.restaurant-form-styles')
<div class="space-y-6">
    @unless($canUpdateGeneral || $canUpdateAddress)
        <p class="text-sm text-gray-600">You have permission to view this information. Update permission is required to save changes.</p>
    @endunless
    @include('restaurant::admin.partials.general-information-form', [
        'showPasswordField' => false,
        'generalAction' => route('settings.restaurant.general-information.update'),
        'addressAction' => route('settings.restaurant.general-information.address'),
    ])
</div>
@include('restaurant::admin.partials.general-information-script')
@include('restaurant::admin.partials.restaurant-address-script')
@endsection
