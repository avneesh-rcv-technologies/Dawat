<?php

namespace Modules\Currencies\Services;

use Modules\Currencies\Repositories\CurrencyRepository;

class CurrencyService
{
    protected CurrencyRepository $repo;

    public function __construct(CurrencyRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(array $filters = [])
    {
        return $this->repo->all($filters);
    }

    public function show($id)
    {
        return $this->repo->find($id);
    }

    public function store(array $data)
    {
        return $this->repo->create($data);
    }

    // Compatibility aliases used by some controllers
    public function create(array $data)
    {
        return $this->store($data);
    }

    public function find($id)
    {
        return $this->show($id);
    }

    public function deleteMultiple(array $ids): int
    {
        return $this->repo->deleteMultiple($ids);
    }

    public function update($id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function delete($id)
    {
        return (bool)$this->repo->delete($id);
    }
}
