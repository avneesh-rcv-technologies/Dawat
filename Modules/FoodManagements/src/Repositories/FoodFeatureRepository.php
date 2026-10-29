<?php

namespace Modules\FoodManagements\Repositories;

use Modules\FoodManagements\Models\FoodFeature;

class FoodFeatureRepository
{
    public function all()
    {
        return FoodFeature::with([
            'description',
            'parent.description',
            'categories',
            'variants.description',
        ])
            ->orderBy('position')
            ->get();
    }

    public function forCategories(array $categoryIds = [])
    {
        return FoodFeature::with([
            'description',
            'parent.description',
            'categories',
            'variants.description',
        ])
            ->applicableToCategories($categoryIds)
            ->orderBy('position')
            ->get();
    }

    public function find(int $id): FoodFeature
    {
        return FoodFeature::with([
            'description',
            'parent.description',
            'categories',
            'variants.description',
        ])->findOrFail($id);
    }

    public function create(array $data): FoodFeature
    {
        return FoodFeature::create($data);
    }

    public function update(FoodFeature $feature, array $data): FoodFeature
    {
        $feature->update($data);
        return $feature;
    }

    public function delete(FoodFeature $feature): void
    {
        $feature->delete();
    }

    public function bulkDelete(array $ids): void
    {
        FoodFeature::whereIn('feature_id', $ids)->delete();
    }
}
