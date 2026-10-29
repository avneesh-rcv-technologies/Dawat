<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Str;
use Modules\FoodManagements\Models\FoodTag;

class RestaurantFoodTagService
{
    public function listForRestaurant(array $filters = [])
    {
        $query = FoodTag::query()
            ->orderBy($filters['sort_by'] ?? 'name', $filters['sort_dir'] ?? 'asc');

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['name'])) {
            $name = trim((string) $filters['name']);
            $query->where('name', 'like', "%{$name}%");
        }

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        return $query->paginate($perPage)->appends($filters);
    }

    public function showForRestaurant(int $tagId): FoodTag
    {
        return FoodTag::query()->findOrFail($tagId);
    }

    public function createForRestaurant(array $data): FoodTag
    {
        $payload = [
            'name' => trim((string) ($data['name'] ?? '')),
            'slug' => $this->generateUniqueSlug((string) ($data['name'] ?? '')),
        ];

        $tag = FoodTag::query()->create($payload);

        $tag->makeHidden([
            'slug',
        ]);

        return $tag;
    }

    public function updateForRestaurant(int $tagId, array $data): FoodTag
    {
        $tag = $this->showForRestaurant($tagId);

        $tag->fill([
            'name' => trim((string) ($data['name'] ?? $tag->name)),
            'slug' => $this->generateUniqueSlug(
                (string) ($data['name'] ?? $tag->name),
                $tag->id
            ),
        ]);

        $tag->save();

        $tag = $tag->fresh();

        $tag->makeHidden([
            'slug',
        ]);

        return $tag;
    }

    public function deleteForRestaurant(int $tagId): bool
    {
        $tag = $this->showForRestaurant($tagId);

        return (bool) $tag->delete();
    }

    public function bulkDeleteForRestaurant(array $tagIds): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $tagIds))));

        if ($ids === []) {
            return 0;
        }

        return (int) FoodTag::query()->whereIn('id', $ids)->delete();
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name ?: 'food-tag');
        $slug = $base ?: 'food-tag';
        $counter = 1;

        while (FoodTag::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
