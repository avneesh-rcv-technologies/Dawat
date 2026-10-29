@extends('backend::layouts.app')

@section('title', isset($feature) ? 'Edit Feature' : 'Add Feature')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Food Features', 'url' => route('admin.foodmanagements.features.index')],
        ['label' => isset($feature) ? 'Edit Feature' : 'Add Feature', 'url' => ''],
    ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="isset($feature) ? 'Edit Feature' : 'Add Feature'" />
@endsection

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    @include('foodmanagements::food_features.partials.form-content')
</div>
@endsection
