<section id="tab-filemanager" class="tab-panel">
    @php
        $selectedStorage = old('filemanager.storage_type', fn_get_setting('filemanager.storage_type', 'public'));
        $isS3 = $selectedStorage === 's3';
    @endphp

    <div class="px-6 py-6 space-y-6">
        <div class="border-b border-gray-200 pb-6">
            <h3 class="text-md font-semibold text-gray-800 mb-4">File Upload Configuration</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <label class="custom-label">File Storage Type</label>
                    <p class="mt-1 text-sm text-gray-500">Choose storage location for file uploads</p>
                </div>
                <div class="md:col-span-2">
                    <select name="settings[filemanager.storage_type]" id="fileStorageType" class="input-field">
                        <option value="public" {{ $selectedStorage === 'public' ? 'selected' : '' }}>Public Storage</option>
                        <option value="s3" {{ $selectedStorage === 's3' ? 'selected' : '' }}>AWS S3</option>
                    </select>
                    <p id="s3-server-hint" class="mt-2 text-sm text-gray-500 {{ $isS3 ? '' : 'hidden' }}">
                        S3 uses AWS credentials configured on the server environment. Only the bucket name is set here.
                    </p>
                </div>
            </div>

            <div class="mt-8 {{ $isS3 ? '' : 'hidden' }}" id="s3-settings">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">AWS Bucket</label>
                        <p class="mt-1 text-sm text-gray-500">S3 bucket name for file uploads</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[filesystems.disks.s3.bucket]" type="text"
                            value="{{ old('filesystems.disks.s3.bucket', fn_get_setting('filesystems.disks.s3.bucket', config('filesystems.disks.s3.bucket'))) }}"
                            class="input-field"
                            placeholder="Your S3 bucket name"
                            @disabled(!$isS3)>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden">
        <div class="px-6 py-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <label class="custom-label">File Manager Path</label>
                    <p class="mt-1 text-sm text-gray-500">Set the path for your file manager data store</p>
                </div>
                <div class="md:col-span-2">
                    <input name="settings[general.filemanager.path]" type="text"
                        value="{{ old('general.filemanager.path', fn_get_setting('general.filemanager.path')) }}"
                        class="input-field">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                <div class="md:col-span-1">
                    <label class="custom-label">Extensions</label>
                    <p class="mt-1 text-sm text-gray-500">Comma separated list of allowed file extensions</p>
                </div>
                <div class="md:col-span-2">
                    <input name="settings[general.filemanager.extensions]" type="text"
                        value="{{ old('general.filemanager.extensions', fn_get_setting('general.filemanager.extensions')) }}"
                        class="input-field">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label class="custom-label">Max File Size (MB)</label>
                    <p class="mt-1 text-sm text-gray-500">Maximum file size allowed for uploads in MB</p>
                </div>
                <div class="md:col-span-2">
                    <input name="settings[general.filemanager.max_file_size]" type="text"
                        value="{{ old('general.filemanager.max_file_size', fn_get_setting('general.filemanager.max_file_size')) }}"
                        class="input-field">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label class="custom-label">Default Image</label>
                    <p class="mt-1 text-sm text-gray-500">The default image to be used when no image is available</p>
                </div>
                <div class="md:col-span-2">
                    @include('filemanager::components.file-uploader', [
                        'object_type' => 'default_image',
                        'object_id' => 0,
                        'name' => 'default_image',
                    ])
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label class="custom-label">Image Generate Sizes</label>
                    <p class="mt-1 text-sm text-gray-500">The image sizes to be generated when an image is uploaded</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Width
                        <input name="settings[general.filemanager.image_thumbnail_width]" type="text"
                            value="{{ old('general.filemanager.image_thumbnail_width', fn_get_setting('general.filemanager.image_thumbnail_width')) }}"
                            class="input-field">
                    </label>

                    <label class="block text-sm font-medium text-gray-700 mb-1">Height
                        <input name="settings[general.filemanager.image_thumbnail_height]" type="text"
                            value="{{ old('general.filemanager.image_thumbnail_height', fn_get_setting('general.filemanager.image_thumbnail_height')) }}"
                            class="input-field">
                    </label>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const storageSelect = document.getElementById('fileStorageType');
        const s3Settings = document.getElementById('s3-settings');
        const s3Hint = document.getElementById('s3-server-hint');

        if (!storageSelect || !s3Settings) {
            return;
        }

        function syncStorageSections() {
            const isS3 = storageSelect.value === 's3';
            s3Settings.classList.toggle('hidden', !isS3);
            if (s3Hint) {
                s3Hint.classList.toggle('hidden', !isS3);
            }
            s3Settings.querySelectorAll('input, select, textarea').forEach(function (el) {
                el.disabled = !isS3;
            });
        }

        storageSelect.addEventListener('change', syncStorageSections);
        syncStorageSections();
    });
</script>
