@extends('backend::layouts.app')

@section('title', isset($banner) ? 'Edit Banner' : 'Create Banner')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')], 
    ['label' => 'Mobile Configuration', 'url' => ''],
    ['label' => 'Banners', 'url' => route('mobileconfiguration.banners.index')],
    ['label' => isset($banner) ? 'Edit Banner' : 'Create Banner','url' => ''],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="isset($banner) ? 'Edit Banner' : 'Create Banner'" />
@endsection

@section('content')

<form id="bannerForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6 form-ajax" method="POST"
    action="@isset($banner) {{ route('mobileconfiguration.banners.update', $banner->id) }} @else {{ route('mobileconfiguration.banners.store') }} @endisset"
    enctype="multipart/form-data">
    @csrf
    @isset($banner) @method('PUT') @endisset

    <!-- Left Column (2/3) -->
    <div class="lg:col-span-2 bg-white rounded-lg p-6 space-y-6">

        <!-- Banner Name -->
        <div>
            <label for="name" class="custom-label required">Banner Name</label>
            <input type="text" name="name" id="name" 
                value="{{ old('name', $banner->name ?? '') }}" 
                class="input-field" required>
        </div>

        <!-- Banner Type -->
        <div>
            <label for="type" class="custom-label required">Banner Type</label>
            <select name="type" id="type" class="input-field" required>
                <option value="">Select Type</option>
                <option value="product" {{ old('type', $banner->type ?? '') === 'product' ? 'selected' : '' }}>Product</option>
                <option value="category" {{ old('type', $banner->type ?? '') === 'category' ? 'selected' : '' }}>Category</option>
                <option value="external_link" {{ old('type', $banner->type ?? '') === 'external_link' ? 'selected' : '' }}>External Link</option>
                <option value="no_link" {{ old('type', $banner->type ?? '') === 'no_link' ? 'selected' : '' }}>No Link</option>
            </select>
        </div>

        <!-- Link Value (Dynamic based on type) -->
        <div id="link-value-container" style="display: none;">
            <!-- Product Selector -->
            <div id="product-selector" style="display: none;">
                <x-advanced-selector
                    name="product_id"
                    table="product_descriptions"
                    search-column="name"
                    value-column="product_id"
                    display-column="name"
                    :multiple="false"
                    :required="false"
                    :selected="old('type_value', ($banner->type ?? '') === 'product' ? ($banner->type_value ?? '') : '')"
                    placeholder="Select product..."
                    label="Product"
                    :additional-columns="['short_description']"
                    :filters="[]"
                    order-by="name"
                    order-direction="asc" />
            </div>

            <!-- Category Selector -->
            <div id="category-selector" style="display: none;">
                <x-advanced-selector
                    name="category_id"
                    table="categories"
                    search-column="name"
                    value-column="id"
                    display-column="name"
                    :multiple="false"
                    :required="false"
                    :selected="old('type_value', ($banner->type ?? '') === 'category' ? ($banner->type_value ?? '') : '')"
                    placeholder="Select category..."
                    label="Category"
                    :additional-columns="[]"
                    :filters="[]"
                    order-by="name"
                    order-direction="asc" />
            </div>

            <!-- External Link Input -->
            <div id="external-link-input" style="display: none;">
                <label for="external_url" class="custom-label required">External URL</label>
                <input type="url" name="external_url" id="external_url" 
                    value="{{ old('type_value', ($banner->type ?? '') === 'external_link' ? ($banner->type_value ?? '') : '') }}" 
                    class="input-field" placeholder="https://example.com">
            </div>

            <!-- Hidden input for actual type_value -->
            <input type="hidden" name="type_value" id="type_value" value="{{ old('type_value', $banner->type_value ?? '') }}">
        </div>


        <label for="" class="custom-label">Image</label>
        <!-- Banner Image -->
        @include('filemanager::components.file-uploader', [
            'object_type' => 'mobile_banner', 
            'object_id' => $banner->id ?? 0, 
            'name' => 'image',
            'label' => 'Banner Image',
            'is_multiple' => false,
            'accept' => 'image/jpeg,image/png',
            'span_text'=>'Drag and drop an image here or click to select a file. (JPEG, PNG)',
            'existingFiles' => $banner->image ? [$banner->image] : []
        ])

    </div>

    <!-- Right Column (1/3) -->
    <div class="bg-white rounded-lg p-6 space-y-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Banner Settings</h3>

        <!-- Status -->
        <div>
            <label for="status" class="custom-label required">Status</label>
            <select name="status" id="status" class="input-field" required>
                <option value="1" {{ old('status', $banner->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status', $banner->status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Position -->
        <div>
            <label for="position" class="custom-label">Position</label>
            <input type="number" name="position" id="position" 
                value="{{ old('position', $banner->position ?? 0) }}" 
                class="input-field" min="0">
            <small class="text-gray-500">Lower numbers appear first</small>
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="btn-primary w-full">
                @isset($banner) Update Banner @else Create Banner @endisset
            </button>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const linkValueContainer = document.getElementById('link-value-container');
    const productSelector = document.getElementById('product-selector');
    const categorySelector = document.getElementById('category-selector');
    const externalLinkInput = document.getElementById('external-link-input');
    const linkValueHidden = document.getElementById('type_value');

    function toggleLinkFields() {
        const selectedType = typeSelect.value;
        
        // Hide all fields first
        linkValueContainer.style.display = 'none';
        productSelector.style.display = 'none';
        categorySelector.style.display = 'none';
        externalLinkInput.style.display = 'none';

        // Show appropriate field based on type
        if (selectedType === 'product') {
            linkValueContainer.style.display = 'block';
            productSelector.style.display = 'block';
        } else if (selectedType === 'category') {
            linkValueContainer.style.display = 'block';
            categorySelector.style.display = 'block';
        } else if (selectedType === 'external_link') {
            linkValueContainer.style.display = 'block';
            externalLinkInput.style.display = 'block';
        }
    }

    // Initialize on page load
    toggleLinkFields();

    // Handle type change
    typeSelect.addEventListener('change', toggleLinkFields);

    // Handle form submission to set correct type_value
    document.getElementById('bannerForm').addEventListener('submit', function(e) {
        const selectedType = typeSelect.value;
        
        if (selectedType === 'product') {
            const productId = document.querySelector('input[name="product_id"]')?.value;
            linkValueHidden.value = productId || '';
        } else if (selectedType === 'category') {
            const categoryId = document.querySelector('input[name="category_id"]')?.value;
            linkValueHidden.value = categoryId || '';
        } else if (selectedType === 'external_link') {
            const externalUrl = document.getElementById('external_url').value;
            linkValueHidden.value = externalUrl || '';
        } else {
            linkValueHidden.value = '';
        }
    });
});
</script>
@endsection