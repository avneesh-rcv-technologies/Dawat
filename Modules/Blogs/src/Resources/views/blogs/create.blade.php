@extends('backend::layouts.app')
@section('title', 'Create Blog')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Blogs', 'url' => route('admin.blogs.blogs.index')],
        ['label' => 'Create Blog', 'url' => ''],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Create Blog'" />
@endsection

@section('content')
<div class="py-6">
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <form action="{{ route('admin.blogs.blogs.store') }}" method="POST" enctype="multipart/form-data" class="form-ajax" id="blog-form">
            @csrf

            <div class="border-b border-gray-200 px-4">
                <nav class="flex flex-wrap gap-1 -mb-px" aria-label="Blog form tabs">
                    <button type="button"
                        class="blog-tab-btn active inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 border-[var(--color-primary-dark)] text-[var(--color-primary-dark)]"
                        data-tab="general">
                        <span class="material-icons-outlined text-base">settings</span>
                        General
                    </button>
                    <button type="button"
                        class="blog-tab-btn inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                        data-tab="content">
                        <span class="material-icons-outlined text-base">article</span>
                        Content
                    </button>
                    <button type="button"
                        class="blog-tab-btn inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                        data-tab="seo">
                        <span class="material-icons-outlined text-base">travel_explore</span>
                        SEO
                    </button>
                </nav>
            </div>

            <div class="p-6">
                {{-- General --}}
                <div id="general-tab" class="blog-tab-panel">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 ">
                        <div>
                            <label class="custom-label">Title <span class="text-red-500">*</span></label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}" class="input-field @error('title') border-red-500 @enderror" required>
                            @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="custom-label">Post URL <span class="text-red-500">*</span></label>
                            <input type="text" id="slug" name="post_url" value="{{ old('post_url') }}" class="input-field @error('post_url') border-red-500 @enderror" required>
                            @error('post_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-advanced-selector
                                name="categories"
                                table="blog_categories"
                                search-column="name"
                                value-column="id"
                                display-column="name"
                                :multiple="true"
                                :required="false"
                                :selected="old('categories', [])"
                                placeholder="Select categories..."
                                label="Categories"
                                :additional-columns="[]"
                                :filters="[['column' => 'status', 'operator' => '=', 'value' => 1]]"
                                order-by="name"
                                order-direction="asc"
                            />
                            @error('categories')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-advanced-selector
                                name="author_id"
                                table="blog_authors"
                                search-column="name"
                                value-column="id"
                                display-column="name"
                                :multiple="false"
                                :required="false"
                                :selected="old('author_id') ? [old('author_id')] : []"
                                placeholder="Select author..."
                                label="Author"
                                :additional-columns="[]"
                                :filters="[['column' => 'status', 'operator' => '=', 'value' => 1]]"
                                order-by="name"
                                order-direction="asc"
                            />
                            @error('author_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="custom-label">Status <span class="text-red-500">*</span></label>
                            <select name="status" id="blog-status" class="input-field @error('status') border-red-500 @enderror" required>
                                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="custom-label">Published Date</label>
                            <input type="date" name="published_at" value="{{ old('published_at', now()->format('Y-m-d')) }}" class="input-field @error('published_at') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Shown on the public blog pages. Defaults to today when published.</p>
                            @error('published_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="custom-label">Sort Order</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order') }}" min="1" max="2147483647" step="1" class="input-field @error('sort_order') border-red-500 @enderror">
                            @error('sort_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="schedule-fields" class="lg:col-span-2 grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label class="custom-label">Scheduled At</label>
                                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="input-field @error('scheduled_at') border-red-500 @enderror">
                                @error('scheduled_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="custom-label">Timezone</label>
                                <select name="scheduled_timezone" class="input-field @error('scheduled_timezone') border-red-500 @enderror">
                                    @foreach($timezones as $region => $regionTimezones)
                                        <optgroup label="{{ $region }}">
                                            @foreach($regionTimezones as $tzValue => $tzLabel)
                                                <option value="{{ $tzValue }}" @selected(old('scheduled_timezone', config('app.timezone', 'UTC')) === $tzValue)>
                                                    {{ $tzLabel }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('scheduled_timezone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div id="content-tab" class="blog-tab-panel hidden">
                    <div class="space-y-6">
                        <div>
                            <label class="custom-label">Short Description</label>
                            <textarea name="short_description" rows="3" class="input-field @error('short_description') border-red-500 @enderror">{{ old('short_description') }}</textarea>
                            @error('short_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                            <div class="xl:col-span-2">
                                <label class="custom-label">Long Description</label>
                                <textarea id="long-description-editor" editor="true" data-height="480" data-image-upload-url="{{ route('admin.blogs.blogs.content-image') }}" name="long_description" rows="8">{{ old('long_description') }}</textarea>
                                @include('blogs::blogs.partials.layout-tools')
                                @error('long_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-xs text-gray-500">Insert a CTA shortcode like <code class="bg-gray-100 px-1 rounded">@{{get-started-with-giftcards}}</code> — it will render on preview/frontend.</p>
                            </div>

                            <div>
                                <div class="rounded-lg border border-gray-200 bg-white sticky top-24">
                                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                        <h3 class="text-sm font-semibold text-gray-900">Available CTAs</h3>
                                        <p class="mt-1 text-xs text-gray-500">Click to insert into content</p>
                                    </div>
                                    <div class="p-3 max-h-[28rem] overflow-y-auto space-y-2">
                                        @forelse(($ctas ?? collect()) as $cta)
                                            <button type="button"
                                                class="w-full text-left rounded-lg border border-gray-200 px-3 py-2.5 hover:border-[var(--color-primary)] hover:bg-violet-50 transition"
                                                data-insert-cta="{{ $cta->slug }}"
                                                data-cta-label="{{ $cta->name }}">
                                                <span class="block text-sm font-medium text-gray-900" data-cta-name>{{ $cta->name }}</span>
                                                <code class="mt-1 block text-[11px] text-violet-700">{{ '{' . '{' . $cta->slug . '}' . '}' }}</code>
                                            </button>
                                        @empty
                                            <p class="text-sm text-gray-500 px-1 py-2">No active CTAs yet. Create one under Blog CTAs.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label class="custom-label">Inner Image</label>
                                @include('filemanager::components.file-uploader', [
                                    'object_type' => 'blog_inner_image',
                                    'object_id' => 0,
                                    'name' => 'inner_image',
                                    'isMultiple' => false,
                                ])
                            </div>
                            <div>
                                <label class="custom-label">Outer Image</label>
                                @include('filemanager::components.file-uploader', [
                                    'object_type' => 'blog_outer_image',
                                    'object_id' => 0,
                                    'name' => 'outer_image',
                                    'isMultiple' => false,
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEO --}}
                <div id="seo-tab" class="blog-tab-panel hidden">
                    <div class=" space-y-6">
                        <div>
                            <label class="custom-label">Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title') }}" maxlength="60" class="input-field @error('meta_title') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Recommended: 60 characters</p>
                            @error('meta_title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="custom-label">Meta Description</label>
                            <textarea name="meta_description" rows="3" maxlength="160" class="input-field @error('meta_description') border-red-500 @enderror">{{ old('meta_description') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Recommended: 160 characters</p>
                            @error('meta_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="custom-label">Meta Keywords</label>
                            <input type="text" name="meta_keywords" value="{{ old('meta_keywords') }}" class="input-field @error('meta_keywords') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Separate keywords with commas</p>
                            @error('meta_keywords')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="custom-label">Schema (JSON-LD)</label>
                            <textarea name="schema" rows="10" class="input-field font-mono text-sm @error('schema') border-red-500 @enderror" placeholder='{"@type":"BlogPosting","headline":"..."}'>{{ old('schema') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Optional custom JSON-LD schema for this blog. Must be valid JSON.</p>
                            @error('schema')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="sticky bottom-0 border-t border-gray-200 bg-white px-6 py-4 flex items-center justify-between gap-3">
                <p class="text-xs text-gray-500 hidden sm:block">Switch tabs above to fill General, Content, and SEO.</p>
                <div class="ml-auto flex items-center gap-3">
                    <a href="{{ route('admin.blogs.blogs.index') }}" class="btn-cancel">Cancel</a>
                    <x-button type="submit" class="btn-primary" label="Create" icon="" name="button" />
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const tabButtons = document.querySelectorAll('.blog-tab-btn');
        const tabPanels = document.querySelectorAll('.blog-tab-panel');

        tabButtons.forEach((button) => {
            button.addEventListener('click', function () {
                const tabName = this.dataset.tab;

                tabButtons.forEach((btn) => {
                    btn.classList.remove('active', 'border-[var(--color-primary-dark)]', 'text-[var(--color-primary-dark)]');
                    btn.classList.add('border-transparent', 'text-gray-500');
                });
                this.classList.add('active', 'border-[var(--color-primary-dark)]', 'text-[var(--color-primary-dark)]');
                this.classList.remove('border-transparent', 'text-gray-500');

                tabPanels.forEach((panel) => panel.classList.add('hidden'));
                document.getElementById(tabName + '-tab')?.classList.remove('hidden');
            });
        });

        const nameInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        const statusSelect = document.getElementById('blog-status');
        const scheduleFields = document.getElementById('schedule-fields');

        const toggleSchedule = () => {
            scheduleFields.style.display = statusSelect.value === 'scheduled' ? 'grid' : 'none';
        };
        statusSelect.addEventListener('change', toggleSchedule);
        toggleSchedule();

        const slugify = (value) => String(value || '')
            .toLowerCase().trim()
            .replace(/\s+/g, '-')
            .replace(/&/g, '-and-')
            .replace(/[^a-z0-9-]/g, '')
            .replace(/-+/g, '-');

        const expectedSlug = () => slugify(nameInput.value);
        const syncSlug = () => {
            if (slugInput.dataset.touched === '1') return;
            slugInput.value = expectedSlug();
        };

        const currentSlug = slugify(slugInput.value);
        if (currentSlug !== '' && currentSlug !== expectedSlug()) {
            slugInput.dataset.touched = '1';
        } else {
            syncSlug();
        }

        nameInput.addEventListener('input', syncSlug);
        slugInput.addEventListener('input', function () {
            this.value = slugify(this.value);
            slugInput.dataset.touched = '1';
        });
    })();
</script>
@include('blogs::blogs.partials.cta-chips-script')
@endsection
