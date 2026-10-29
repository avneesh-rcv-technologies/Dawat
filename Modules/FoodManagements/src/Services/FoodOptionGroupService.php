<?php

namespace Modules\FoodManagements\Services;

use Modules\FoodManagements\Models\FoodOptionGroup;
use Modules\FoodManagements\Repositories\FoodOptionGroupRepository;

class FoodOptionGroupService
{
    protected FoodOptionGroupRepository $repository;

    public function __construct(FoodOptionGroupRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): FoodOptionGroup
    {
        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $data['min'] = $data['min'] ?? 0;
        $data['max'] = $data['max'] ?? 1;

        return $this->repository->create($data);
    }

    public function getById(int $id): FoodOptionGroup
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): FoodOptionGroup
    {
        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $data['min'] = $data['min'] ?? 0;
        $data['max'] = $data['max'] ?? 1;

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

     /**
     * Get option groups for cart screen
     */
    public function getOptionGroupsForCart(array $itemIds = [])
    {
        return $this->repository->getOptionGroupsForCart($itemIds);
    }
}
