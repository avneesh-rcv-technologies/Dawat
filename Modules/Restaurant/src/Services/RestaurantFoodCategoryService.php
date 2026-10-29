<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Modules\FoodManagements\Models\FoodCategory;

class RestaurantFoodCategoryService
{
    public function listForRestaurant(int $restaurantId, array $filters = [])
    {
        $query = FoodCategory::query()
            ->with(['parent', 'children'])
            ->where('restaurant_id', $restaurantId)
            ->orderBy(
                $filters['sort_by'] ?? 'name',
                $filters['sort_dir'] ?? 'asc'
            );

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $status = strtolower((string) $filters['status']);

            $query->where(
                'status',
                $status === 'active' || $status === '1' || $status === 'true'
                ? 1
                : 0
            );
        }

        if (!empty($filters['food_type'])) {
            $query->where('food_type', $filters['food_type']);
        }

        if (array_key_exists('parent_id', $filters) && $filters['parent_id'] !== '') {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (isset($filters['include_inactive']) && $filters['include_inactive'] === '1') {
            // keep as-is
        }

        $perPage = isset($filters['per_page'])
            ? (int) $filters['per_page']
            : 15;

        $perPage = max(1, min($perPage, 100));

        $categories = $query
            ->paginate($perPage)
            ->appends($filters);

        $categories->getCollection()->map->makeHidden([
            'restaurant_id',
            'slug',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        return $categories;
    }

    public function showForRestaurant(int $restaurantId, int $categoryId): FoodCategory
    {
        $category = FoodCategory::query()
            ->with(['parent', 'children'])
            ->where('restaurant_id', $restaurantId)
            ->where('id', $categoryId)
            ->firstOrFail();

        $category->makeHidden([
            'restaurant_id',
            'created_at',
            'slug',
            'updated_at',
            'deleted_at',
        ]);

        return $category;
    }

    public function createForRestaurant(int $restaurantId, array $data): FoodCategory
    {
        $payload = $this->preparePayload($restaurantId, $data);

        $category = FoodCategory::query()->create($payload);

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            fn_upload_image($data['image'], 'category_image', $category->id, true);
        }

        $category = $category->fresh(['parent', 'children']);

        $category->makeHidden([
            'restaurant_id',
            'created_at',
            'updated_at',
            'deleted_at',
            'slug',
            'image',
        ]);

        return $category;
    }
    public function updateForRestaurant(
        int $restaurantId,
        int $categoryId,
        array $data
    ): FoodCategory {
        $category = $this->showForRestaurant($restaurantId, $categoryId);

        $payload = $this->preparePayload(
            $restaurantId,
            $data,
            $category
        );

        $category->fill($payload);
        $category->save();

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            fn_upload_image(
                $data['image'],
                'category_image',
                $category->id,
                true
            );
        }

        $category = $category->fresh(['parent', 'children']);

        $category->makeHidden([
            'restaurant_id',
            'created_at',
            'updated_at',
            'deleted_at',
            'slug',
            'image',
        ]);

        return $category;
    }
    public function updateStatusForRestaurant(int $restaurantId, int $categoryId, bool $status): FoodCategory
    {
        $category = $this->showForRestaurant($restaurantId, $categoryId);
        $category->status = $status;
        $category->save();

        return $category->fresh(['parent', 'children', 'restaurant']);
    }

    public function listActiveParentCategoriesForRestaurant(
        int $restaurantId,
        array $filters = []
    ) {
        $query = FoodCategory::query()
            ->where('restaurant_id', $restaurantId)
            ->where('status', true)
            ->whereNull('parent_id')
            ->orderBy('name', 'asc');

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['food_type'])) {
            $query->where('food_type', $filters['food_type']);
        }

        $categories = $query->get();

        $categories->each->makeHidden([
            'restaurant_id',
            'created_at',
            'updated_at',
            'deleted_at',
            'parent_id',
            'slug',
            'description',
            'status',
            'image',
        ]);

        return $categories;
    }

    public function deleteForRestaurant(int $restaurantId, int $categoryId): bool
    {
        $category = $this->showForRestaurant($restaurantId, $categoryId);

        return (bool) $category->delete();
    }

    public function bulkDeleteForRestaurant(int $restaurantId, array $categoryIds): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));

        if ($ids === []) {
            return 0;
        }

        return (int) FoodCategory::query()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('id', $ids)
            ->delete();
    }

    public function uploadCategoryImage(int $restaurantId, int $categoryId, UploadedFile $file): FoodCategory
    {
        $category = $this->showForRestaurant($restaurantId, $categoryId);

        fn_upload_image($file, 'category_image', $category->id, true);

        return $category->fresh();
    }

    public function removeCategoryImage(int $restaurantId, int $categoryId): array
    {
        $category = $this->showForRestaurant($restaurantId, $categoryId);

        $images = fn_get_images('category_image', $category->id);

        if (empty($images)) {
            return [
                'deleted' => false,
                'message' => 'Image not found to delete.',
            ];
        }

        $deleted = (new \Modules\Filemanager\Services\FileService())->deleteFile('category_image', $category->id);
        $category->update(['image' => null]);

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Food category image removed successfully.' : 'Unable to remove food category image.',
        ];
    }

    protected function preparePayload(int $restaurantId, array $data, ?FoodCategory $category = null): array
    {
        $payload = [
            'restaurant_id' => $restaurantId,
        ];

        if ($category !== null) {
            unset($data['slug']);
        }

        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            $payload['name'] = $name;
        }

        if (array_key_exists('description', $data)) {
            $payload['description'] = $data['description'] !== null ? trim((string) $data['description']) : null;
        }

        if (array_key_exists('food_type', $data)) {
            $payload['food_type'] = $data['food_type'];
        }

        if (array_key_exists('status', $data)) {
            $payload['status'] = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($payload['status'] === null) {
                $payload['status'] = (bool) $data['status'];
            }
        }

        if (array_key_exists('parent_id', $data)) {
            $payload['parent_id'] = $data['parent_id'] ?: null;
        }

        if (array_key_exists('slug', $data)) {
            $payload['slug'] = trim((string) $data['slug']) ?: null;
        }

        if (empty($payload['slug']) && (!empty($payload['name']) || $category === null)) {
            $payload['slug'] = $this->generateUniqueSlug((string) ($payload['name'] ?? ''), $restaurantId, $category?->id);
        }

        if ($category !== null && !array_key_exists('name', $data) && !array_key_exists('slug', $data) && !array_key_exists('image', $data) && !array_key_exists('description', $data) && !array_key_exists('food_type', $data) && !array_key_exists('parent_id', $data) && !array_key_exists('status', $data)) {
            $payload = [];
        }

        return $payload;
    }

    protected function generateUniqueSlug(string $name, int $restaurantId, ?int $ignoreId = null): string
    {
        $base = Str::slug($name ?: 'category');
        $slug = $base ?: 'category';
        $counter = 1;

        while (
            FoodCategory::query()
                ->where('restaurant_id', $restaurantId)
                ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

}
