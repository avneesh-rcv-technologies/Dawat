@extends('backend::layouts.app')

@php
    $mobileCms = $mobileCms ?? null;
    $page = optional($mobileCms)->cmsPage;
    $pageTitle = isset($mobileCms) ? 'Edit Mobile CMS Page' : 'Create Mobile CMS Page';
@endphp

@section('title', $pageTitle)

@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'System Settings', 'url' => route('settings.index')],
            ['label' => 'Mobile CMS', 'url' => route('mobileconfiguration.cms.index')],
            ['label' => $pageTitle],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$pageTitle" />
@endsection

@section('content')
<form id="mobileCmsForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6 form-ajax" method="POST"
    action="@isset($mobileCms) {{ route('mobileconfiguration.cms.update', $mobileCms->id) }} @else {{ route('mobileconfiguration.cms.store') }} @endisset"
    enctype="multipart/form-data">
    @csrf
    @isset($mobileCms) @method('PUT') @endisset

    <!-- Left Column (2/3): page content -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg p-6 space-y-6">
            <!-- Title -->
            <div>
                <label for="page_title" class="custom-label required">Title</label>
                <input type="text" name="page_title" id="page_title" maxlength="100"
                    value="{{ old('page_title', $page->page_title ?? optional($mobileCms)->name ?? '') }}"
                    class="input-field" placeholder="e.g. About Us" required>
                <p id="page_title_error" class="text-sm text-red-600 mt-1 hidden"></p>
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="custom-label">Slug</label>
                <input type="text" name="slug" id="slug" maxlength="120"
                    value="{{ old('slug', $page->slug ?? '') }}" class="input-field font-mono text-sm"
                    placeholder="Auto-generated from the title">
                <small class="text-helper">The mobile app opens this page by its slug. Leave empty to generate it from the title.</small>
            </div>

            <!-- Content -->
            <div>
                <label for="description" class="custom-label required">Content</label>
                <textarea id="description" editor="true" name="description" class="input-field"
                    rows="10">{{ old('description', $page->description ?? '') }}</textarea>
            </div>

            <!-- Image -->
            @include('filemanager::components.file-uploader', [
                'object_type' => 'mobile_cms',
                'object_id' => optional($mobileCms)->id ?? 0,
                'name' => 'image',
                'label' => 'Page Image',
                'is_multiple' => false,
                'accept' => 'image/jpeg,image/png',
                'span_text' => 'Drag and drop an image here or click to select a file. (JPEG, PNG)',
                'existingFiles' => optional($mobileCms)->image ? [optional($mobileCms)->image] : [],
            ])
        </div>

        <!-- SEO -->
        <div class="bg-white rounded-lg p-6 space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">SEO</h3>
                <p class="text-helper">Optional meta information for this page.</p>
            </div>

            <div>
                <label for="meta_title" class="custom-label">Meta Title</label>
                <input type="text" name="meta_title" id="meta_title" maxlength="100"
                    value="{{ old('meta_title', $page->meta_title ?? '') }}" class="input-field">
            </div>

            <div>
                <label for="meta_description" class="custom-label">Meta Description</label>
                <textarea name="meta_description" id="meta_description" rows="3" class="input-field"
                    maxlength="150">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
            </div>

            <div>
                <label for="meta_keywords" class="custom-label">Meta Keywords</label>
                <textarea name="meta_keywords" id="meta_keywords" rows="2" class="input-field" maxlength="150"
                    placeholder="keyword1, keyword2, keyword3">{{ old('meta_keywords', $page->meta_keywords ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Right Column (1/3): publishing -->
    <div class="bg-white rounded-lg p-6 space-y-6 self-start">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Publishing</h3>

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
            <input type="number" name="position" id="position" min="0"
                value="{{ old('position', optional($mobileCms)->position ?? 0) }}" class="input-field">
            <small class="text-helper">Lower numbers appear first in the app.</small>
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" name="button" class="w-full btn-primary">
                @isset($mobileCms) Update Page @else Create Page @endisset
            </button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const title = document.getElementById('page_title');
        const slug = document.getElementById('slug');
        const errorEl = document.getElementById('page_title_error');

        // Fill the slug from the title until the admin edits it by hand.
        slug.dataset.autoGenerated = slug.value ? 'false' : 'true';
        title.addEventListener('input', function () {
            if (slug.dataset.autoGenerated === 'true') {
                slug.value = this.value.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });
        slug.addEventListener('input', function () {
            this.dataset.autoGenerated = this.value ? 'false' : 'true';
        });

        // Same title rule as the server, so the admin sees it before submitting.
        document.getElementById('mobileCmsForm').addEventListener('submit', function (e) {
            const value = (title.value || '').trim();
            if (!/^[\p{L}0-9\s\-\&\(\)\:\/\'\"\,\.]+$/u.test(value)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                errorEl.textContent = 'Title contains invalid characters. Only letters, numbers, spaces and - & ( ) : / \' " , . are allowed.';
                errorEl.classList.remove('hidden');
                title.focus();
                return false;
            }
            errorEl.classList.add('hidden');
            errorEl.textContent = '';
        }, true);
    });
</script>
@endsection
