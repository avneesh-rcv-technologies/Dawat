@extends('backend::layouts.app')
@section('title', 'Edit Blog Author')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Blog Authors', 'url' => route('admin.blogs.blog_authors.index')],
        ['label' => 'Edit Author', 'url' => ''],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Edit Author'" />
@endsection

@section('content')
<div class="py-6 min-h-screen">
    <form method="POST"
          action="{{ route('admin.blogs.blog_authors.update', $author->id) }}"
          enctype="multipart/form-data"
          class="form-ajax grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @method('PUT')

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Author Details</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="custom-label">Name <span class="text-red-600">*</span></label>
                        <input type="text" name="name" id="name" class="input-field" value="{{ old('name', $author->name) }}" required>
                    </div>

                    <div>
                        <label class="custom-label">Slug</label>
                        <input type="text" name="slug" id="slug" class="input-field" value="{{ old('slug', $author->slug) }}">
                    </div>

                    <div>
                        <label class="custom-label">Designation</label>
                        <input type="text" name="designation" class="input-field" value="{{ old('designation', $author->designation) }}" placeholder="e.g. Senior Content Writer">
                    </div>

                    <div>
                        <label class="custom-label">Bio</label>
                        @php $bioValue = old('bio', strip_tags((string) $author->bio)); @endphp
                        <textarea name="bio" rows="6" maxlength="1000" class="input-field" placeholder="Short author biography (max 1000 characters)">{{ $bioValue }}</textarea>
                        <p class="mt-1 text-xs text-gray-500"><span data-bio-count>{{ strlen($bioValue) }}</span>/1000 characters</p>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="custom-label">Email</label>
                        <input type="email" name="email" class="input-field" value="{{ old('email', $author->email) }}">
                    </div>

                    <div>
                        <label class="custom-label">Avatar</label>
                        @include('filemanager::components.file-uploader', [
                            'object_type' => 'blog_author_avatar',
                            'object_id' => $author->id,
                            'name' => 'avatar',
                            'isMultiple' => false,
                        ])
                    </div>
                </div>
            </div>

            {{-- <div class="bg-white rounded-lg border border-gray-200">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Social Media Links</h3>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="custom-label">Twitter / X</label>
                        <input type="url" name="twitter_url" class="input-field" value="{{ old('twitter_url', $author->twitter_url) }}" placeholder="https://">
                    </div>
                    <div>
                        <label class="custom-label">LinkedIn</label>
                        <input type="url" name="linkedin_url" class="input-field" value="{{ old('linkedin_url', $author->linkedin_url) }}" placeholder="https://">
                    </div>
                    <div>
                        <label class="custom-label">Facebook</label>
                        <input type="url" name="facebook_url" class="input-field" value="{{ old('facebook_url', $author->facebook_url) }}" placeholder="https://">
                    </div>
                    <div>
                        <label class="custom-label">Instagram</label>
                        <input type="url" name="instagram_url" class="input-field" value="{{ old('instagram_url', $author->instagram_url) }}" placeholder="https://">
                    </div>
                    <div>
                        <label class="custom-label">YouTube</label>
                        <input type="url" name="youtube_url" class="input-field" value="{{ old('youtube_url', $author->youtube_url) }}" placeholder="https://">
                    </div>
                    <div>
                        <label class="custom-label">Website</label>
                        <input type="url" name="website_url" class="input-field" value="{{ old('website_url', $author->website_url) }}" placeholder="https://">
                    </div>
                </div>
            </div> --}}
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
                            <option value="1" @selected(old('status', $author->status ? '1' : '0') == '1')>Active</option>
                            <option value="0" @selected(old('status', $author->status ? '1' : '0') == '0')>Inactive</option>
                        </select>
                    </div>
                    <x-button type="submit" class="btn-primary w-full" label="Update Author" icon="" name="button" />
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

    const bioInput = document.querySelector('textarea[name="bio"]');
    const bioCount = document.querySelector('[data-bio-count]');
    if (bioInput && bioCount) {
        const updateBioCount = () => { bioCount.textContent = bioInput.value.length; };
        bioInput.addEventListener('input', updateBioCount);
        updateBioCount();
    }
</script>
@endsection
