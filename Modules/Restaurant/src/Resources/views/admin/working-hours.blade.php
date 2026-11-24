@extends('backend::layouts.app')

@section('title', 'Settings | Working Hours')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Settings', 'url' => route('settings.index')],
        ['label' => 'Working Hours'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Working Hours'" />
@endsection

@section('content')
<div class="working-hours-settings">
    <p class="restaurant-name mb-4 text-sm text-gray-600">{{ $restaurant->name }}</p>
    @unless($canUpdateHours)
        <p class="mb-4 text-sm text-gray-600">You have permission to view working hours. Update permission is required to save changes.</p>
    @endunless
    @include('restaurant::admin.partials.working-hours-form', [
        'hoursAction' => route('settings.restaurant.working-hours.update'),
    ])
</div>
@endsection
