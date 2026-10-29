<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodFeatureValue;
use Modules\FoodManagements\Models\FoodFeatureVariant;
use Modules\FoodManagements\Models\FoodItem;

class RestaurantFoodItemService
{
    public function listForRestaurant(int $restaurantId, array $filters = [])
    {
        $query = FoodItem::query()
            ->with([
                'categories',
                'tags',
                'optionGroups.options',
                'featureValues',
            ])
            ->whereHas('categories', function (Builder $categoryQuery) use ($restaurantId) {
                $categoryQuery->where('restaurant_id', $restaurantId);
            });

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $itemQuery) use ($search) {
                $itemQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('long_description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->whereHas('categories', function (Builder $categoryQuery) use ($filters) {
                $categoryQuery->where('food_categories.id', (int) $filters['category_id']);
            });
        }

        if (!empty($filters['tag_id'])) {
            $query->whereHas('tags', function (Builder $tagQuery) use ($filters) {
                $tagQuery->where('food_tags.id', (int) $filters['tag_id']);
            });
        }

        if (!empty($filters['food_type'])) {
            $query->where('food_type', (string) $filters['food_type']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $status = filter_var(
                $filters['status'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if ($status !== null) {
                $query->where('is_active', $status);
            }
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';

        $sortDir = strtolower((string) ($filters['sort_dir'] ?? 'desc')) === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sortBy, $sortDir);

        $perPage = isset($filters['per_page'])
            ? (int) $filters['per_page']
            : 15;

        $perPage = max(1, min($perPage, 100));

        $items = $query->paginate($perPage)->appends($filters);

        // Hide slug from FoodItem
        $items->getCollection()->each(function ($item) {
            $item->makeHidden(['slug']);

            // Hide slug from categories
            $item->categories->each->makeHidden(['slug']);

            // Hide slug from tags
            $item->tags->each->makeHidden(['slug']);
        });

        return $items;
    }
    public function showForRestaurant(int $restaurantId, int $foodItemId): FoodItem
    {
        $foodItem = FoodItem::query()
            ->with([
                'categories',
                'tags',
                'optionGroups.options',
                'featureValues',
            ])
            ->where('restaurant_id', $restaurantId)
            ->where('id', $foodItemId)
            ->firstOrFail();

        // Hide FoodItem slug
        $foodItem->makeHidden([
            'slug',
        ]);

        // Hide category slugs
        $foodItem->categories->each->makeHidden([
            'slug',
        ]);

        // Hide tag slugs
        $foodItem->tags->each->makeHidden([
            'slug',
        ]);

        return $foodItem;
}

    public function createForRestaurant(int $restaurantId, array $data, array $files = []): FoodItem
    {
        $payload = $this->normalizeCreatePayload($data, null, $restaurantId);

        $foodItem = FoodItem::query()->create($payload);

        $categoryIds = $this->normalizeIds($data['categories'] ?? []);
        $allowedCategoryIds = FoodCategory::query()
            ->where('restaurant_id', $restaurantId)
            ->pluck('id')
            ->all();

        $validCategoryIds = array_values(array_intersect($categoryIds, $allowedCategoryIds));
        if (array_key_exists('categories', $data)) {
            $foodItem->categories()->sync($validCategoryIds);
        }

        $tagIds = $this->normalizeIds($data['tags'] ?? []);
        if (array_key_exists('tags', $data)) {
            $foodItem->tags()->sync($tagIds);
        }

        $optionGroupIds = $this->normalizeIds($data['option_groups'] ?? []);
        if (array_key_exists('option_groups', $data)) {
            $foodItem->optionGroups()->sync($optionGroupIds);
        }

        if (!empty($data['option_prices']) && is_array($data['option_prices'])) {
            foreach ($data['option_prices'] as $optionId => $price) {
                $foodItem->setOptionPrice((int) $optionId, (float) $price);
            }
        }

        if (array_key_exists('features', $data) && is_array($data['features'])) {
            $this->syncFeatureValues($foodItem, $data['features']);
        }

        $this->handleUploadedImages($foodItem, $files);

        return $foodItem->fresh(['categories', 'tags', 'optionGroups.options', 'featureValues']);
    }

    public function updateForRestaurant(int $restaurantId, int $foodItemId, array $data, array $files = []): FoodItem
    {
        $foodItem = $this->showForRestaurant($restaurantId, $foodItemId);
        $payload = $this->normalizeUpdatePayload($data, $foodItem);

        if (!empty($payload)) {
            $foodItem->update($payload);
        }

        if (array_key_exists('categories', $data)) {
            $categoryIds = $this->normalizeIds($data['categories'] ?? []);
            $allowedCategoryIds = FoodCategory::query()
                ->where('restaurant_id', $restaurantId)
                ->pluck('id')
                ->all();

            $validCategoryIds = array_values(array_intersect($categoryIds, $allowedCategoryIds));
            $foodItem->categories()->sync($validCategoryIds);
        }

        if (array_key_exists('tags', $data)) {
            $foodItem->tags()->sync($this->normalizeIds($data['tags'] ?? []));
        }

        if (array_key_exists('option_groups', $data)) {
            $foodItem->optionGroups()->sync($this->normalizeIds($data['option_groups'] ?? []));
        }

        if (array_key_exists('option_prices', $data) && is_array($data['option_prices'])) {
            foreach ($data['option_prices'] as $optionId => $price) {
                $foodItem->setOptionPrice((int) $optionId, (float) $price);
            }
        }

        if (array_key_exists('features', $data) && is_array($data['features'])) {
            $this->syncFeatureValues($foodItem, $data['features']);
        }

        $this->handleUploadedImages($foodItem, $files);

        return $foodItem->fresh(['categories', 'tags', 'optionGroups.options', 'featureValues']);
    }

    public function deleteForRestaurant(int $restaurantId, int $foodItemId): bool
    {
        $foodItem = $this->showForRestaurant($restaurantId, $foodItemId);
        $foodItem->delete();

        return true;
    }

    protected function normalizeCreatePayload(array $data, ?int $ignoreId = null, ?int $restaurantId = null): array
    {
        $name = trim((string) ($data['name'] ?? ''));

        return [
            'restaurant_id' => $restaurantId,
            'name' => $name,
            'slug' => $this->generateUniqueSlug($name, $ignoreId),
            'description' => $data['description'] ?? null,
            'long_description' => $data['long_description'] ?? null,
            'sale_price' => isset($data['sale_price']) ? (float) $data['sale_price'] : 0,
            'cost_price' => array_key_exists('cost_price', $data) && $data['cost_price'] !== '' ? (float) $data['cost_price'] : null,
            'list_price' => array_key_exists('list_price', $data) && $data['list_price'] !== '' ? (float) $data['list_price'] : null,
            'in_stock' => isset($data['in_stock']) ? (bool) $data['in_stock'] : true,
            'quantity_type' => $data['quantity_type'] ?? 'manual',
            'quantity' => isset($data['quantity']) ? (int) $data['quantity'] : 0,
            'is_available' => isset($data['is_available']) ? (bool) $data['is_available'] : true,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            'food_type' => $data['food_type'] ?? 'veg',
        ];
    }

    protected function normalizeUpdatePayload(array $data, FoodItem $foodItem): array
    {
        $payload = [];

        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            if ($name !== '') {
                $payload['name'] = $name;
                $payload['slug'] = $this->generateUniqueSlug($name, $foodItem->id);
            }
        }

        foreach (['description', 'long_description', 'sale_price', 'cost_price', 'list_price', 'in_stock', 'quantity_type', 'quantity', 'is_available', 'is_active', 'food_type'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        return $payload;
    }

    protected function syncFeatureValues(FoodItem $foodItem, array $featureValues): void
    {
        FoodFeatureValue::query()->where('food_item_id', $foodItem->id)->delete();

        foreach ($featureValues as $featureId => $valueSet) {
            $values = is_array($valueSet) ? $valueSet : [$valueSet];

            foreach ($values as $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                $variantId = null;
                if (is_numeric($value)) {
                    $variantMatch = FoodFeatureVariant::query()
                        ->where('variant_id', (int) $value)
                        ->where('feature_id', (int) $featureId)
                        ->exists();

                    if ($variantMatch) {
                        $variantId = (int) $value;
                    }
                }

                FoodFeatureValue::query()->create([
                    'feature_id' => (int) $featureId,
                    'food_item_id' => $foodItem->id,
                    'variant_id' => $variantId,
                    'value' => $variantId !== null ? '' : (string) $value,
                    'value_int' => $variantId === null && is_numeric($value) ? (float) $value : null,
                    'lang_code' => 'en',
                ]);
            }
        }
    }

    protected function handleUploadedImages(FoodItem $foodItem, array $files = []): void
    {
        if (!empty($files['food_image'])) {
            $image = $files['food_image'];
            if ($image instanceof UploadedFile) {
                fn_upload_image($image, 'food_image', $foodItem->id, true);
            }
        }

        if (!empty($files['images'])) {
            $images = $files['images'];
            if (!is_array($images)) {
                $images = [$images];
            }

            foreach ($images as $image) {
                if ($image instanceof UploadedFile) {
                    fn_upload_image($image, 'food_image', $foodItem->id, false);
                }
            }
        }
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name) ?: 'food-item';
        $original = $slug;
        $counter = 1;

        while (FoodItem::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function normalizeIds(array $values): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $values), fn ($id) => $id > 0)));
    }
}
