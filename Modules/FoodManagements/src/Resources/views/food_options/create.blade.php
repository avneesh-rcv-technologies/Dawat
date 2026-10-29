@extends('backend::layouts.app')

@section('title', 'Create Option Group')

@section('breadcrumbs')
    @php
        $title = 'Create Option Group';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Food Options', 'url' => route('admin.foodmanagements.food-option-groups.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Create New Option Group</h3>
        <p class="text-sm text-gray-600">Add a new option group with individual options</p>
    </div>

    @include('foodmanagements::food_options.form')
</div>
@endsection