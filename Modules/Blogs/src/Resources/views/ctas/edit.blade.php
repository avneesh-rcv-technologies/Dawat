@extends('backend::layouts.app')
@section('title', 'Edit Blog CTA')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Blog CTAs', 'url' => route('admin.blogs.blog_ctas.index')],
        ['label' => 'Edit CTA', 'url' => ''],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Edit CTA'" />
@endsection

@section('content')
<div class="py-6 min-h-screen">
    <form method="POST"
          action="{{ route('admin.blogs.blog_ctas.update', $cta->id) }}"
          class="form-ajax grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @method('PUT')

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">CTA Details</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="custom-label">Name <span class="text-red-600">*</span></label>
                        <input type="text" name="name" id="name" class="input-field" value="{{ old('name', $cta->name) }}" required>
                    </div>

                    <div>
                        <label class="custom-label">Slug</label>
                        <input type="text" name="slug" id="slug" class="input-field" value="{{ old('slug', $cta->slug) }}">
                        <p class="text-sm text-gray-500 mt-1">
                            Insert in blogs as <code class="bg-gray-100 px-1 rounded">{{ '{' . '{' . $cta->slug . '}' . '}' }}</code>
                        </p>
                    </div>

                    <div>
                        <label class="custom-label">Content</label>
                        <textarea id="cta-content-editor" editor="true" data-height="420" name="content" rows="12">{{ old('content', $cta->content) }}</textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Status</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="custom-label">Status</label>
                        <select name="status" class="input-field">
                            <option value="1" @selected(old('status', $cta->status ? '1' : '0') == '1')>Active</option>
                            <option value="0" @selected(old('status', $cta->status ? '1' : '0') == '0')>Inactive</option>
                        </select>
                    </div>
                    <x-button type="submit" class="btn-primary w-full" label="Update CTA" icon="" name="button" />
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const slugify = (value) => String(value || '').toLowerCase().trim()
        .replace(/\s+/g, '-').replace(/&/g, '-and-').replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-');
    const expectedSlug = () => slugify(nameInput.value);
    const syncSlug = () => {
        if (slugInput.dataset.touched === '1') return;
        slugInput.value = expectedSlug();
    };
    if (slugify(slugInput.value) !== '' && slugify(slugInput.value) !== expectedSlug()) {
        slugInput.dataset.touched = '1';
    } else {
        syncSlug();
    }
    nameInput.addEventListener('input', syncSlug);
    slugInput.addEventListener('input', function () {
        this.value = slugify(this.value);
        this.dataset.touched = '1';
    });
</script>
@endsection
