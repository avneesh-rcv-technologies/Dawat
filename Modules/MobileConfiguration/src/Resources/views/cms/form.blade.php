@extends('backend::layouts.app')

@php
    $mobileCms = $mobileCms ?? null;
@endphp

@section('title', isset($mobileCms) ? 'Edit Mobile CMS' : 'Create Mobile CMS')
@section('content')
<form id="mobileCmsForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6 form-ajax" method="POST"
    action="@isset($mobileCms) {{ route('mobileconfiguration.cms.update', $mobileCms->id) }} @else {{ route('mobileconfiguration.cms.store') }} @endisset"
    enctype="multipart/form-data">
    @csrf
    @isset($mobileCms) @method('PUT') @endisset

    <!-- Left Column (2/3) -->
    <div class="lg:col-span-2 bg-white rounded-lg p-6 space-y-6">

        <!-- Name -->
        <div>
            <label for="name" class="custom-label required">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', optional($mobileCms)->name ?? '') }}"
                class="input-field" required>
        </div>

        <!-- CMS Page Selector -->
        <div>
            @php
            $selectedCmsPage = null;
            if (isset($mobileCms) && $mobileCms->cms_page_id) {
            $selectedCmsPage = [$mobileCms->cms_page_id];
            } elseif (old('cms_page_id')) {
            $selectedCmsPage = [old('cms_page_id')];
            }
            @endphp

            <x-advanced-selector name="cms_page_id" table="cms_page_settings" search-column="page_title"
                value-column="id" display-column="page_title" :multiple="false" :required="true"
                :selected="$selectedCmsPage" placeholder="Select CMS page..." label="CMS Page"
                :additional-columns="['slug']" :filters="[['column' => 'status', 'value' => 1]]" order-by="page_title"
                order-direction="asc" />
        </div>
        <!-- Mobile CMS Image -->
        @include('filemanager::components.file-uploader', [
        'object_type' => 'mobile_cms',
        'object_id' => optional($mobileCms)->id ?? 0,
        'name' => 'image',
        'label' => 'Mobile CMS Image',
        'is_multiple' => false,
        'accept' => 'image/jpeg,image/png',
        'span_text'=>'Drag and drop an image here or click to select a file. (JPEG, PNG)',
        'existingFiles' => optional($mobileCms)->image ? [optional($mobileCms)->image] : []
        ])

    </div>

    <!-- Right Column (1/3) -->
    <div class="bg-white rounded-lg p-6 space-y-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Mobile CMS Settings</h3>

        <!-- Status -->
        <div>
            <label for="status" class="custom-label required">Status</label>
            <select name="status" id="status" class="input-field" required>
                <option value="1" {{ old('status', optional($mobileCms)->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status', optional($mobileCms)->status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Position -->
        <div>
            <label for="position" class="custom-label">Position</label>
            <input type="number" name="position" id="position" value="{{ old('position', optional($mobileCms)->position ?? 0) }}"
                class="input-field" min="0">
            <small class="text-gray-500">Lower numbers appear first</small>
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" name="button" class="w-full btn-primary">
                @isset($mobileCms) Update Mobile CMS @else Create Mobile CMS @endisset
            </button>
        </div>
    </div>
</form>

@endsection
