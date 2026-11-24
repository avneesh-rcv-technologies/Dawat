<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Filemanager\Models\FileLink;
use Modules\Filemanager\Services\FileService;
use Modules\Restaurant\Models\Restaurant;

class RestaurantGalleryService
{
    public const MAX_IMAGES = 10;

    public function __construct(private FileService $files) {}

    public static function rules(bool $required = false): array
    {
        return [
            'images' => [$required ? 'required' : 'nullable', 'array', 'list', 'max:10'],
            'images.*' => ['bail', 'required', 'file', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp'],
            'remove_ids' => ['nullable', 'array', 'list'],
            'remove_ids.*' => ['required', 'integer', 'distinct'],
        ];
    }

    public static function messages(): array
    {
        return [
            'images.required' => 'Please select at least one gallery image.',
            'images.array' => 'Please select your gallery images as a list of files.',
            'images.list' => 'Please select your gallery images as a list of files.',
            'images.max' => 'A restaurant gallery can have at most 10 images.',
            'images.*.required' => 'Please select a valid gallery image.',
            'images.*.file' => 'Please select a valid gallery image file.',
            'images.*.image' => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            'images.*.mimes' => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            'images.*.extensions' => 'Only .jpg, .jpeg, .png, and .webp image files are allowed.',
            'images.*.max' => 'Each gallery image must be 2 MB or smaller.',
            'remove_ids.*.integer' => 'Please select a valid gallery image to delete.',
            'remove_ids.*.distinct' => 'Please select each image to delete only once.',
        ];
    }

    public function update(Restaurant $restaurant, array $images, array $removeIds = []): void
    {
        Validator::make(['images' => $images, 'remove_ids' => $removeIds], self::rules(), self::messages())->validate();

        DB::transaction(function () use ($restaurant, $images, $removeIds) {
            Restaurant::query()->lockForUpdate()->findOrFail($restaurant->id);
            $links = FileLink::with('file')->where('object_type', 'restaurant_gallery')
                ->where('object_id', $restaurant->id)->lockForUpdate()->get();
            $removals = $links->whereIn('id', $removeIds);
            if ($removals->count() !== count($removeIds)) {
                throw ValidationException::withMessages(['remove_ids' => 'One or more images could not be found in your restaurant gallery. Refresh the page and try again.']);
            }

            $existingCount = $links->count() - $removals->count();
            if ($images !== [] && $existingCount + count($images) > self::MAX_IMAGES) {
                $remaining = max(0, self::MAX_IMAGES - $existingCount);
                throw ValidationException::withMessages([
                    'images' => "A restaurant gallery can have at most 10 images. You already have {$existingCount} images and can add {$remaining} more. Delete an image before uploading more.",
                ]);
            }

            $position = ((int) ($links->max('position') ?? -1)) + 1;
            foreach ($images as $image) {
                $this->files->uploadFile($image, 'restaurant_gallery', $restaurant->id, 'A', $position++);
            }

            // Existing images are removed only when explicitly selected for deletion.
            foreach ($removals as $link) {
                $file = $link->file;
                $link->delete();
                if ($file && ! FileLink::where('file_id', $file->id)->exists()) {
                    $file->delete();
                    DB::afterCommit(function () use ($file) {
                        $disk = Storage::disk(fn_get_setting('general.image_driver') ?? 'public');
                        $paths = [$file->path.'/'.$file->file_name];
                        foreach (['thumbnails', 'medium', 'large'] as $size) {
                            $paths[] = $file->path.'/'.$size.'/'.$file->file_name;
                        }
                        $disk->delete($paths);
                    });
                }
            }

            DB::afterCommit(function () use ($restaurant) {
                $cache = app(RestaurantCacheService::class);
                $cache->forgetGallery($restaurant->id);
                $cache->forgetDetail($restaurant->id);
                $cache->forgetList();
            });
        });
    }
}
