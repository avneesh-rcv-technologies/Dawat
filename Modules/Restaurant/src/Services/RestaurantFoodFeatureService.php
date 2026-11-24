<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\FoodManagements\Models\FoodFeature;
use Modules\FoodManagements\Services\FoodFeatureService;
use Illuminate\Validation\ValidationException;

class RestaurantFoodFeatureService
{
    public const TYPES = [
        'S' => ['purpose' => 'find_products', 'feature_style' => 'text', 'filter_style' => 'checkbox'],
        'M' => ['purpose' => 'find_products', 'feature_style' => 'multiple_checkbox', 'filter_style' => 'checkbox'],
        'C' => ['purpose' => 'find_products', 'feature_style' => 'checkbox', 'filter_style' => 'checkbox'],
        'T' => ['purpose' => 'describe_product', 'feature_style' => 'text', 'filter_style' => ''],
        'O' => ['purpose' => 'find_products', 'feature_style' => 'number', 'filter_style' => 'slider'],
    ];

    public function __construct(protected FoodFeatureService $featureService)
    {
    }

    public function listForRestaurant(int $restaurantId, array $filters = [])
    {
        $query = FoodFeature::query()->forRestaurant($restaurantId)
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

    public function showForRestaurant(int $restaurantId, int $featureId): FoodFeature
    {
        return FoodFeature::query()->forRestaurant($restaurantId)
            ->where('feature_type', '!=', 'G')
            ->with(['description', 'parent.description', 'categories', 'variants.description'])
            ->findOrFail($featureId);
    }

    public function createForRestaurant(int $restaurantId, array $data): FoodFeature
    {
        $payload = $this->normalizePayload($data) + ['restaurant_id' => $restaurantId];
        $feature = $this->featureService->create($payload);

        return $feature->fresh(['description', 'parent.description', 'categories', 'variants.description']);
    }

    public function updateForRestaurant(int $restaurantId, int $featureId, array $data): FoodFeature
    {
        $feature = $this->showForRestaurant($restaurantId, $featureId);
        $payload = $this->normalizePayload($data, $feature);

        $updated = $this->featureService->update($feature, $payload);

        return $updated->fresh(['description', 'parent.description', 'categories', 'variants.description']);
    }

    public function updateStatusForRestaurant(int $restaurantId, int $featureId, bool $status): FoodFeature
    {
        $feature = $this->showForRestaurant($restaurantId, $featureId);
        $feature->update(['status' => $status ? 'A' : 'D']);

        return $feature->fresh(['description', 'parent.description', 'categories', 'variants.description']);
    }

    /** Active features with active options, shaped for the app's food item form. */
    public function activeListsForRestaurant(int $restaurantId): array
    {
        return FoodFeature::query()->forRestaurant($restaurantId)
            ->where('feature_type', '!=', 'G')
            ->where('status', 'A')
            ->with(['description', 'variants' => fn ($query) => $query->where('status', 'A')->orderBy('position'), 'variants.description'])
            ->orderBy('position')->orderBy('feature_id')
            ->get()
            ->map(fn (FoodFeature $feature) => [
                'feature_id' => $feature->feature_id,
                'name' => $feature->description?->name,
                'feature_type' => $feature->feature_type,
                'feature_style' => $feature->feature_style,
                'variants' => $feature->variants->map(fn ($variant) => [
                    'variant_id' => $variant->variant_id,
                    'name' => $variant->description?->description,
                ])->values()->all(),
            ])->values()->all();
    }

    public function deleteForRestaurant(int $restaurantId, int $featureId): bool
    {
        $feature = $this->showForRestaurant($restaurantId, $featureId);
        $this->featureService->delete($feature);

        return true;
    }

    public function bulkDeleteForRestaurant(int $restaurantId, array $ids): int
    {
        $ids = $this->normalizeIds($ids);

        if ($ids === []) {
            return 0;
        }

        $features = FoodFeature::query()->forRestaurant($restaurantId)
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
        $type = $data['feature_type'] ?? $feature?->feature_type ?? 'S';
        if (! isset(self::TYPES[$type])) {
            throw ValidationException::withMessages(['feature_type' => 'Choose S, M, C, T, or O.']);
        }
        $payload = [
            'name' => trim((string) ($data['name'] ?? $feature?->description?->name ?? '')),
            'description' => array_key_exists('description', $data) ? (string) $data['description'] : ($feature?->description?->description ?? null),
            'feature_code' => trim((string) ($data['feature_code'] ?? $feature?->feature_code ?? '')),
            'feature_type' => $type,
            ...self::TYPES[$type],
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

        if (! in_array($type, ['S', 'M'], true)) {
            $payload['variants'] = [];
        } else {
            $existing = $feature?->variants ?? collect();
            $names = [];
            $ids = [];
            $payload['variants'] = array_map(function ($variant, $index) use ($existing, &$names, &$ids) {
                $description = trim((string) ($variant['description'] ?? ''));
                $key = mb_strtolower($description);
                if ($description === '' || in_array($key, $names, true)) {
                    throw ValidationException::withMessages(['variants' => 'Enter distinct, non-empty option descriptions.']);
                }
                $names[] = $key;
                $variantId = $variant['variant_id'] ?? null;
                if ($variantId && ! $existing->contains('variant_id', (int) $variantId)) {
                    throw ValidationException::withMessages(['variants' => 'Choose an option belonging to this feature.']);
                }
                // Retain unchanged options and their food-item assignments without requiring IDs.
                $match = $variantId ? $existing->firstWhere('variant_id', (int) $variantId)
                    : $existing->first(fn ($option) => mb_strtolower(trim((string) $option->description?->description)) === $key);
                if ($match && in_array($match->variant_id, $ids, true)) {
                    throw ValidationException::withMessages(['variants' => 'Each existing option can be included only once.']);
                }
                if ($match) {
                    $ids[] = $match->variant_id;
                }

                return [
                    'variant_id' => $match?->variant_id, 'description' => $description,
                    'position' => $index + 1, 'status' => $variant['status'] ?? 'A',
                ];
            }, array_values($payload['variants']), array_keys(array_values($payload['variants'])));
            if (count($payload['variants']) < 2) {
                throw ValidationException::withMessages(['variants' => 'Add at least two options.']);
            }
        }

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
