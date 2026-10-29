<?php

namespace Modules\Countries\Services;

use Modules\Countries\Repositories\CityRepository;

class CityService
{
    protected CityRepository $cities;

    public function __construct(CityRepository $cities)
    {
        $this->cities = $cities;
    }

    public function index(array $filters = [])
    {
        return $this->cities->all($filters);
    }

    public function find(int $id)
    {
        return $this->cities->find($id);
    }

    public function store(array $data)
    {
        return $this->cities->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->cities->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->cities->delete($id);
    }

    public function deleteMultiple(array $ids): int
    {
        return $this->cities->deleteMultiple($ids);
    }
}
