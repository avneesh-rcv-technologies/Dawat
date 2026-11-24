<?php

namespace Modules\Restaurant\Services;

use App\Services\CacheService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Filemanager\Models\File;
use Modules\Filemanager\Models\FileLink;
use Modules\Filemanager\Services\FileService;
use Modules\FoodManagements\Services\FoodImageUploadPolicy;
use Modules\Restaurant\Models\Restaurant;
use Throwable;

class RestaurantFoodItemImageService
{
    public const MAX_IMAGES = FoodImageUploadPolicy::MAX_IMAGES;

    public function __construct(private RestaurantFoodItemV2Service $items, private FileService $files) {}

    public function gallery(Restaurant $restaurant, int $itemId): array
    {
        $this->items->find($restaurant, $itemId);

        return $this->galleryData($itemId);
    }

    public function save(Restaurant $restaurant, int $itemId, array $images, bool $replaceAll = false, ?int $imageId = null): array
    {
        FoodImageUploadPolicy::validateFiles($images, 'images');
        $disk = fn_get_setting('general.image_driver') ?? 'public';
        $newPaths = [];
        $committed = false;
        try {
            return DB::transaction(function () use ($restaurant, $itemId, $images, $replaceAll, $imageId, $disk, &$newPaths, &$committed) {
                $item = $this->lockItem($restaurant, $itemId);
                DB::afterCommit(function () use (&$committed) { $committed = true; });
                $links = $this->links($itemId)->lockForUpdate()->get();
                $target = $imageId === null ? null : $links->firstWhere('id', $imageId);
                abort_if($imageId !== null && ! $target, 404, 'Image not found for this food item.');
                FoodImageUploadPolicy::assertCount($replaceAll ? 0 : $links->count() - ($target ? 1 : 0), count($images));
                $position = $replaceAll ? 0 : ((int) ($links->max('position') ?? -1)) + 1;
                foreach ($images as $image) {
                    $file = $this->storeFile($image, $itemId, $disk, $newPaths, 'food_image');
                    if ($target) {
                        $oldFile = $target->file;
                        // Preserve the image ID and gallery position when replacing one image.
                        $target->update(['file_id' => $file->id]);
                        $this->removeUnlinkedFile($oldFile, $disk);
                    } else {
                        FileLink::create([
                            'file_id' => $file->id, 'object_type' => 'food_image', 'object_id' => $itemId,
                            'type' => 'A', 'position' => $position++,
                        ]);
                    }
                }
                if ($replaceAll) {
                    foreach ($links as $link) {
                        $oldFile = $link->file;
                        $link->delete();
                        $this->removeUnlinkedFile($oldFile, $disk);
                    }
                }
                $item->touch();
                $this->forgetAfterCommit($itemId);

                return $this->galleryData($itemId);
            });
        } catch (Throwable $exception) {
            // A failed batch must not leave uploaded files or replace existing gallery files.
            if (! $committed) {
                $this->deletePaths($disk, $newPaths);
            }
            throw $exception;
        }
    }

    public function saveSingle(Restaurant $restaurant, int $itemId, UploadedFile $image): array
    {
        FoodImageUploadPolicy::validateFiles([$image], 'image');
        $disk = fn_get_setting('general.image_driver') ?? 'public';
        $newPaths = [];
        $committed = false;

        try {
            return DB::transaction(function () use ($restaurant, $itemId, $image, $disk, &$newPaths, &$committed) {
                $item = $this->lockItem($restaurant, $itemId);
                DB::afterCommit(function () use (&$committed) { $committed = true; });

                $existingLinks = FileLink::with('file')->where('object_type', 'food_image_single')->where('object_id', $itemId)->lockForUpdate()->get();
                $file = $this->storeFile($image, $itemId, $disk, $newPaths, 'food_image_single');

                foreach ($existingLinks as $link) {
                    $oldFile = $link->file;
                    $link->delete();
                    $this->removeUnlinkedFile($oldFile, $disk);
                }

                FileLink::create([
                    'file_id' => $file->id,
                    'object_type' => 'food_image_single',
                    'object_id' => $itemId,
                    'type' => 'A',
                    'position' => 0,
                ]);

                $item->touch();
                $this->forgetAfterCommit($itemId);

                return $this->singleImageData($itemId);
            });
        } catch (Throwable $exception) {
            if (! $committed) {
                $this->deletePaths($disk, $newPaths);
            }
            throw $exception;
        }
    }

    public function delete(Restaurant $restaurant, int $itemId, array $imageIds): array
    {
        return DB::transaction(function () use ($restaurant, $itemId, $imageIds) {
            $item = $this->lockItem($restaurant, $itemId);
            $links = $this->links($itemId)->whereIn('id', $imageIds)->lockForUpdate()->get();
            abort_unless($links->count() === count($imageIds), 404, 'One or more images were not found for this food item.');
            $disk = fn_get_setting('general.image_driver') ?? 'public';
            foreach ($links as $link) {
                $file = $link->file;
                $link->delete();
                $this->removeUnlinkedFile($file, $disk);
            }
            $item->touch();
            $this->forgetAfterCommit($itemId);

            return $this->galleryData($itemId);
        });
    }

    private function lockItem(Restaurant $restaurant, int $itemId)
    {
        Restaurant::whereKey($restaurant->id)->where('admin_id', $restaurant->admin_id)
            ->whereIn('status', ['draft', 'active'])->lockForUpdate()->firstOrFail();

        return $this->items->find($restaurant, $itemId, true);
    }

    private function links(int $itemId)
    {
        return FileLink::with('file')->where('object_type', 'food_image')->where('object_id', $itemId)
            ->orderBy('position')->orderBy('id');
    }

    private function galleryData(int $itemId): array
    {
        $images = collect($this->files->getFiles('food_image', $itemId))->map(fn ($image) => [
            'id' => $image['file_link_id'], 'url' => $image['url'],
            'original_name' => $image['original_name'], 'position' => (int) $image['position'],
        ])->values()->all();

        return ['food_item_id' => $itemId, 'images' => $images, 'count' => count($images), 'max_images' => self::MAX_IMAGES];
    }

    private function singleImageData(int $itemId): array
    {
        $image = collect($this->files->getFiles('food_image_single', $itemId))->map(fn ($entry) => [
            'id' => $entry['file_link_id'],
            'url' => $entry['url'],
            'original_name' => $entry['original_name'],
            'position' => (int) $entry['position'],
            'object_type' => 'food_image_single',
        ])->first();

        return ['food_item_id' => $itemId, 'object_type' => 'food_image_single', 'image' => $image];
    }

    private function storeFile(UploadedFile $image, int $itemId, string $disk, array &$newPaths, string $objectType = 'food_image'): File
    {
        $directory = 'uploads/'.$objectType.'/'.$itemId;
        $path = $directory.'/'.$image->hashName();
        $newPaths[] = $path;
        if (! Storage::disk($disk)->putFileAs($directory, $image, basename($path))) {
            throw new \RuntimeException('Unable to store the food image.');
        }
        $dimensions = getimagesize($image->getRealPath());

        return File::create([
            'file_name' => basename($path), 'original_name' => Str::substr(basename($image->getClientOriginalName()), 0, 120),
            'mime_type' => $image->getMimeType(), 'extension' => pathinfo($path, PATHINFO_EXTENSION),
            'size' => $image->getSize(), 'path' => dirname($path), 'is_image' => true,
            'width' => $dimensions[0] ?? null, 'height' => $dimensions[1] ?? null,
        ]);
    }

    private function removeUnlinkedFile(?File $file, string $disk): void
    {
        if (! $file || $file->fileLinks()->exists()) {
            return;
        }
        $paths = [$file->path.'/'.$file->file_name];
        foreach (['thumbnails', 'medium', 'large'] as $size) {
            $paths[] = $file->path.'/'.$size.'/'.$file->file_name;
        }
        $file->delete();
        // Retain old physical files until the database mutation has successfully committed.
        DB::afterCommit(fn () => $this->deletePaths($disk, $paths));
    }

    private function deletePaths(string $disk, array $paths): void
    {
        if (! $paths) {
            return;
        }
        try {
            if (! Storage::disk($disk)->delete($paths)) {
                Log::warning('Could not delete food item image files.', ['disk' => $disk, 'paths' => $paths]);
            }
        } catch (Throwable $exception) {
            Log::warning('Could not delete food item image files.', ['disk' => $disk, 'error' => $exception->getMessage()]);
        }
    }

    private function forgetAfterCommit(int $itemId): void
    {
        DB::afterCommit(function () use ($itemId) {
            try {
                CacheService::forgetFoodItem($itemId);
            } catch (Throwable $exception) {
                Log::warning('Could not clear food item image cache.', ['food_item_id' => $itemId, 'error' => $exception->getMessage()]);
            }
        });
    }
}
