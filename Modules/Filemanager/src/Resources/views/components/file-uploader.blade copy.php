@php
    $isMultiple = $isMultiple ?? false;
    $is_delete = $is_delete ?? true;
    $is_update = $is_update ?? true;
    $images = [];

    if (!empty($object_type)) {

        $images = fn_get_images($object_type, $object_id);
    }
    $input_name = $name ?? 'image';
    $store = $store ?? true;

@endphp

<div class="flex flex-col gap-6">
    <!-- Existing & New Previews -->
    <div id="previewContainer" class="preview-container flex flex-wrap gap-4 -{{$is_delete}}-">
        @if(!empty($images) && $store)
            @foreach($images as $img)
                <div class="relative {{ in_array($img['extension'], ['png', 'jpg', 'jpeg']) ? 'shadow-lg rounded-lg p-2' : '' }} w-[160px] h-full flex flex-col justify-between items-center bg-white shadow existing-image"
                    data-id="{{$img['id']}}">
                    <a href="{{$img['url']}}" target="_blank" download
                        class="h-[100px] w-full flex items-center justify-center">
                        @if(in_array($img['extension'], ['png', 'jpg', 'jpeg']))
                            <img src="{{$img['url']}}" class="h-full w-full object-contain rounded">
                        @else
                            <div class="text-center w-full">
                                <i class="fas fa-file text-6xl text-gray-500 mb-1"></i>
                                <p class="text-xs text-gray-600 truncate w-full"></p>
                            </div>
                        @endif
                    </a>
                    <!-- <img src="{{$img['url']}}" class="h-[100px] w-full object-contain rounded"> -->
                    @if($is_delete)
                        <input type="hidden" name="existing_ids[]" value="{{$img['id']}}">

                        @php
                            $deleteUrl = $delete_route ?? route('filemanager.delete', ['id' => '__id__']);
                            $deleteUrl = str_replace('__id__', $img['id'], $deleteUrl);
                        @endphp
                        <a onclick="openDeleteModal('{{ $deleteUrl }}', false)"
                            class="text-red-600 hover:text-red-300 mr-3 cursor-pointer" @if($is_delete) delete @endif>
                            <span class="material-icons-outlined">delete</span>

                        </a>
                        {{-- <input type="text" name="existing_alts[{{$img['id']}}]" value="{{$img['alt_text']}}"
                            class="mt-2 px-2 py-1 border border-gray-300 rounded text-sm w-full" @if($is_update) update @endif
                            placeholder="Alt text"> --}}
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    @if($is_update)
        <!-- File Input -->
        <div class="image-uploader btn-group flex space-x-2 mb-5 border border-gray-300 rounded-lg p-2">
            <input type="file" name="{{$input_name}}" id="imageInput" @if($isMultiple) multiple @endif
                class="max-w-full btn flex-1 py-2 px-4 bg-white text-gray-700 font-medium hover:bg-gray-50">

            {{-- <button type="button" class="btn flex-1 py-2 px-4 bg-white text-gray-700 font-medium hover:bg-gray-50">
                <!-- Filemanager -->
            </button> --}}

        </div>
    @endif
</div>


<script>
    // Define openDeleteModal if it doesn't exist
    if (typeof window.openDeleteModal === 'undefined') {
        window.openDeleteModal = function (deleteUrl, isNew = false) {
            if (confirm('Are you sure you want to delete this file?')) {
                fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);

                        if (data.success) {
                            // Reload the page to reflect changes
                            window.location.reload();

                            const fileElement = document.querySelector(`[data-id="${data.id}"]`);
                            if (fileElement) {
                                fileElement.remove();
                            }
                            // Show success message if showToast function exists
                            if (typeof showToast === 'function') {
                                showToast('success', data.message || 'File deleted successfully');
                            } else if (data.message) {

                            }
                        } else {
                            throw new Error(data.message || 'Failed to delete file');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (typeof showToast === 'function') {
                            showToast('error', error.message || 'Failed to delete file');
                        } else {
                            // alert('Error: ' + (error.message || 'Failed to delete file'));
                        }
                    });
            }
            return false; // Prevent default action
        };
    }
</script>