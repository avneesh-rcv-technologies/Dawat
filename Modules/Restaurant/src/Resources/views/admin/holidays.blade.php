@extends('backend::layouts.app')

@section('title', 'Settings | Holiday Management')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Settings', 'url' => route('settings.index')],
        ['label' => 'Holiday Management'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Holiday Management'" />
@endsection

@section('content')
<div class="holiday-settings">
    <p class="mb-4 text-sm text-gray-600">{{ $restaurant->name }}</p>
    @unless($canUpdateHolidays)
        <p class="mb-4 text-sm text-gray-600">You have permission to view holidays. Update permission is required to save changes.</p>
    @endunless
    @include('restaurant::admin.partials.holidays-form', [
        'holidaysAction' => route('settings.restaurant.holidays.update'),
    ])
</div>
@endsection
