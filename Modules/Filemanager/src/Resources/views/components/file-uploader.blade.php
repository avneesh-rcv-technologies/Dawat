@php
    $isMultiple = $isMultiple ?? $is_multiple ?? false;
    $is_delete = $is_delete ?? true;
    $is_update = $is_update ?? true;
    $images = [];
    $unique_id = md5($object_type . '-' . $object_id);

    if (!empty($object_type)) {

        $images = fn_get_images($object_type, $object_id);
    }
    $input_name = $name ?? 'image';
    $store = $store ?? true;
    $accept = $accept ?? 'image/*';
    $span_text = $span_text ?? 'Drag and drop your files here or click to select';
    $max_size = $max_size ?? '2MB';
    $max_size_bytes = null;
    $max_size_label = null;

    if (!empty($max_size)) {
        $normalized_size = strtolower(trim((string) $max_size));

        if (is_numeric($normalized_size)) {
            $max_size_bytes = (int) $normalized_size;
        } else {
            if (str_ends_with($normalized_size, 'kb')) {
                $max_size_bytes = (float) rtrim($normalized_size, 'kb') * 1024;
            } elseif (str_ends_with($normalized_size, 'mb')) {
                $max_size_bytes = (float) rtrim($normalized_size, 'mb') * 1024 * 1024;
            } elseif (str_ends_with($normalized_size, 'gb')) {
                $max_size_bytes = (float) rtrim($normalized_size, 'gb') * 1024 * 1024 * 1024;
            } elseif (str_ends_with($normalized_size, 'b')) {
                $max_size_bytes = (float) rtrim($normalized_size, 'b');
            }
        }

        if (!empty($max_size_bytes)) {
            $max_size_bytes = (int) round($max_size_bytes);
        }

        $max_size_label = strtoupper((string) $max_size);
    }

    $allowed_types = null;
    if (!empty($accept) && $accept !== '*/*') {
        $accepted_rules = array_filter(array_map('trim', explode(',', $accept)));
        $allowed_labels = [];

        foreach ($accepted_rules as $rule) {
            $normalized = strtolower(trim($rule));

            if ($normalized === 'image/*') {
                $allowed_labels[] = 'Images';
                continue;
            }

            if ($normalized === 'image/jpeg' || $normalized === '.jpeg' || $normalized === '.jpg') {
                $allowed_labels[] = 'JPEG';
                continue;
            }

            if ($normalized === 'image/png' || $normalized === '.png') {
                $allowed_labels[] = 'PNG';
                continue;
            }

            if ($normalized === 'image/webp' || $normalized === '.webp') {
                $allowed_labels[] = 'WEBP';
                continue;
            }

            if (str_starts_with($normalized, 'image/')) {
                $allowed_labels[] = strtoupper(str_replace('image/', '', $normalized));
                continue;
            }

            if (str_starts_with($normalized, '.')) {
                $allowed_labels[] = strtoupper(substr($normalized, 1));
                continue;
            }

            $allowed_labels[] = strtoupper($normalized);
        }

        $allowed_types = implode(', ', array_values(array_unique($allowed_labels)));
    }
@endphp

<div class="file-uploader flex flex-col gap-6">
    @if($is_update)
        <!-- File Input Area -->
        <div class="image-uploader border-2 border-dashed border-blue-300 rounded-xl p-8 bg-gradient-to-br from-blue-50 to-indigo-50 hover:border-blue-500 transition-all duration-300 cursor-pointer"
            id="uploadArea-{{$unique_id}}">
            <input type="file" name="{{$input_name}}" id="imageInput-{{$unique_id}}" @if($isMultiple) multiple @endif
                class="hidden" accept="{{ $accept }}" data-max-size="{{ $max_size_bytes ?? '' }}">
            
            <div class="flex flex-col items-center justify-center text-center">
                <div class="mb-4 p-3 bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Upload files</h3>
                <p class="text-sm mb-4 {{ !empty($accept) && $accept !== '*/*' ? 'text-black-600 font-medium' : 'text-gray-600' }}">
                    {{ $span_text }}
                    @if(!empty($max_size_label))
                        <span class="block mt-2 text-xs text-gray-500">Maximum file size: {{ $max_size_label }}</span>
                    @endif
                    @if(!empty($allowed_types))
                        <span class="block mt-1 text-xs text-gray-500">Accepted formats: {{ $allowed_types }}</span>
                    @endif
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
                <div class="relative group existing-image rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300"
                    data-id="{{$img['id']}}">
                    <a href="{{$img['url']}}" target="_blank" download class="block h-[150px] w-full bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if(in_array($img['extension'], ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg']))
                            <img src="{{$img['url']}}" class="h-full w-full object-cover hover:scale-110 transition-transform duration-300">
                        @else
                            <div class="text-center w-full flex flex-col items-center justify-center h-full bg-gradient-to-br from-gray-200 to-gray-300">
                                <span class="material-icons-outlined text-4xl text-gray-600 mb-2">description</span>
                                <p class="text-xs text-gray-700 font-medium">{{ strtoupper($img['extension']) }}</p>
                            </div>
                        @endif
                    </a>

                    @if($is_delete)
                        <input type="hidden" name="existing_ids[]" value="{{$img['id']}}">
                        @php
                            $deleteUrl = $delete_route ?? route('filemanager.delete', ['id' => '__id__']);
                            $deleteUrl = str_replace('__id__', $img['id'], $deleteUrl);
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

<style>
    .file-uploader { --uploader-ink: #103647; --uploader-muted: #627785; --uploader-accent: #00b978; }
    .file-uploader .image-uploader {
        position: relative;
        min-height: 208px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 28px;
        border: 2px dashed #c7d5dd;
        border-radius: 16px;
        background: linear-gradient(145deg, #f8fbfd 0%, #f1f7fa 100%);
        box-shadow: 0 2px 8px rgba(16, 54, 71, .04);
        transition: border-color .18s ease, background .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .file-uploader .image-uploader:hover,
    .file-uploader .image-uploader:focus-within {
        border-color: var(--uploader-accent);
        background: linear-gradient(145deg, #f5fffa 0%, #edf9f4 100%);
        box-shadow: 0 0 0 4px rgba(0, 185, 120, .09);
    }
    .file-uploader .image-uploader:active { transform: scale(.998); }
    .file-uploader .image-uploader > div { max-width: 520px; }
    .file-uploader .image-uploader > div > div {
        width: 56px;
        height: 56px;
        display: grid;
        place-items: center;
        margin: 0 auto 14px;
        padding: 0;
        color: #008a5d;
        background: #dcf8ed;
    }
    .file-uploader .image-uploader h3 { color: var(--uploader-ink); font-size: 17px; margin-bottom: 6px; }
    .file-uploader .image-uploader p { margin: 0; color: var(--uploader-muted); line-height: 1.55; }
    .file-uploader .image-uploader p span { color: var(--uploader-muted); }
    .file-uploader [id^="uploadProgress-"] { padding: 12px 16px; border: 1px solid #e1e9ee; border-radius: 12px; background: #fff; }
    .file-uploader .preview-container,
    .file-uploader > div[id^="newPreviewsContainer-"] { gap: 14px; }
    .file-uploader .existing-image,
    .file-uploader #newPreviewsContainer-{{ $unique_id }} > div {
        min-width: 0;
        border: 1px solid #dce5ea;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(16, 54, 71, .07);
    }
    .file-uploader .existing-image > a { height: 176px; background: #f4f7f8; }
    .file-uploader .existing-image > a img { transition: transform .2s ease; }
    .file-uploader .existing-image:hover,
    .file-uploader #newPreviewsContainer-{{ $unique_id }} > div:hover {
        border-color: #b6cbd4;
        box-shadow: 0 8px 20px rgba(16, 54, 71, .12);
        transform: translateY(-2px);
    }
    .file-uploader .existing-image > div { background: rgba(16, 54, 71, .32); }
    .file-uploader .existing-image button,
    .file-uploader #newPreviewsContainer-{{ $unique_id }} button {
        width: 38px;
        height: 38px;
        border: 1px solid rgba(255,255,255,.75);
        box-shadow: 0 3px 10px rgba(0,0,0,.18);
    }
    .file-uploader .existing-image button:focus-visible,
    .file-uploader #newPreviewsContainer-{{ $unique_id }} button:focus-visible { outline: 3px solid #fff; outline-offset: 3px; }
    @media (hover: none) {
        .file-uploader .existing-image > div { background: transparent; align-items: flex-start; justify-content: flex-end; padding: 8px; }
        .file-uploader .existing-image button,
        .file-uploader #newPreviewsContainer-{{ $unique_id }} button { display: flex !important; }
    }
    @media (max-width: 640px) {
        .file-uploader .image-uploader { min-height: 180px; padding: 22px 16px; }
        .file-uploader .existing-image > a { height: 140px; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea-{{ $unique_id }}');
    const imageInput = document.getElementById('imageInput-{{ $unique_id }}');
    const previewContainer = document.getElementById('newPreviewsContainer-{{ $unique_id }}');
    const uploadProgress = document.getElementById('uploadProgress-{{ $unique_id }}');

    if (!uploadArea) return;

    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.add('border-blue-500', 'bg-blue-100');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.remove('border-blue-500', 'bg-blue-100');
        });
    });

    uploadArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        imageInput.files = files;
        handleFiles(files);
    });

    uploadArea.addEventListener('click', () => imageInput.click());

    imageInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    function isAcceptedFile(file) {
        const acceptAttr = (imageInput.getAttribute('accept') || '').trim();

        if (!acceptAttr || acceptAttr === '*/*') {
            return true;
        }

        const acceptedValues = acceptAttr.split(',').map(v => v.trim().toLowerCase());
        const fileType = (file.type || '').toLowerCase();
        const fileExt = '.' + (file.name.split('.').pop() || '').toLowerCase();

        return acceptedValues.some((rule) => {
            if (!rule) return false;

            if (rule === 'image/*') {
                return fileType.startsWith('image/');
            }

            if (rule.endsWith('/*')) {
                const baseType = rule.slice(0, -1);
                return fileType.startsWith(baseType);
            }

            if (rule.startsWith('.')) {
                return fileExt === rule.toLowerCase();
            }

            return fileType === rule || fileExt === rule;
        });
    }

    function formatFileSize(bytes) {
        if (!bytes || bytes <= 0) return '0 Bytes';
        const units = ['Bytes', 'KB', 'MB', 'GB'];
        const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        const size = bytes / (1024 ** index);
        return `${size.toFixed(size >= 10 || index === 0 ? 0 : 1)} ${units[index]}`;
    }

    function handleFiles(files) {
        const acceptAttr = (imageInput.getAttribute('accept') || '').trim();
        const maxSizeBytes = Number(imageInput.dataset.maxSize || 0);
        const selectedFiles = Array.from(files || []);
        const invalidFiles = selectedFiles.filter(file => acceptAttr && !isAcceptedFile(file));
        const oversizedFiles = selectedFiles.filter(file => maxSizeBytes > 0 && file.size > maxSizeBytes);

        if (invalidFiles.length > 0) {
            const invalidNames = invalidFiles.map(file => file.name).slice(0, 3).join(', ');
            const extra = invalidFiles.length > 3 ? '...' : '';
            const invalidMessage = 'Invalid file type. Please upload only: ' + acceptAttr;

            if (typeof showToast === 'function') {
                showToast(invalidMessage, 'error', 'Upload Error', 5000);
            } else {
                alert(invalidMessage);
            }

            imageInput.value = '';
            previewContainer.innerHTML = '';
            return;
        }

        if (oversizedFiles.length > 0) {
            const oversizeName = oversizedFiles.map(file => `${file.name} (${formatFileSize(file.size)})`).slice(0, 3).join(', ');
            const extra = oversizedFiles.length > 3 ? '...' : '';
            const maxSizeLabel = formatFileSize(maxSizeBytes);
            const invalidMessage = `Allowed size ${maxSizeLabel} limit.`;

            if (typeof showToast === 'function') {
                showToast(invalidMessage, 'error', 'Upload Error', 5000);
            } else {
                alert(invalidMessage);
            }

            imageInput.value = '';
            previewContainer.innerHTML = '';
            return;
        }

        previewContainer.innerHTML = '';
        let loadedCount = 0;

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = (e) => {
                loadedCount++;
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative group rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300';
                
                const isImage = file.type.startsWith('image/');

                if (isImage) {
                    previewDiv.className = 'relative group rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 bg-white';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'h-[150px] w-full object-contain bg-gray-50 hover:scale-105 transition-transform duration-300';
                    
                    const badge = document.createElement('div');
                    badge.className = 'absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium shadow-lg';
                    badge.textContent = 'New';
                    
                    const overlay = document.createElement('div');
                    overlay.className = 'absolute inset-0 bg-black/10 bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300 flex items-center justify-center';
                    
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.className = 'hidden group-hover:flex items-center justify-center w-10 h-10 bg-red-600 hover:bg-red-700 rounded-full text-white transition-all duration-300';
                    deleteBtn.innerHTML = '<span class="material-icons-outlined text-lg">delete</span>';
                    deleteBtn.onclick = function() { this.parentElement.parentElement.remove(); };
                    
                    overlay.appendChild(deleteBtn);
                    previewDiv.appendChild(img);
                    previewDiv.appendChild(badge);
                    previewDiv.appendChild(overlay);
                } else {
                    const fileExt = file.name.split('.').pop().toUpperCase();
                    previewDiv.className = 'relative group rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 bg-white';
                    
                    const fileContent = document.createElement('div');
                    fileContent.className = 'h-[150px] w-full bg-gradient-to-br from-gray-100 to-gray-200 flex flex-col items-center justify-center';
                    fileContent.innerHTML = `
                        <span class="material-icons-outlined text-5xl text-gray-500 mb-2">description</span>
                        <p class="text-xs text-gray-700 font-semibold">${fileExt}</p>
                        <p class="text-xs text-gray-600 mt-2 text-center px-2 truncate w-full">${file.name}</p>
                    `;
                    
                    const badge = document.createElement('div');
                    badge.className = 'absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium shadow-lg';
                    badge.textContent = 'New';
                    
                    const overlay = document.createElement('div');
                    overlay.className = 'absolute inset-0 bg-black/10 bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300 flex items-center justify-center';
                    
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.className = 'hidden group-hover:flex items-center justify-center w-10 h-10 bg-red-600 hover:bg-red-700 rounded-full text-white transition-all duration-300';
                    deleteBtn.innerHTML = '<span class="material-icons-outlined text-lg">delete</span>';
                    deleteBtn.onclick = function() { this.parentElement.parentElement.remove(); };
                    
                    overlay.appendChild(deleteBtn);
                    previewDiv.appendChild(fileContent);
                    previewDiv.appendChild(badge);
                    previewDiv.appendChild(overlay);
                }

                previewContainer.appendChild(previewDiv);

                // Show progress
                if (loadedCount === files.length) {
                    uploadProgress.classList.add('hidden');
                }
            };

            reader.readAsDataURL(file);
        });
    }
});
</script>
