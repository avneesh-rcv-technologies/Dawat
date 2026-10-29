@php
    $fileService = app(\Modules\Filemanager\Services\FileService::class);
    $galleryImages = $restaurantId ? $fileService->getFiles('restaurant_gallery', $restaurantId) : [];
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Restaurant Gallery</h3>
        <p class="text-sm text-gray-600">Upload images that will be displayed on the restaurant detail page in the app</p>
    </div>

    <form method="POST" action="{{ route('admin.restaurant.gallery.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="restaurant_id" value="{{ $restaurantId }}">
        
        @include('filemanager::components.file-uploader', [
            'object_type' => 'restaurant_gallery',
            'object_id' => $restaurantId ?? 0,
            'name' => 'images[]',
            'isMultiple' => true,
            'store' => true,
            'accept' => 'image/jpeg,image/png',
            'span_text'=>'Drag and drop images here or click to select files. (JPEG, PNG)',
            'existingFiles' => $galleryImages,
            'delete_route' => route('admin.restaurant.gallery.destroy', ['id' => '__id__']),
        ])

        <div class="flex justify-end mt-6">
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Gallery Images
            </button>
        </div>
    </form>
</div>

