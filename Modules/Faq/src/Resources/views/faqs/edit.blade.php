@extends('backend::layouts.app')
@section('title', 'Edit FAQ')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'FAQs', 'url' => route('admin.faqs.index')],
        ['label' => 'Edit FAQ', 'url' => ''],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Edit FAQ'" />
@endsection

@section('content')
<div class="w-full py-2">
    @if (session('status'))
    <div class="mb-4 p-4 border border-green-200 bg-green-50 text-green-700 rounded-lg">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
    <div class="mb-4 p-4 border border-red-200 bg-red-50 text-red-700 rounded-lg">
        <ul class="list-disc ml-6">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.faqs.update', $faq) }}" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        @csrf @method('PUT')

        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Edit FAQ</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Update this question or expand into a multi-FAQ group.</p>
                    </div>
                    <span class="text-xs font-medium text-gray-500 bg-white border border-gray-200 rounded-full px-3 py-1" id="faq-count-badge">1 question</span>
                </div>

                <div class="p-5 space-y-5">
                    <div>
                        <label class="custom-label">Group Name <span class="text-red-500">*</span></label>
                        <input type="text" name="group_name" value="{{ old('group_name', $faq->group_name) }}" class="input-field" required>
                    </div>

                    <div id="faq-entries" class="space-y-4">
                        <div class="faq-entry relative rounded-xl border border-gray-200 bg-gradient-to-b from-gray-50/80 to-white p-5 space-y-4">
                            <input type="hidden" name="faqs[0][id]" value="{{ $faq->id }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-white faq-number">1</span>
                                    <span class="text-sm font-semibold text-gray-800">Question</span>
                                </div>
                                <button type="button" class="remove-faq-btn hidden text-xs font-medium text-gray-400 hover:text-red-500 transition"
                                    onclick="this.closest('.faq-entry').remove(); renumberFaqs();">Remove</button>
                            </div>
                            <div>
                                <label class="custom-label">Question</label>
                                <input type="text" name="faqs[0][question]" value="{{ old('faqs.0.question', $faq->question) }}" class="input-field" required>
                            </div>
                            <div>
                                <label class="custom-label">Answer</label>
                                <textarea name="faqs[0][answer]" rows="4" class="input-field" required>{{ old('faqs.0.answer', $faq->answer) }}</textarea>
                            </div>
                            <div class="max-w-[140px]">
                                <label class="custom-label">Sort Order</label>
                                <input type="number" name="faqs[0][sort_order]" value="{{ old('faqs.0.sort_order', $faq->sort_order) }}" min="0" class="input-field">
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-faq-btn"
                        class="inline-flex items-center gap-2 rounded-lg border border-dashed border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 hover:border-gray-400 hover:bg-gray-50 transition w-full justify-center">
                        <span class="text-lg leading-none">+</span> Add Another FAQ
                    </button>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden p-5">
                <x-advanced-selector
                    name="blog_ids"
                    table="blogs"
                    search-column="title"
                    value-column="id"
                    display-column="title"
                    :multiple="true"
                    :required="false"
                    :selected="old('blog_ids', $selectedBlogIds)"
                    placeholder="Select blog posts..."
                    label="Map to Blog Posts"
                    :additional-columns="[]"
                    :filters="[]"
                    order-by="title"
                    order-direction="asc"
                />
            </div>

            <x-button type="submit" class="btn-primary w-full" label="Update FAQ" icon='' name="button" />
        </div>
    </form>

    <div class="mt-4 xl:max-w-sm xl:ml-auto">
        <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" data-confirm="This FAQ will be permanently removed." data-confirm-title="Delete this FAQ?" data-confirm-button="Delete" data-confirm-tone="danger">
            @csrf @method('DELETE')
            <button type="submit" class="w-full px-4 py-2.5 text-sm font-semibold text-gray-600 rounded-lg border border-gray-300 hover:bg-gray-50">
                Delete FAQ
            </button>
        </form>
    </div>
</div>
@include('faq::faqs.partials.scripts')
@endsection
