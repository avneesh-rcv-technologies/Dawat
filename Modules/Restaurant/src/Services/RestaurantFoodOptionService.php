<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\FoodManagements\Models\FoodOption;
use Modules\FoodManagements\Models\FoodOptionGroup;

class RestaurantFoodOptionService
{
    public function listForRestaurant(array $filters = [])
    {
        $query = FoodOptionGroup::query()
            ->with(['options'])
            ->orderBy($filters['sort_by'] ?? 'name', $filters['sort_dir'] ?? 'asc');

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if (array_key_exists('is_required', $filters) && $filters['is_required'] !== '') {
            $query->where('is_required', (bool) $filters['is_required']);
        }

        if (!empty($filters['min'])) {
            $query->where('min', '>=', (int) $filters['min']);
        }

        if (!empty($filters['max'])) {
            $query->where('max', '<=', (int) $filters['max']);
        }

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        return $query->paginate($perPage)->appends($filters);
    }

    public function showForRestaurant(int $groupId): FoodOptionGroup
    {
        return FoodOptionGroup::query()
            ->with(['options'])
            ->findOrFail($groupId);
    }

    public function createForRestaurant(array $data): FoodOptionGroup
    {
        $payload = [
            'name' => trim((string) ($data['name'] ?? '')),
            'is_required' => (bool) ($data['is_required'] ?? false),
            'min' => isset($data['min']) ? (int) $data['min'] : 0,
            'max' => isset($data['max']) ? (int) $data['max'] : 1,
        ];

        return FoodOptionGroup::query()->create($payload);
    }

    public function updateForRestaurant(int $groupId, array $data): FoodOptionGroup
    {
        $group = $this->showForRestaurant($groupId);

        $group->fill([
            'name' => trim((string) ($data['name'] ?? $group->name)),
            'is_required' => array_key_exists('is_required', $data) ? (bool) $data['is_required'] : $group->is_required,
            'min' => array_key_exists('min', $data) ? (int) $data['min'] : $group->min,
            'max' => array_key_exists('max', $data) ? (int) $data['max'] : $group->max,
        ]);

        $group->save();

        return $group->fresh(['options']);
    }

    public function deleteForRestaurant(int $groupId): bool
    {
        $group = $this->showForRestaurant($groupId);

        return (bool) $group->delete();
    }

    public function bulkDeleteForRestaurant(array $ids): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === []) {
            return 0;
        }

        return (int) FoodOptionGroup::query()->whereIn('id', $ids)->delete();
    }

    public function listOptionsForGroup(int $groupId, array $filters = [])
    {
        $group = $this->showForRestaurant($groupId);

        $query = $group->options()
            ->orderBy($filters['sort_by'] ?? 'name', $filters['sort_dir'] ?? 'asc');

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('name', 'like', "%{$search}%");
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        return $query->paginate($perPage)->appends($filters);
    }

    public function createOptionForGroup(int $groupId, array $data): FoodOption
    {
        $group = $this->showForRestaurant($groupId);

        return $group->options()->create([
            'name' => trim((string) ($data['name'] ?? '')),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    public function bulkDeleteOptionsForGroup(int $groupId, array $ids): int
    {
        $group = $this->showForRestaurant($groupId);

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === []) {
            return 0;
        }

        return (int) $group->options()->whereIn('id', $ids)->delete();
    }

    public function updateOptionForGroup(int $groupId, int $optionId, array $data): FoodOption
    {
        $group = $this->showForRestaurant($groupId);
        $option = $group->options()->where('id', $optionId)->firstOrFail();

        $option->fill([
            'name' => trim((string) ($data['name'] ?? $option->name)),
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $option->is_active,
        ]);

        $option->save();

        return $option->fresh();
    }

    public function deleteOptionForGroup(int $groupId, int $optionId): bool
    {
        $group = $this->showForRestaurant($groupId);
        $option = $group->options()->where('id', $optionId)->firstOrFail();

        return (bool) $option->delete();
    }
}
