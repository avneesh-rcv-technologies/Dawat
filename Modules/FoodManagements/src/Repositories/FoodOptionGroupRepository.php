<?php

namespace Modules\FoodManagements\Repositories;

use Modules\FoodManagements\Models\FoodOptionGroup;

class FoodOptionGroupRepository
{
    public function findById(int $id): FoodOptionGroup
    {
        return FoodOptionGroup::with('options')->findOrFail($id);
    }

    public function create(array $data): FoodOptionGroup
    {
        return FoodOptionGroup::create($data);
    }

    public function update(int $id, array $data): FoodOptionGroup
    {
        $group = FoodOptionGroup::findOrFail($id);
        $group->update($data);
        return $group;
    }

    public function delete(int $id): bool
    {
        return FoodOptionGroup::where('id', $id)->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return FoodOptionGroup::whereIn('id', $ids)->delete();
    }

    /**
     * Get option groups for cart screen
     */
    public function getOptionGroupsForCart(array $itemIds = [])
    {
        $query = FoodOptionGroup::with('options');

        if (!empty($itemIds)) {
            $query->whereHas('foodItems', function ($q) use ($itemIds) {
                $q->whereIn('food_items.id', $itemIds);
            });
        }

        return $query->orderBy('name')->get()->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'is_required' => $group->is_required,
                'min' => $group->min,
                'max' => $group->max,
                'options' => $group->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'name' => $option->name,
                        'price' => $option->price ?? 0,
                    ];
                }),
            ];
        });
    }
}
