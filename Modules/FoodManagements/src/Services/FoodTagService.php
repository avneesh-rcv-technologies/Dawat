<?php

namespace Modules\FoodManagements\Services;

use Illuminate\Support\Str;
use Modules\FoodManagements\Models\FoodTag;
use Modules\FoodManagements\Repositories\FoodTagRepository;

class FoodTagService
{
    protected FoodTagRepository $repository;

    public function __construct(FoodTagRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): FoodTag
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->repository->create($data);
    }

    public function getById(int $id): FoodTag
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): FoodTag
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->repository->bulkDelete($ids);
    }
}
