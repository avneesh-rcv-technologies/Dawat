<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\FoodManagements\Models\FoodFeature;
use Modules\FoodManagements\Services\FoodFeatureService;

class RestaurantFoodFeatureService
{
    public function __construct(protected FoodFeatureService $featureService)
    {
    }

    public function listForRestaurant(array $filters = [])
    {
        $query = FoodFeature::query()
            ->where('feature_type', '!=', 'G')
            ->with(['description', 'parent.description', 'categories', 'variants.description'])
            ->select('food_features.*');

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('food_features.feature_code', 'like', "%{$search}%")
                    ->orWhereHas('description', function (Builder $descriptionQuery) use ($search) {
                        $descriptionQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['group_id'])) {
            $query->where('food_features.parent_id', (int) $filters['group_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('food_features.status', strtoupper((string) $filters['status']));
        }

        if (!empty($filters['purpose'])) {
            $query->where('food_features.purpose', (string) $filters['purpose']);
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
            $query->leftJoin('food_feature_descriptions as food_feature_desc', function ($join) {
                $join->on('food_features.feature_id', '=', 'food_feature_desc.feature_id')
                    ->where('food_feature_desc.lang_code', 'en');
            })
                ->orderBy('food_feature_desc.name', $sortDir)
                ->select('food_features.*');
        } elseif ($sortBy === 'group') {
            $query->leftJoin('food_feature_descriptions as food_parent_desc', function ($join) {
                $join->on('food_features.parent_id', '=', 'food_parent_desc.feature_id')
                    ->where('food_parent_desc.lang_code', 'en');
            })
                ->orderBy('food_parent_desc.name', $sortDir)
                ->select('food_features.*');
        } else {
            $query->orderBy('food_features.' . $sortBy, $sortDir);
        }

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        return $query->paginate($perPage)->appends($filters);
    }

    public function showForRestaurant(int $featureId): FoodFeature
    {
        return FoodFeature::query()
            ->where('feature_type', '!=', 'G')
            ->with(['description', 'parent.description', 'categories', 'variants.description'])
            ->findOrFail($featureId);
    }

    public function createForRestaurant(array $data): FoodFeature
    {
        $payload = $this->normalizePayload($data);
        $feature = $this->featureService->create($payload);

        return $feature->fresh(['description', 'parent.description', 'categories', 'variants.description']);
    }

    public function updateForRestaurant(int $featureId, array $data): FoodFeature
    {
        $feature = $this->showForRestaurant($featureId);
        $payload = $this->normalizePayload($data, $feature);

        $updated = $this->featureService->update($feature, $payload);

        return $updated->fresh(['description', 'parent.description', 'categories', 'variants.description']);
    }

    public function deleteForRestaurant(int $featureId): bool
    {
        $feature = $this->showForRestaurant($featureId);
        $this->featureService->delete($feature);

        return true;
    }

    public function bulkDeleteForRestaurant(array $ids): int
    {
        $ids = $this->normalizeIds($ids);

        if ($ids === []) {
            return 0;
        }

        $features = FoodFeature::query()
            ->where('feature_type', '!=', 'G')
            ->whereIn('feature_id', $ids)
            ->get();

        foreach ($features as $feature) {
            $this->featureService->delete($feature);
        }

        return $features->count();
    }

    protected function normalizePayload(array $data, ?FoodFeature $feature = null): array
    {
        $payload = [
            'name' => trim((string) ($data['name'] ?? $feature?->description?->name ?? '')),
            'description' => array_key_exists('description', $data) ? (string) $data['description'] : ($feature?->description?->description ?? null),
            'feature_code' => trim((string) ($data['feature_code'] ?? $feature?->feature_code ?? '')),
            'feature_type' => $data['feature_type'] ?? $feature?->feature_type ?? null,
            'purpose' => $data['purpose'] ?? $feature?->purpose ?? '',
            'feature_style' => $data['feature_style'] ?? $feature?->feature_style ?? '',
            'filter_style' => $data['filter_style'] ?? $feature?->filter_style ?? '',
            'parent_id' => isset($data['parent_id']) ? (int) $data['parent_id'] : ($feature?->parent_id ?? 0),
            'display_on_product' => $data['display_on_product'] ?? $feature?->display_on_product ?? 'Y',
            'display_on_catalog' => $data['display_on_catalog'] ?? $feature?->display_on_catalog ?? 'Y',
            'display_on_header' => $data['display_on_header'] ?? $feature?->display_on_header ?? 'N',
            'status' => $data['status'] ?? $feature?->status ?? 'A',
            'position' => isset($data['position']) ? (int) $data['position'] : ($feature?->position ?? 0),
            'comparison' => $data['comparison'] ?? $feature?->comparison ?? 'N',
            'category_ids' => $this->normalizeIds($data['category_ids'] ?? $feature?->categories?->pluck('id')->all() ?? []),
            'variants' => $data['variants'] ?? $feature?->variants?->map(fn ($variant) => [
                'variant_id' => $variant->variant_id,
                'description' => $variant->description?->description ?? '',
                'position' => $variant->position,
                'status' => $variant->status,
            ])->all() ?? [],
        ];

        if ($payload['parent_id'] !== null && $payload['parent_id'] > 0) {
            $payload['parent_id'] = (int) $payload['parent_id'];
        } else {
            $payload['parent_id'] = 0;
        }

        return $payload;
    }

    protected function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
    }
}
