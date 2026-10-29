<?php

namespace Modules\FoodManagements\Repositories;

use Modules\FoodManagements\Models\FoodTag;

class FoodTagRepository
{
    public function findById(int $id): FoodTag
    {
        return FoodTag::findOrFail($id);
    }

    public function create(array $data): FoodTag
    {
        return FoodTag::create($data);
    }

    public function update(int $id, array $data): FoodTag
    {
        $tag = FoodTag::findOrFail($id);
        $tag->update($data);
        return $tag;
    }

    public function delete(int $id): bool
    {
        return FoodTag::where('id', $id)->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return FoodTag::whereIn('id', $ids)->delete();
    }
}
