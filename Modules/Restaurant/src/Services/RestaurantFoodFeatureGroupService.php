<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\FoodManagements\Models\FoodFeature;
use Modules\FoodManagements\Services\FoodFeatureService;

class RestaurantFoodFeatureGroupService
{
    public function __construct(protected FoodFeatureService $featureService)
    {
    }

    public function listForRestaurant(array $filters = [])
    {
        $query = FoodFeature::query()
            ->where('feature_type', 'G')
            ->with(['description', 'categories'])
            ->select('food_features.*');

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $groupQuery) use ($search) {
                $groupQuery->where('food_features.feature_code', 'like', "%{$search}%")
                    ->orWhereHas('description', function (Builder $descriptionQuery) use ($search) {
                        $descriptionQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (array_key_exists('status', $filters) && $filters['status'] !== '') {
            $query->where('food_features.status', strtoupper((string) $filters['status']));
        }

        if (!empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];

            $query->where(function (Builder $categoryQuery) use ($categoryId) {
                $categoryQuery->whereDoesntHave('categories')
                    ->orWhereHas('categories', fn (Builder $relation) => $relation->where('food_categories.id', $categoryId));
            });
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDir = strtolower((string) ($filters['sort_dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'name') {
            $query->leftJoin('food_feature_descriptions as food_feature_group_desc', function ($join) {
                $join->on('food_features.feature_id', '=', 'food_feature_group_desc.feature_id')
                    ->where('food_feature_group_desc.lang_code', 'en');
            })
                ->orderBy('food_feature_group_desc.name', $sortDir)
                ->select('food_features.*');
        } else {
            $query->orderBy('food_features.' . $sortBy, $sortDir);
        }

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        return $query->paginate($perPage)->appends($filters);
    }

    public function showForRestaurant(int $groupId): FoodFeature
    {
        return FoodFeature::query()
            ->where('feature_type', 'G')
            ->with(['description', 'categories'])
            ->findOrFail($groupId);
    }

    public function createForRestaurant(array $data): FoodFeature
    {
        $payload = [
            'name' => trim((string) ($data['name'] ?? '')),
            'description' => isset($data['description']) ? (string) $data['description'] : null,
            'feature_code' => trim((string) ($data['feature_code'] ?? '')),
            'status' => $data['status'] ?? 'A',
            'position' => isset($data['position']) ? (int) $data['position'] : 0,
            'display_on_product' => $data['display_on_product'] ?? true,
            'display_on_catalog' => $data['display_on_catalog'] ?? true,
            'display_on_header' => $data['display_on_header'] ?? false,
            'feature_type' => 'G',
            'parent_id' => 0,
            'category_ids' => $this->normalizeCategoryIds($data['category_ids'] ?? []),
        ];

        $feature = $this->featureService->create($payload);

        return $feature->fresh(['description', 'categories']);
    }

    public function updateForRestaurant(int $groupId, array $data): FoodFeature
    {
        $group = $this->showForRestaurant($groupId);

        $payload = [
            'name' => array_key_exists('name', $data) ? trim((string) $data['name']) : $group->description?->name,
            'description' => array_key_exists('description', $data) ? (string) $data['description'] : $group->description?->description,
            'feature_code' => array_key_exists('feature_code', $data) ? trim((string) $data['feature_code']) : $group->feature_code,
            'status' => $data['status'] ?? $group->status,
            'position' => array_key_exists('position', $data) ? (int) $data['position'] : $group->position,
            'display_on_product' => array_key_exists('display_on_product', $data) ? $data['display_on_product'] : $group->display_on_product,
            'display_on_catalog' => array_key_exists('display_on_catalog', $data) ? $data['display_on_catalog'] : $group->display_on_catalog,
            'display_on_header' => array_key_exists('display_on_header', $data) ? $data['display_on_header'] : $group->display_on_header,
            'feature_type' => 'G',
            'parent_id' => 0,
            'category_ids' => $this->normalizeCategoryIds($data['category_ids'] ?? $group->categories->pluck('id')->all()),
        ];

        $feature = $this->featureService->update($group, $payload);

        return $feature->fresh(['description', 'categories']);
    }

    public function deleteForRestaurant(int $groupId): bool
    {
        $group = $this->showForRestaurant($groupId);
        $this->featureService->delete($group);

        return true;
    }

    public function bulkDeleteForRestaurant(array $ids): int
    {
        $ids = $this->normalizeIds($ids);

        if ($ids === []) {
            return 0;
        }

        $groups = FoodFeature::query()
            ->where('feature_type', 'G')
            ->whereIn('feature_id', $ids)
            ->get();

        foreach ($groups as $group) {
            $this->featureService->delete($group);
        }

        return $groups->count();
    }

    protected function normalizeCategoryIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
    }

    protected function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
    }
}
