<?php

namespace Modules\Filemanager\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Modules\Filemanager\Contracts\FileStorageDriverInterface;
use Modules\Filemanager\Exceptions\FileServiceException;
use Modules\Filemanager\Models\File;
use Modules\Filemanager\Models\FileLink;
use Nette\Utils\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService
{
    public function __construct(
        protected PublicFileStorageService $publicStorageService,
        protected S3FileStorageService $s3StorageService,
        protected FileRecordService $fileRecordService,
        protected FileSecurityScanner $securityScanner,
    ) {
    }

    public function uploadFile(
        UploadedFile $uploadedFile,
        string $objectType,
        $objectId,
        string $type = 'A',
        int $position = 0,
        ?string $customPath = null
    ): FileLink {
        try {
            $this->validateUpload($uploadedFile);
            $storage = $this->storage();
            $fileName = $this->generateFileName($uploadedFile);

            $path = $this->resolveDirectoryPath($objectType, $objectId, $customPath);

            $metadata = $this->storeUploadedFile($storage, $uploadedFile, $path, $fileName);
          
            return $this->fileRecordService->createFileWithLink(
                array_merge($metadata, [
                    'driver' => $storage->getDriver(),
                    'file_name' => $fileName,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'path' => $path,
                ]),
                [
                    'object_type' => $objectType,
                    'object_id' => $objectId,
                    'type' => $type,
                    'position' => $position,
                ]
            );
        } catch (InvalidArgumentException $e) {
            Log::warning('File upload validation failed: ' . $e->getMessage(), $this->uploadLogContext(
                $uploadedFile,
                $objectType,
                $objectId,
                $path ?? null,
                $fileName ?? null
            ));

            throw new FileServiceException($e->getMessage(), 0, $e);
        } catch (\Throwable $e) {
            // Catch \Throwable (not just Exception) so PHP Errors such as a missing
            // GD function or class are logged and returned as a clean failure
            // instead of surfacing as a 500 Internal Server Error.
            Log::error('File upload failed: ' . $e->getMessage(), $this->uploadLogContext(
                $uploadedFile,
                $objectType,
                $objectId,
                $path ?? null,
                $fileName ?? null,
                $e
            ));

            throw new FileServiceException('File upload failed. Please try again.', 0, $e);
        }
    }

    /**
     * Context written to the log for a failed upload: the file, where it was going,
     * the server's image/upload capabilities, and the exception details.
     */
    protected function uploadLogContext(
        UploadedFile $uploadedFile,
        string $objectType,
        $objectId,
        ?string $path = null,
        ?string $fileName = null,
        ?\Throwable $e = null
    ): array {
        $gdInfo = function_exists('gd_info') ? gd_info() : [];

        $context = [
            'original_name' => $uploadedFile->getClientOriginalName(),
            'client_mime' => $uploadedFile->getClientMimeType(),
            'extension' => $uploadedFile->getClientOriginalExtension(),
            'size' => $uploadedFile->getSize(),
            'upload_error' => $uploadedFile->getError(),
            'object_type' => $objectType,
            'object_id' => $objectId,
            'driver' => $this->configuredDriver(),
            'target_path' => $path,
            'file_name' => $fileName,
            'storage_root' => config('filesystems.disks.public.root'),
            'storage_root_writable' => is_writable((string) config('filesystems.disks.public.root')),
            'temp_dir_writable' => is_writable(storage_path('app')),
            'gd_loaded' => extension_loaded('gd'),
            'gd_webp' => $gdInfo['WebP Support'] ?? false,
            'gd_jpeg' => $gdInfo['JPEG Support'] ?? false,
            'gd_png' => $gdInfo['PNG Support'] ?? false,
            'fileinfo_loaded' => extension_loaded('fileinfo'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'user_id' => auth()->id(),
        ];

        if ($e !== null) {
            $context['exception'] = get_class($e);
            $context['exception_file'] = $e->getFile() . ':' . $e->getLine();
            $context['trace'] = $e->getTraceAsString();
        }

        return $context;
    }

    /**
     * Validate an upload without storing it. Lets callers check a replacement
     * file before deleting the existing one.
     *
     * @throws FileServiceException
     */
    public function assertValidUpload(UploadedFile $uploadedFile): void
    {
        try {
            $this->validateUpload($uploadedFile);
        } catch (InvalidArgumentException $e) {
            Log::warning('File upload validation failed: ' . $e->getMessage());

            throw new FileServiceException($e->getMessage(), 0, $e);
        }
    }

    public function deleteFile(string $objectType, $objectId): bool
    {
        try {
            $fileLinks = $this->fileRecordService->getObjectFileLinks($objectType, $objectId);

            foreach ($fileLinks as $fileLink) {
                $this->deleteStoredFile($fileLink);
            }

            return true;
        } catch (Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());

            return false;
        }
    }

    public function deleteFileWithId($fileLinkId): bool
    {
        try {
            $fileLink = $this->fileRecordService->getFileLinkByLinkId((int) $fileLinkId);

            if (!$fileLink) {
                return false;
            }

            $this->deleteStoredFile($fileLink);

            return true;
        } catch (Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());

            return false;
        }
    }

    public function deleteFileLinkAfterCommit(FileLink $fileLink): void
    {
        $file = $fileLink->file;
        $this->fileRecordService->deleteFileLinkAndFile($fileLink);
        if (! $file || $file->fileLinks()->exists()) {
            return;
        }

        // Remove the database link now, retaining the old bytes if an outer update rolls back.
        \Illuminate\Support\Facades\DB::afterCommit(function () use ($file) {
            try {
                $this->deletePhysicalFiles($file);
            } catch (\Throwable $exception) {
                Log::warning('Could not remove replaced image files.', ['file_id' => $file->id]);
            }
        });
    }

    public function userCanDeleteFileLink(FileLink $fileLink, object $user, string $guard): bool
    {
        if ($guard === 'admin') {
            return true;
        }

        $objectId = (int) $fileLink->object_id;
        $objectType = (string) $fileLink->object_type;
        $userId = (int) $user->id;

        if ($objectId === $userId) {
            $expectedPrefix = match ($guard) {
                'client' => 'client_',
                'member' => 'member_',
                default => null,
            };

            if ($expectedPrefix === null || str_starts_with($objectType, $expectedPrefix)) {
                return true;
            }
        }

        if ($guard === 'member' && isset($user->client_id) && $objectId === (int) $user->client_id) {
            return str_starts_with($objectType, 'client_');
        }

        if ($objectType === Client::class && $guard === 'client') {
            return $objectId === $userId;
        }

        return false;
    }

    public function deleteFileById(int $fileId): bool
    {
        try {
            $file = $this->fileRecordService->getFileById($fileId);

            if (!$file) {
                return false;
            }

            $this->deletePhysicalFiles($file);
            $this->fileRecordService->deleteFileAndLinks($file);

            return true;
        } catch (Exception $e) {
            Log::error('File deletion by file id failed: ' . $e->getMessage());

            return false;
        }
    }

    public function createFolder(string $folderPath): array
    {
        $cleanPath = trim(str_replace('..', '', $folderPath), '/');

        if ($cleanPath === '') {
            throw new InvalidArgumentException('Folder path is required.');
        }

        $storage = $this->storage();
        $fullPath = trim($this->configuredBasePath(), '/') . '/' . $cleanPath;

        $storage->makeDirectory($fullPath);

        return [
            'driver' => $storage->getDriver(),
            'path' => $fullPath,
            'url' => $storage->url($fullPath),
        ];
    }

    public function updateFileById(int $fileId, array $payload = [], ?UploadedFile $uploadedFile = null): ?array
    {
        $file = $this->fileRecordService->getFileById($fileId);

        if (!$file) {
            return null;
        }

        if ($uploadedFile instanceof UploadedFile) {
            $this->replaceFileContent($file, $uploadedFile);
            $file->refresh();
        }

        $fileUpdates = array_filter([
            'original_name' => $payload['original_name'] ?? null,
        ], static fn($value) => $value !== null);

        if ($fileUpdates !== []) {
            $this->fileRecordService->updateFile($file, $fileUpdates);
        }

        $fileLink = $this->fileRecordService->getFileLinkByFileId($file->id);

        if ($fileLink) {
            $fileLinkUpdates = [];

            if (isset($payload['object_type'])) {
                $fileLinkUpdates['object_type'] = (string) $payload['object_type'];
            }

            if (isset($payload['object_id'])) {
                $fileLinkUpdates['object_id'] = $payload['object_id'];
            }

            if (isset($payload['type']) && in_array($payload['type'], ['M', 'A'], true)) {
                $fileLinkUpdates['type'] = $payload['type'];
            }

            if (isset($payload['position'])) {
                $fileLinkUpdates['position'] = (int) $payload['position'];
            }

            if ($fileLinkUpdates !== []) {
                $this->fileRecordService->updateFileLink($fileLink, $fileLinkUpdates);
            }
        }

        return $this->formatFileData($file->fresh(), $fileLink?->fresh());
    }

    public function getFiles(string $objectType, $objectId, string $type = null)
    {
        return $this->fileRecordService
            ->getObjectFileLinks($objectType, $objectId, $type)
            ->map(fn(FileLink $fileLink) => $this->formatFileData($fileLink->file, $fileLink));
    }

    public function getFile(string $objectType, $objectId): array
    {
        $fileLink = $this->fileRecordService->getObjectFileLink($objectType, $objectId);

        if (!$fileLink) {
            return $objectType === 'default_image'
                ? ['url' => $this->fallbackDefaultImageUrl()]
                : $this->getDefaultImage();
        }

        return $this->formatFileData($fileLink->file, $fileLink);
    }

    public function getDefaultImage(): array
    {
        $fileLink = $this->fileRecordService->getObjectFileLink('default_image', 0);

        if (!$fileLink) {
            return ['url' => $this->fallbackDefaultImageUrl()];
        }

        return $this->formatFileData($fileLink->file, $fileLink);
    }

    public function getFileById(int $fileId): ?array
    {
        $file = $this->fileRecordService->getFileById($fileId);

        if (!$file) {
            return null;
        }

        return $this->formatFileData($file, $this->fileRecordService->getFileLinkByFileId($fileId));
    }

    public function getStoredFileUrl(File $file, ?string $variant = null): string
    {
        $fileLink = $this->fileRecordService->getFileLinkByFileId((int) $file->id);
        if ($fileLink && $fileLink->object_type === 'admin_avatar') {
            return route('filemanager.private-image', [
                'file' => $file->id,
                'variant' => $variant ?: 'original',
            ]);
        }

        $relativePath = $file->path . '/' . ltrim($variant ? $variant . '/' . $file->file_name : $file->file_name, '/');
        $driver = $this->resolveStoredDriver($file);

        try {
            return $this->storage($driver)->url($relativePath);
        } catch (Exception $e) {
            Log::warning('File URL generation failed for driver ' . $driver . ': ' . $e->getMessage());
        }

        // Never recurse back into getStoredFileUrl — that caused IMDS timeout loops.
        return $this->fallbackDefaultImageUrl();
    }

    public function getPrivateImageContents(int $fileId, string $variant = 'original'): array
    {
        $file = $this->fileRecordService->getFileById($fileId);
        $fileLink = $this->fileRecordService->getFileLinkByFileId($fileId);

        if (!$file || !$file->is_image || !$fileLink || $fileLink->object_type !== 'admin_avatar') {
            abort(404);
        }

        $authenticatedId = auth('admin')->id();
        if (!$authenticatedId) {
            abort(404);
        }

        $fileLink = $this->fileRecordService->getPrivateAvatarLink(
            $fileId,
            'admin_avatar',
            (int) $authenticatedId
        );
        if (!$fileLink) {
            abort(404);
        }

        $relativePath = $file->path . '/' . ltrim(
            $variant === 'original' ? $file->file_name : $variant . '/' . $file->file_name,
            '/'
        );
        $storage = $this->storage($this->resolveStoredDriver($file));

        if (!$storage->exists($relativePath)) {
            abort(404);
        }

        return [
            'contents' => $this->readStoredFile($file, $relativePath),
            'mime_type' => $file->mime_type ?: 'application/octet-stream',
            'file_name' => $file->file_name,
            'etag' => sha1($file->id . ':' . $file->updated_at?->timestamp . ':' . $variant),
        ];
    }

    protected function readStoredFile(File $file, string $path): string
    {
        if ($this->resolveStoredDriver($file) === 's3') {
            return (string) \Illuminate\Support\Facades\Storage::disk('s3')->get($path);
        }

        return (string) \Illuminate\Support\Facades\Storage::disk('public')->get($path);
    }

    protected function validateUpload(UploadedFile $uploadedFile): void
    {
        if (! $uploadedFile->isValid()) {
            $uploadMax = ini_get('upload_max_filesize');
            $postMax = ini_get('post_max_size');

            throw new InvalidArgumentException(
                "File failed to upload. It may exceed the PHP upload limit "
                . "(upload_max_filesize={$uploadMax}, post_max_size={$postMax})."
            );
        }

        if (substr_count($uploadedFile->getClientOriginalName(), '.') > 1) {
            Log::warning('Filemanager: malicious upload rejected', [
                'reason' => 'double extension',
                'original_name' => $uploadedFile->getClientOriginalName(),
            ]);

            throw new InvalidArgumentException(FileSecurityScanner::MALICIOUS_MESSAGE);
        }

        $maxFileSize = $this->configuredMaxFileSize();
        $fileSizeMb = $uploadedFile->getSize() / 1024 / 1024;

        if ($fileSizeMb > $maxFileSize) {
            throw new InvalidArgumentException(
                "File exceeds maximum allowed size ({$fileSizeMb} MB). Maximum size is {$maxFileSize} MB."
            );
        }

        $allowedExtensions = $this->configuredExtensions();
        $extension = $this->normalizeExtension((string) $uploadedFile->getClientOriginalExtension());

        if ($allowedExtensions === []) {
            throw new InvalidArgumentException('File uploads are disabled. Please configure allowed file extensions in the admin panel.');
        }

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new InvalidArgumentException('File extension is not allowed. Only ' . implode(', ', $allowedExtensions) . ' are allowed.');
        }

        // Validates the client Content-Type header, the MIME detected from the
        // file bytes, and scans image metadata / trailing data / SVG markup for
        // embedded code. Throws InvalidArgumentException on any mismatch.
        $this->securityScanner->assertSafe($uploadedFile, $extension);
    }

    protected function storeUploadedFile(
        FileStorageDriverInterface $storage,
        UploadedFile $uploadedFile,
        string $path,
        string $fileName
    ): array {
        $mimeType = (string) $uploadedFile->getMimeType();
        $isImage = str_starts_with($mimeType, 'image/');

        $metadata = [
            'mime_type' => $mimeType,
            'extension' => $this->normalizeExtension((string) $uploadedFile->getClientOriginalExtension()),
            'size' => $uploadedFile->getSize(),
            'is_image' => $isImage,
            'width' => null,
            'height' => null,
        ];


        if (!$isImage || $metadata['extension'] === 'svg') {
            $storage->put(
    		$path . '/' . $fileName,
    		file_get_contents($uploadedFile->getRealPath())
	    );

            return $metadata;
        }

        $image = Image::fromFile($uploadedFile->getRealPath());
       
        $metadata['width'] = $image->getWidth();
        $metadata['height'] = $image->getHeight();

        $storage->makeDirectory($path);
        $storage->put($path . '/' . $fileName, $this->renderImageContents($image, 90, $metadata['extension']));

        // The original is already stored, so resize the same image in place instead of
        // cloning it: a clone holds a second full-size bitmap (~48MB for a 12MP photo),
        // which exhausts memory_limit for large phone photos. Sizes are shrink-only,
        // so each smaller variant is derived from the previous (larger-or-equal) one.
        $sizes = $this->imageSizes();
        uasort($sizes, static fn(array $a, array $b) => ($b[0] * $b[1]) <=> ($a[0] * $a[1]));

        foreach ($sizes as $folder => $dimensions) {
            $image->resize($dimensions[0], $dimensions[1], Image::FIT | Image::SHRINK_ONLY);
            $storage->makeDirectory($path . '/' . $folder);
            $storage->put(
                $path . '/' . $folder . '/' . $fileName,
                $this->renderImageContents($image, 85, $metadata['extension'])
            );
        }

        unset($image);

        return $metadata;
    }

    protected function replaceFileContent(File $file, UploadedFile $uploadedFile): void
    {
        $this->validateUpload($uploadedFile);

        $storage = $this->storage($this->resolveStoredDriver($file));

        $this->deletePhysicalFiles($file);

        $fileName = $this->generateFileName($uploadedFile);
        $metadata = $this->storeUploadedFile($storage, $uploadedFile, $file->path, $fileName);

        $this->fileRecordService->updateFile($file, array_merge($metadata, [
            'driver' => $storage->getDriver(),
            'file_name' => $fileName,
            'original_name' => $uploadedFile->getClientOriginalName(),
        ]));
    }

    protected function deleteStoredFile(FileLink $fileLink): void
    {
        if (!$fileLink->file) {
            $fileLink->delete();

            return;
        }

        $this->deletePhysicalFiles($fileLink->file);
        $this->fileRecordService->deleteFileLinkAndFile($fileLink);
    }

    protected function deletePhysicalFiles(File $file): void
    {
        $storage = $this->storage($this->resolveStoredDriver($file));

        $paths = [$file->path . '/' . $file->file_name];

        if ($file->is_image) {
            $legacySizes = ['thumbnails', 'medium', 'large'];
            $configuredSizes = array_keys($this->imageSizes());

            foreach (array_unique(array_merge($legacySizes, $configuredSizes)) as $size) {
                $paths[] = $file->path . '/' . $size . '/' . $file->file_name;
            }
        }

        $storage->delete($paths);
    }

    protected function formatFileData(File $file, FileLink $fileLink = null): array
    {
        $baseData = [
            'id' => $file->id,
            'driver' => $this->resolveStoredDriver($file),
            'file_name' => $file->file_name,
            'original_name' => $file->original_name,
            'alt_text' => $file->alt_text ?? $file->original_name,
            'mime_type' => $file->mime_type,
            'extension' => $file->extension,
            'size' => $file->size,
            'formatted_size' => $this->formatBytes($file->size),
            'path' => $file->path,
            'url' => $this->getStoredFileUrl($file),
            'is_image' => (bool) $file->is_image,
            'created_at' => $file->created_at,
            'updated_at' => $file->updated_at,
        ];

        if ($fileLink) {
            $baseData['object_type'] = $fileLink->object_type;
            $baseData['object_id'] = $fileLink->object_id;
            $baseData['type'] = $fileLink->type;
            $baseData['position'] = $fileLink->position;
            $baseData['file_link_id'] = $fileLink->id;
        }

        if ($file->is_image) {
            $baseData['width'] = $file->width;
            $baseData['height'] = $file->height;
            $baseData['dimensions'] = $file->height ? $file->width . 'x' . $file->height : null;
            $baseData['aspect_ratio'] = $file->height > 0 ? $file->width / $file->height : 0;
            $baseData['thumbnail_url'] = $this->getStoredFileUrl($file, 'thumbnails');
            $baseData['medium_url'] = $this->getStoredFileUrl($file, 'medium');
            $baseData['large_url'] = $this->getStoredFileUrl($file, 'large');
        }

        return $baseData;
    }

    protected function storage(?string $driver = null): FileStorageDriverInterface
    {
        return match ($driver ?? $this->configuredDriver()) {
            's3' => $this->s3StorageService,
            default => $this->publicStorageService,
        };
    }

    protected function configuredDriver(): string
    {
        $driver = fn_get_setting('filemanager.storage_type')
            ?? fn_get_setting('general.filemanager.driver')
            ?? 'public';

        return in_array($driver, ['public', 's3'], true) ? $driver : 'public';
    }

    protected function resolveStoredDriver(File $file): string
    {
        $driver = $file->driver ?: $this->configuredDriver();

        return in_array($driver, ['public', 's3'], true) ? $driver : 'public';
    }

    protected function configuredBasePath(): string
    {
        return trim((string) (fn_get_setting('general.filemanager.path') ?? 'uploads'), '/');
    }

    protected function configuredExtensions(): array
    {
        $raw = (string) fn_get_setting('general.filemanager.extensions', '');


        $extensions = array_values(array_filter(array_map(
            fn($value) => $this->normalizeExtension($value),
            explode(',', $raw)
        )));

        if (in_array('jpg', $extensions, true) && !in_array('jpeg', $extensions, true)) {
            $extensions[] = 'jpeg';
        }

        if (in_array('jpeg', $extensions, true) && !in_array('jpg', $extensions, true)) {
            $extensions[] = 'jpg';
        }

        return array_values(array_unique($extensions));
    }

    protected function configuredMaxFileSize(): int
    {
        return max(1, (int) (fn_get_setting('general.filemanager.max_file_size') ?? 10));
    }

    protected function resolveDirectoryPath(string $objectType, $objectId, ?string $customPath = null): string
    {
        if ($customPath !== null && trim($customPath) !== '') {
            return trim(str_replace('..', '', $customPath), '/');
        }

        return trim($this->configuredBasePath() . '/' . strtolower($objectType) . '/' . $objectId, '/');
    }

    protected function generateFileName(UploadedFile $uploadedFile): string
    {
        return uniqid() . '_' . time() . '.' . $this->normalizeExtension((string) $uploadedFile->getClientOriginalExtension());
    }

    protected function imageSizes(): array
    {
        $width = (int) (fn_get_setting('general.filemanager.image_thumbnail_width') ?? 0);
        $height = (int) (fn_get_setting('general.filemanager.image_thumbnail_height') ?? 0);

        if ($width <= 0 || $height <= 0) {
            return [];
        }

        return [
            'thumbnails' => [$width, $height],
        ];
    }

    protected function renderImageContents(Image $image, int $quality, string $extension): string
    {
        $tempPath = storage_path('app/' . uniqid('filemanager_', true) . '.' . trim($extension, '.'));

        $image->save($tempPath, $quality);

        try {
            return (string) file_get_contents($tempPath);
        } finally {
            @unlink($tempPath);
        }
    }

    protected function fallbackDefaultImageUrl(): string
    {
        return asset('avatar.png');
    }

    protected function getFallbackImageUrl(?File $file = null): string
    {
        if ($file && $this->isDefaultImageFile($file)) {
            return $this->fallbackDefaultImageUrl();
        }

        $defaultImage = $this->getDefaultImage();

        return $defaultImage['url'] ?? $this->fallbackDefaultImageUrl();
    }

    protected function isDefaultImageFile(File $file): bool
    {
        return FileLink::where('file_id', $file->id)
            ->where('object_type', 'default_image')
            ->where('object_id', 0)
            ->exists();
    }

    protected function normalizeExtension(string $extension): string
    {
        $normalized = strtolower(trim($extension, " \t\n\r\0\x0B."));

        return $normalized === 'jpeg' ? 'jpg' : $normalized;
    }

    protected function resolveStoredDriverFallback(File $file): string
    {
        $resolvedDriver = $this->resolveStoredDriver($file);

        return $resolvedDriver === 's3' ? 'public' : 's3';
    }

    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
