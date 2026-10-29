@extends('backend::layouts.app')

@section('page_title', 'Gallery Management')

@section('breadcrumbs')
    @php
        $title = 'Gallery Management';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => $title, 'url' => null]
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
<div class="mx-auto">
    {{-- Success/Error Messages --}}
    {{-- @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif --}}

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Gallery Management --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Admin Gallery Images</h3>
            <p class="text-sm text-gray-600">Upload images that will be displayed on the app home screen</p>
        </div>

        <form method="POST" action="{{ route('gallery.store') }}" enctype="multipart/form-data">
            @csrf
            
            @include('filemanager::components.file-uploader', [
                'object_type' => 'admin_gallery',
                'object_id' => 1,
                'name' => 'images[]',
                'isMultiple' => true,
                'store' => true,
                'accept' => 'image/jpeg,image/png',
                'span_text'=>'Drag and drop images here or click to select files. (JPEG, PNG) allowed 10 images only',
                'existingFiles' => $galleryImages ?? [],
            ])

            <div class="flex justify-end mt-6">
                <button type="submit" class="btn btn-primary">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
