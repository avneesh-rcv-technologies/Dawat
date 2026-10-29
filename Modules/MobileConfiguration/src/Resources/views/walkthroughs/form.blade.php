@extends('backend::layouts.app')

@section('title', isset($walkthrough) ? 'Edit Walkthrough' : 'Create Walkthrough')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')], 
    ['label' => 'Mobile Configuration', 'url' => ''],
    ['label' => 'Walkthroughs', 'url' => route('mobileconfiguration.walkthroughs.index')],
    ['label' => isset($walkthrough) ? 'Edit Walkthrough' : 'Create Walkthrough','url' => ''],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="isset($walkthrough) ? 'Edit Walkthrough' : 'Create Walkthrough'" />
@endsection

@section('content')

<form id="walkthroughForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6 form-ajax" method="POST"
    action="@isset($walkthrough) {{ route('mobileconfiguration.walkthroughs.update', $walkthrough->id) }} @else {{ route('mobileconfiguration.walkthroughs.store') }} @endisset"
    enctype="multipart/form-data">
    @csrf
    @isset($walkthrough) @method('PUT') @endisset

    <!-- Left Column (2/3) -->
    <div class="lg:col-span-2 bg-white rounded-lg p-6 space-y-6">

        <!-- Title -->
        <div>
            <label for="title" class="custom-label required">Title</label>
            <input type="text" name="title" id="title" 
                value="{{ old('title', $walkthrough->title ?? '') }}" 
                class="input-field" required>
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="custom-label required">Description</label>
            <textarea name="description" id="description" rows="6" 
                class="input-field" required>{{ old('description', $walkthrough->description ?? '') }}</textarea>
        </div>

        <!-- Walkthrough Image -->
        @include('filemanager::components.file-uploader', [
            'object_type' => 'mobile_walkthrough', 
            'object_id' => $walkthrough->id ?? 0, 
            'name' => 'image',
            'label' => 'Walkthrough Image'
        ])

    </div>

    <!-- Right Column (1/3) -->
    <div class="bg-white rounded-lg p-6 space-y-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Walkthrough Settings</h3>

        <!-- Status -->
        <div>
            <label for="status" class="custom-label required">Status</label>
            <select name="status" id="status" class="input-field" required>
                <option value="1" {{ old('status', $walkthrough->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status', $walkthrough->status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Position -->
        <div>
            <label for="position" class="custom-label">Position</label>
            <input type="number" name="position" id="position" 
                value="{{ old('position', $walkthrough->position ?? 0) }}" 
                class="input-field" min="0">
            <small class="text-gray-500">Lower numbers appear first</small>
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="w-full btn-primary">
                @isset($walkthrough) Update Walkthrough @else Create Walkthrough @endisset
            </button>
        </div>
    </div>
</form>

@endsection