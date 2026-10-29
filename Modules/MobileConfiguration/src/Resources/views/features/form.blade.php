@extends('backend::layouts.app')

@section('title', isset($feature) ? 'Edit Feature' : 'Create Feature')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')], 
    ['label' => 'Mobile Configuration', 'url' => ''],
    ['label' => 'Features', 'url' => route('mobileconfiguration.features.index')],
    ['label' => isset($feature) ? 'Edit Feature' : 'Create Feature','url' => ''],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="isset($feature) ? 'Edit Feature' : 'Create Feature'" />
@endsection

@section('content')

<form id="featureForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6 form-ajax" method="POST"
    action="@isset($feature) {{ route('mobileconfiguration.features.update', $feature->id) }} @else {{ route('mobileconfiguration.features.store') }} @endisset">
    @csrf
    @isset($feature) @method('PUT') @endisset

    <!-- Left Column (2/3) -->
    <div class="lg:col-span-2 bg-white rounded-lg p-6 space-y-6">

        <!-- Feature Name -->
        <div>
            <label for="name" class="custom-label required">Feature Name</label>
            <input type="text" name="name" id="name" 
                value="{{ old('name', $feature->name ?? '') }}" 
                class="input-field" required>
        </div>

        <!-- Slug -->
        <div>
            <label for="slug" class="custom-label">Slug</label>
            <input type="text" name="slug" id="slug" 
                value="{{ old('slug', $feature->slug ?? '') }}" 
                class="input-field" placeholder="Auto-generated from name">
            <small class="text-gray-500">Leave empty to auto-generate from name</small>
        </div>

        <!-- Used For -->
        <div>
            <label for="used_for" class="custom-label required">Used For</label>
            <select name="used_for" id="used_for" class="input-field" required>
                <option value="">Select Usage</option>
                <option value="general" {{ old('used_for', $feature->used_for ?? '') === 'general' ? 'selected' : '' }}>General</option>
                <option value="carousel" {{ old('used_for', $feature->used_for ?? '') === 'carousel' ? 'selected' : '' }}>Carousel</option>
            </select>
        </div>

        <!-- Type -->
        <div>
            <label for="type" class="custom-label required">Type</label>
            <select name="type" id="type" class="input-field" required>
                <option value="">Select Type</option>
                <option value="product" {{ old('type', $feature->type ?? '') === 'product' ? 'selected' : '' }}>Product</option>
                <option value="category" {{ old('type', $feature->type ?? '') === 'category' ? 'selected' : '' }}>Category</option>
            </select>
        </div>

        <!-- Selection Type -->
        <div>
            <label for="selection_type" class="custom-label required">Selection Type</label>
            <select name="selection_type" id="selection_type" class="input-field" required>
                <option value="">Select Selection Type</option>
                <option value="newest" {{ old('selection_type', $feature->selection_type ?? '') === 'newest' ? 'selected' : '' }}>Newest</option>
                <option value="oldest" {{ old('selection_type', $feature->selection_type ?? '') === 'oldest' ? 'selected' : '' }}>Oldest</option>
                <option value="custom" {{ old('selection_type', $feature->selection_type ?? '') === 'custom' ? 'selected' : '' }}>Custom</option>
            </select>
        </div>

        <!-- Custom Selection (Dynamic based on type and selection_type) -->
        <div id="custom-selection-container" style="display: none;">
            <!-- Product Selector -->
            <div id="product-selector" style="display: none;">
                <x-advanced-selector
                    name="custom_products"
                    table="product_descriptions"
                    search-column="name"
                    value-column="product_id"
                    display-column="name"
                    :multiple="true"
                    :required="false"
                    :selected="old('custom_products', ($feature->type ?? '') === 'product' && ($feature->selection_type ?? '') === 'custom' ? ($feature->custom_items ?? []) : [])"
                    placeholder="Select products..."
                    label="Custom Products"
                    :additional-columns="[]"
                    :filters="[['column' => 'language_code', 'value' => 'en']]"
                    order-by="name"
                    order-direction="asc" />
            </div>

            <!-- Category Selector -->
            <div id="category-selector" style="display: none;">
                <x-advanced-selector
                    name="custom_categories"
                    table="categories"
                    search-column="name"
                    value-column="id"
                    display-column="name"
                    :multiple="true"
                    :required="false"
                    :selected="old('custom_categories', ($feature->type ?? '') === 'category' && ($feature->selection_type ?? '') === 'custom' ? ($feature->custom_items ?? []) : [])"
                    placeholder="Select categories..."
                    label="Custom Categories"
                    :additional-columns="[]"
                    :filters="[]"
                    order-by="name"
                    order-direction="asc" />
            </div>
        </div>

        <!-- Items Count -->
        <div id="items-count-container">
            <label for="items_count" class="custom-label">Number of Items to Show</label>
            <input type="number" name="items_count" id="items_count" 
                value="{{ old('items_count', $feature->items_count ?? 10) }}" 
                class="input-field" min="1" max="100">
            <small class="text-gray-500">Maximum 100 items (not applicable for custom selection)</small>
        </div>

    </div>

    <!-- Right Column (1/3) -->
    <div class="bg-white rounded-lg p-6 space-y-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Feature Settings</h3>

        <!-- Status -->
        <div>
            <label for="status" class="custom-label required">Status</label>
            <select name="status" id="status" class="input-field" required>
                <option value="1" {{ old('status', $feature->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status', $feature->status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Position -->
        <div>
            <label for="position" class="custom-label">Position</label>
            <input type="number" name="position" id="position" 
                value="{{ old('position', $feature->position ?? 0) }}" 
                class="input-field" min="0">
            <small class="text-gray-500">Lower numbers appear first</small>
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="w-full btn-primary">
                @isset($feature) Update Feature @else Create Feature @endisset
            </button>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const selectionTypeSelect = document.getElementById('selection_type');
    const customSelectionContainer = document.getElementById('custom-selection-container');
    const productSelector = document.getElementById('product-selector');
    const categorySelector = document.getElementById('category-selector');
    const itemsCountField = document.getElementById('items_count');

    function toggleCustomSelection() {
        const selectedType = typeSelect.value;
        const selectedSelectionType = selectionTypeSelect.value;
        
        // Hide all fields first
        customSelectionContainer.style.display = 'none';
        productSelector.style.display = 'none';
        categorySelector.style.display = 'none';

        // Show custom selection if selection type is custom
        if (selectedSelectionType === 'custom') {
            customSelectionContainer.style.display = 'block';
            
            if (selectedType === 'product') {
                productSelector.style.display = 'block';
            } else if (selectedType === 'category') {
                categorySelector.style.display = 'block';
            }
        }

        // Disable items count for custom selection
        if (selectedSelectionType === 'custom') {
            itemsCountField.disabled = true;
            itemsCountField.removeAttribute('required');
            itemsCountField.value = '';
        } else {
            itemsCountField.disabled = false;
            itemsCountField.setAttribute('required', 'required');
            if (!itemsCountField.value) {
                itemsCountField.value = 10;
            }
        }
    }

    // Initialize on page load
    toggleCustomSelection();

    // Handle type and selection type changes
    typeSelect.addEventListener('change', toggleCustomSelection);
    selectionTypeSelect.addEventListener('change', toggleCustomSelection);

    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (!slugField.value || slugField.dataset.autoGenerated) {
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            slugField.value = slug;
            slugField.dataset.autoGenerated = 'true';
        }
    });

    // Mark slug as manually edited
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.autoGenerated = 'false';
    });

    // Handle form submission
    document.getElementById('featureForm').addEventListener('submit', function(e) {
        const selectionType = document.getElementById('selection_type').value;
        const itemsCountField = document.getElementById('items_count');
        
        // If custom selection, clear items_count to avoid validation issues
        if (selectionType === 'custom') {
            itemsCountField.value = '';
        }
    });
});
</script>
@endsection