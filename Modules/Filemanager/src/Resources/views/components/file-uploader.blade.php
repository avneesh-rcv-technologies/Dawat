@php
    $isMultiple = $isMultiple ?? false;
    $is_delete = $is_delete ?? true;
    $is_update = $is_update ?? true;
    $use_file_link_ids = $use_file_link_ids ?? false;
    $images = [];
    $unique_id = md5($object_type . '-' . $object_id);

    if (!empty($object_type)) {

        $images = fn_get_images($object_type, $object_id);
    }
    $input_name = $name ?? 'image';
    $store = $store ?? true;

@endphp

<div class="flex flex-col gap-8">
    @if($is_update)
        <!-- File Input Area -->
        <div class="image-uploader relative z-10 border-2 border-dashed border-blue-300 rounded-xl p-8 bg-gradient-to-br from-blue-50 to-indigo-50 hover:border-blue-500 transition-all duration-300 cursor-pointer"
            id="uploadArea-{{$unique_id}}">
            <input type="file" name="{{$input_name}}" id="imageInput-{{$unique_id}}" @if($isMultiple) multiple @endif
                class="hidden" accept="image/*,.pdf,.doc,.docx">
            
            <div class="flex flex-col items-center justify-center text-center">
                <div class="mb-4 p-3 bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Upload Files</h3>
                <p class="text-sm text-gray-600 mb-1">Drag and drop your files here or click to select</p>
                <p class="text-xs text-gray-500">
                    Allowed: {{ strtoupper(implode(', ', fn_allowed_image_mimes())) }}.
                    Max {{ fn_max_upload_megabytes() }}MB.
                </p>
            </div>
        </div>

        <!-- Upload Progress -->
        <div id="uploadProgress-{{$unique_id}}" class="hidden">
            <div class="flex items-center gap-3">
                <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div id="progressBar-{{$unique_id}}" class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <span id="progressText-{{$unique_id}}" class="text-sm font-medium text-gray-700">0%</span>
            </div>
        </div>
    @endif

    <!-- Existing & New Previews -->
    <div id="previewContainer-{{$unique_id}}" class="preview-container grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @if(!empty($images) && $store)
            @foreach($images as $img)
                @php
                    $imageId = $use_file_link_ids ? ($img['file_link_id'] ?? $img['id']) : $img['id'];
                @endphp
                <div class="relative group existing-image rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300"
                    data-id="{{$imageId}}">
                    <a href="{{$img['url']}}" target="_blank" download class="block h-[150px] w-full bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if(in_array($img['extension'], ['png', 'jpg', 'jpeg','webp','gif','bmp','svg']))
                            <img src="{{$img['url']}}" class="h-full w-full object-cover hover:scale-110 transition-transform duration-300">
                        @else
                            <div class="text-center w-full flex flex-col items-center justify-center h-full bg-gradient-to-br from-gray-200 to-gray-300">
                                <span class="material-icons-outlined text-4xl text-gray-600 mb-2">description</span>
                                <p class="text-xs text-gray-700 font-medium">{{ strtoupper($img['extension']) }}</p>
                            </div>
                        @endif
                    </a>

                    @if($is_delete)
                        <input type="hidden" name="existing_ids[]" value="{{$imageId}}">
                        @php
                            $deleteUrl = $delete_route ?? route('filemanager.delete', ['id' => '__id__']);
                            $deleteUrl = str_replace('__id__', $imageId, $deleteUrl);
                        @endphp
                        <div class="absolute inset-0 bg-black/10 bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300 flex items-center justify-center">
                            <button type="button" onclick="openDeleteModal('{{ $deleteUrl }}', false)" 
                                class="hidden group-hover:flex items-center justify-center w-10 h-10 bg-red-600 hover:bg-red-700 rounded-full text-white transition-all duration-300">
                                <span class="material-icons-outlined text-lg">delete</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

        <!-- New Preview Items (populated via JavaScript) -->
      
    </div>
      <div id="newPreviewsContainer-{{$unique_id}}" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"></div>
</div>

<script>
(function () {
    const uploadArea = document.getElementById('uploadArea-{{ $unique_id }}');
    const imageInput = document.getElementById('imageInput-{{ $unique_id }}');
    const previewContainer = document.getElementById('newPreviewsContainer-{{ $unique_id }}');
    const uploadProgress = document.getElementById('uploadProgress-{{ $unique_id }}');

    if (!uploadArea || !imageInput || uploadArea.dataset.uploaderInit === '1') {
        return;
    }

    uploadArea.dataset.uploaderInit = '1';

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function (eventName) {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(function (eventName) {
        uploadArea.addEventListener(eventName, function () {
            uploadArea.classList.add('border-blue-500', 'bg-blue-100');
        });
    });

    ['dragleave', 'drop'].forEach(function (eventName) {
        uploadArea.addEventListener(eventName, function () {
            uploadArea.classList.remove('border-blue-500', 'bg-blue-100');
        });
    });

    uploadArea.addEventListener('drop', function (e) {
        const files = e.dataTransfer.files;
        if (typeof DataTransfer !== 'undefined') {
            const transfer = new DataTransfer();
            Array.from(files).forEach(function (file) {
                transfer.items.add(file);
            });
            imageInput.files = transfer.files;
        }
        handleFiles(imageInput.files.length ? imageInput.files : files);
    });

    uploadArea.addEventListener('click', function () {
        imageInput.click();
    });

    imageInput.addEventListener('change', function (e) {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        if (!previewContainer) {
            return;
        }

        previewContainer.innerHTML = '';
        let loadedCount = 0;

        Array.from(files).forEach(function (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                loadedCount++;
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative group rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 bg-white';
                const isImage = file.type.startsWith('image/');

                if (isImage) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'h-[150px] w-full object-contain bg-gray-50 hover:scale-105 transition-transform duration-300';

                    const badge = document.createElement('div');
                    badge.className = 'absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium shadow-lg';
                    badge.textContent = 'New';

                    previewDiv.appendChild(img);
                    previewDiv.appendChild(badge);
                } else {
                    const fileExt = file.name.split('.').pop().toUpperCase();
                    previewDiv.innerHTML = '<div class="h-[150px] w-full bg-gradient-to-br from-gray-100 to-gray-200 flex flex-col items-center justify-center"><span class="material-icons-outlined text-5xl text-gray-500 mb-2">description</span><p class="text-xs text-gray-700 font-semibold">' + fileExt + '</p></div>';
                }

                previewContainer.appendChild(previewDiv);

                if (uploadProgress && loadedCount === files.length) {
                    uploadProgress.classList.add('hidden');
                }
            };

            reader.readAsDataURL(file);
        });
    }
})();
</script>