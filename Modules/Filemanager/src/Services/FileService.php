<?php
// app/Services/FileService.php

namespace Modules\Filemanager\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Modules\Filemanager\Models\File;
use Illuminate\Support\Facades\Storage;
use Modules\Filemanager\Models\FileLink;
use Intervention\Image\Facades\Image;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class FileService
{
    protected $disk;

    public function __construct()
    {
        $this->disk = fn_get_setting('general.image_driver') ?? "public";
    }

    /**
     * Upload a file and create database records.
     */
    public function uploadFile(UploadedFile $uploadedFile, string $objectType, $objectId, string $type = 'A', int $position = 0): FileLink
    {
        // Generate unique file name
        $fileName = uniqid() . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
        $originalName = $uploadedFile->getClientOriginalName();
        $mimeType = $uploadedFile->getMimeType();
        $extension = $uploadedFile->getClientOriginalExtension();
        $size = $uploadedFile->getSize();
        $path = 'uploads/' . strtolower($objectType) . '/' . $objectId;

        // Store the file
        // $filePath = $uploadedFile->store($path, $fileName, $this->disk);

        Storage::disk($this->disk)->putFileAs($path, $uploadedFile, $fileName);
        // Check if it's an image
        $isImage = strpos($mimeType, 'image/') === 0;
        $width = null;
        $height = null;

        // Process image if it's an image
        if ($isImage) {
            try {
                $image = Image::make($uploadedFile);
                $width = $image->width();
                $height = $image->height();

                // Generate different image sizes
                // $this->generateImageSizes($image, $path, $fileName);
                $this->optimizeSingleImage($image, $path, $fileName);
            } catch (Exception $e) {
                // Log error but continue with file creation
                \Log::error('Image processing failed: ' . $e->getMessage());
            }
        }

        // Create file record
        $file = File::create([
            'file_name' => $fileName,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => $size,
            'path' => $path,
            'is_image' => $isImage,
            'width' => $width,
            'height' => $height,
        ]);

        // Create file link record
        $fileLink = FileLink::create([
            'file_id' => $file->id,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'type' => $type,
            'position' => $position,
        ]);

        return $fileLink->load('file');
    }

    /**
     * Generate different sizes for an image.
     */
    protected function generateImageSizes($image, string $path, string $fileName): void
    {
        $sizes = [
            'thumbnails' => [150, 150],
            'medium'     => [600, 600],
            'large'      => [1200, 1200],
        ];

        foreach ($sizes as $folder => $dimensions) {
            $resizedImage = clone $image;

            // Resize with aspect ratio
            $resizedImage->resize($dimensions[0], $dimensions[1], function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Save to storage
            $storagePath = $path . '/' . $folder . '/' . $fileName;
            Storage::disk($this->disk)->put($storagePath, $resizedImage->encode());
        }
    }

    protected function optimizeSingleImage($image, string $path, string $fileName): void
    {
        try {

            $maxWidth = 800;
            $maxHeight = 800;

            // Resize image if larger than max dimensions
            if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
                $image->resize($maxWidth, $maxHeight, function ($constraint) {
                    $constraint->aspectRatio();  // Maintain aspect ratio (no distortion)
                    $constraint->upsize();       // Don't enlarge small images
                });
            }

            // Apply sharpening to prevent blur after resize
            // Value range: 0-100 (10 is subtle, good for most images)
            $image->sharpen(10);

            $quality = 85;
            $encodedImage = $image->encode('jpg', $quality);

            // Save optimized image (replaces original)
            $storagePath = $path . '/' . $fileName;
            Storage::disk($this->disk)->put($storagePath, $encodedImage);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
        }
    }


    /**
     * Delete a file and its database records.
     */
    public function deleteFile(string $objectType, $objectId): bool
    {
        try {
            $fileLinks = FileLink::with('file')
                ->where('object_type', $objectType)
                ->where('object_id', $objectId)
                ->get();

            foreach ($fileLinks as $fileLink) {
                // Delete the physical files
                $this->deletePhysicalFiles($fileLink->file);

                // Delete file link
                $fileLink->delete();

                // Delete file record
                $fileLink->file->delete();
            }

            return true;
        } catch (Exception $e) {
            \Log::error('File deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a file and its database records.
     */
    public function deleteFileWithId($file_id): bool
    {
        try {
            $fileLinks = FileLink::with('file')
                ->where('id', $file_id)
                ->get();

            foreach ($fileLinks as $fileLink) {
                // Delete the physical files
                $this->deletePhysicalFiles($fileLink->file);

                // Delete file link
                $fileLink->delete();

                // Delete file record
                $fileLink->file->delete();
            }

            return true;
        } catch (Exception $e) {
            \Log::error('File deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete physical files from storage.
     */
    protected function deletePhysicalFiles(File $file): void
    {
        // Delete main file
        Storage::disk($this->disk)->delete($file->path . '/' . $file->file_name);

        // Delete resized images if it's an image
        if ($file->is_image) {
            $sizes = ['thumbnails', 'medium', 'large'];
            foreach ($sizes as $size) {
                Storage::disk($this->disk)->delete($file->path . '/' . $size . '/' . $file->file_name);
            }
        }
    }

    /**
     * Get files for a specific object with image details.
     */
    public function getFiles(string $objectType, $objectId, string $type = null)
    {
        $query = FileLink::with('file')
            ->where('object_type', $objectType)
            ->where('object_id', $objectId)
            ->orderBy('position');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get()->map(function ($fileLink) {
            return $this->formatFileData($fileLink->file, $fileLink);
        });
    }

    /**
     * Get the main file for an object with image details.
     */
    public function getFile(string $objectType, $objectId)
    {
        $fileLink = FileLink::with('file')
            ->where('object_type', $objectType)
            ->where('object_id', $objectId)
            ->first();

        if (!$fileLink) {
            return ['url' => $this->getDefaultImage()['url']];
        }
        return $this->formatFileData($fileLink->file, $fileLink);
    }

    public function getDefaultImage()
    {
        return ['url' => '/dawat.png'];
    }

    /**
     * Get file by ID.
     */
    public function getFileById(int $fileId)
    {
        $file = File::find($fileId);
        if (!$file) {
            return null;
        }

        $fileLink = FileLink::where('file_id', $fileId)->first();

        return $this->formatFileData($file, $fileLink);
    }

    /**
     * Format file data with all image details.
     */
    protected function formatFileData(File $file, FileLink $fileLink = null): array
    {
        $baseData = [
            'id' => $file->id,
            'file_name' => $file->file_name,
            'original_name' => $file->original_name,
            'alt_text'  => $file->alt_text ?? $file->original_name,
            'mime_type' => $file->mime_type,
            'extension' => $file->extension,
            'size' => $file->size,
            'formatted_size' => $this->formatBytes($file->size),
            'path' => $file->path,
            'url' => $this->getFileUrl($file->path . '/' . $file->file_name),
            'is_image' => $file->is_image,
            'created_at' => $file->created_at,
            'updated_at' => $file->updated_at,
        ];

        // Add file link data if available
        if ($fileLink) {
            $baseData['object_type'] = $fileLink->object_type;
            $baseData['object_id'] = $fileLink->object_id;
            $baseData['type'] = $fileLink->type;
            $baseData['position'] = $fileLink->position;
            $baseData['file_link_id'] = $fileLink->id;
        }

        // Add image-specific data
        if ($file->is_image) {
            $baseData['width'] = $file->width;
            $baseData['height'] = $file->height;
            $baseData['dimensions'] = $file->width . 'x' . $file->height;
            $baseData['aspect_ratio'] = $file->height > 0 ? $file->width / $file->height : 0;

            // Add resized image URLs
            $baseData['thumbnail_url'] = $this->getFileUrl($file->path . '/thumbnails/' . $file->file_name);
            $baseData['medium_url'] = $this->getFileUrl($file->path . '/medium/' . $file->file_name);
            $baseData['large_url'] = $this->getFileUrl($file->path . '/large/' . $file->file_name);
        }

        return $baseData;
    }

    /**
     * Get file URL from storage.
     */
    protected function getFileUrl(string $path): string
    {
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->url($path);
        }

        return $this->getDefaultImage()['url'];
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Move file to different object.
     */
    public function moveFile(int $fileId, string $newObjectType, $newObjectId): bool
    {
        try {
            $fileLink = FileLink::where('file_id', $fileId)->first();

            if ($fileLink) {
                $fileLink->update([
                    'object_type' => $newObjectType,
                    'object_id' => $newObjectId,
                ]);

                return true;
            }

            return false;
        } catch (Exception $e) {
            \Log::error('File move failed: ' . $e->getMessage());
            return false;
        }
    }
}
