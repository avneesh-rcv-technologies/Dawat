@extends('backend::layouts.app')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Blog Categories', 'url' => route('admin.blogs.blog_categories.index')],
        ['label' => 'Edit Blog Category', 'url' => ''],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Edit Blog Category'" />
@endsection

@section('content')
<div class="py-6 min-h-screen">
   

    <form id="category-form" method="POST" action="{{ route('admin.blogs.blog_categories.update', $category->id) }}"
        enctype="multipart/form-data" class="form-ajax grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @method('PUT')

        <!-- Left Column (Main Fields) -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-4">
            <div class="mb-6 border border-gray-200 rounded-lg">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Category Details</h3>
                </div>
                <div class="p-4 space-y-6">
                    <!-- Name -->
                    <div class="mb-4">
                        <label class="custom-label">Name <span class="text-red-600">*</span></label>
                        <input type="text" name="name" id="name" class="input-field"
                            value="{{ old('name', $category->name) }}" required>
                    </div>

                    <!-- Slug -->
                    <div class="mb-4">
                        <label class="custom-label">Slug <span class="text-red-600">*</span></label>
                        <input type="text" name="slug" id="slug" class="input-field"
                            value="{{ old('slug', $category->slug) }}" required readonly>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="custom-label">Description</label>
                        <textarea id="long-description-editor" editor="true" name="long_description" rows="8">{{ old('long_description', $category->long_description ?? '') }}</textarea>
                       
                        @error('long_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Position -->
                    <div class="mb-4">
                        <label class="custom-label">Position</label>
                        <input type="number" name="position" class="input-field"
                            value="{{ old('position', $category->position) }}">
                    </div>

                    <!-- Banner Image -->
                    {{-- <div class="mb-4">
                        <label class="custom-label">Banner Image</label>
                        @include('filemanager::components.file-uploader', [
                            'object_type' => 'blog_category_banner',
                            'object_id' => $category->id,
                            'name' => 'banner_path',
                            'isMultiple' => false,
                        ])
                    </div> --}}


                </div>
            </div>
        </div>

        <!-- Right Column (Meta / Status / Parent / Locale) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6 border border-gray-200 rounded-lg">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Additional Info</h3>
                </div>
                <div class="p-4 space-y-6">
                    <!-- Status -->
                    <div class="mb-4">
                        <label class="custom-label">Status</label>
                        <select name="status" class="input-field">
                            <option value="1" {{ old('status', $category->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $category->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <!-- Parent Category -->
                    <div class="mb-4">
                        <x-advanced-selector
                            name="parent_id"
                            table="blog_categories"
                            search-column="name"
                            value-column="id"
                            display-column="name"
                            :multiple="false"
                            :required="false"
                            :selected="old('parent_id', $category->parent_id) ? [old('parent_id', $category->parent_id)] : []"
                            placeholder="Select parent category..."
                            label="Parent Category"
                            :additional-columns="[]"
                            :filters="[['column' => 'id', 'operator' => '!=', 'value' => $category->id]]"
                            order-by="name"
                            order-direction="asc"
                        />
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex space-x-3">
                <x-button type="submit" class="btn-primary w-full" label="Update" icon="" name="button" />
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-generate slug from name
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        nameInput.addEventListener('input', function() {
            const name = this.value.trim().toLowerCase();
            const slug = name.replace(/\s+/g, '-').replace(/[^\w-]+/g, '');
            slugInput.value = slug;
        });
    });

  
</script>
@endsection
