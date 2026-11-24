@extends('backend::layouts.app')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Blog Categories', 'url' => route('admin.blogs.blog_categories.index')],
        ['label' => 'Create Blog Category', 'url' => ''],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Create Blog Category'" />
@endsection

@section('content')
<div class="py-6 min-h-screen">
    
    <form method="POST"
          action="{{ route('admin.blogs.blog_categories.store') }}"
          enctype="multipart/form-data"
          class="form-ajax grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf

        <!-- Left Column -->
        <div class="lg:col-span-2 bg-white rounded-lg  p-4">
            <div class="mb-6 border border-gray-200 rounded-lg">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Category Details</h3>
                </div>
                <div class="p-4 space-y-6">

                    <!-- Name -->
                    <div>
                        <label class="custom-label">Name <span class="text-red-600">*</span></label>
                        <input type="text" name="name" id="name" class="input-field" value="{{ old('name') }}" required>
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="custom-label">Slug <span class="text-red-600">*</span></label>
                        <input type="text" name="slug" id="slug" class="input-field" value="{{ old('slug') }}" required>
                        <p class="text-sm text-gray-500">Auto-generated from name, but you can edit manually.</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="custom-label">Description</label>
                        <textarea name="description" rows="4" class="input-field">{{ old('description') }}</textarea>
                    </div>

                    <!-- Position -->
                    <div>
                        <label class="custom-label">Position</label>
                        <input type="number" name="position" class="input-field" value="{{ old('position', 0) }}">
                    </div>

                    <!-- Banner -->
                    {{-- <div class="mb-4">
                        <label class="custom-label">Banner Image</label>
                        @include('filemanager::components.file-uploader', [
                            'object_type' => 'blog_category_banner',
                            'object_id' => 0,
                            'name' => 'banner_path',
                            'isMultiple' => false,
                        ])
                    </div> --}}
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="bg-white rounded-lg  p-4">
            <div class="mb-6 border border-gray-200 rounded-lg">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Additional Info</h3>
                </div>
                <div class="p-4 space-y-6">

                    <!-- Status -->
                    <div>
                        <label class="custom-label">Status</label>
                        <select name="status" class="input-field">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <!-- Parent -->
                    <div>
                        <x-advanced-selector
                            name="parent_id"
                            table="blog_categories"
                            search-column="name"
                            value-column="id"
                            display-column="name"
                            :multiple="false"
                            :required="false"
                            :selected="old('parent_id') ? [old('parent_id')] : []"
                            placeholder="Select parent category..."
                            label="Parent Category"
                            :additional-columns="[]"
                            :filters="[]"
                            order-by="name"
                            order-direction="asc"
                        />
                    </div>

                </div>
            </div>

            <!-- Submit -->
            <div class="flex space-x-3">
                <x-button type="submit" class="btn-primary w-full" label="Save" icon="" name="button" />
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const nameInput = document.getElementById("name");
    const slugInput = document.getElementById("slug");
    let slugManuallyEdited = false;

    // Detect if slug is manually changed
    slugInput.addEventListener("input", function () {
        slugManuallyEdited = true;
    });

    // Auto-generate slug only if user has not typed in slug field
    nameInput.addEventListener("input", function () {
        if (!slugManuallyEdited) {
            let slug = this.value
                .toLowerCase()
                .trim()
                .replace(/\s+/g, '-')      // replace spaces with -
                .replace(/[^\w\-]+/g, '')  // remove invalid chars
                .replace(/\-\-+/g, '-')    // collapse multiple -
                .replace(/^-+|-+$/g, '');  // trim - from start/end
            slugInput.value = slug;
        }
    });
});
</script>
@endsection
