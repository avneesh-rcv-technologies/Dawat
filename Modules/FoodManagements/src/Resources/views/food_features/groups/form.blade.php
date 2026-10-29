@extends('backend::layouts.app')

@section('title', isset($group) ? 'Edit Feature Group' : 'Add Feature Group')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Feature Groups', 'url' => route('admin.foodmanagements.features.group_index')],
        ['label' => isset($group) ? 'Edit Feature Group' : 'Add Feature Group', 'url' => ''],
    ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="isset($group) ? 'Edit Feature Group' : 'Add Feature Group'" />
@endsection

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    @include('foodmanagements::food_features.groups.partials.form-content')
</div>
@endsection
