<?php

namespace Modules\Countries\Services;

use Modules\Countries\Repositories\CountryStateRepository;

class StateService
{
    protected CountryStateRepository $states;

    public function __construct(CountryStateRepository $states)
    {
        $this->states = $states;
    }

    public function index(array $filters = [])
    {
        return $this->states->all($filters);
    }

    public function find(int $id)
    {
        return $this->states->find($id);
    }

    public function store(array $data)
    {
        return $this->states->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->states->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->states->delete($id);
    }

    public function deleteMultiple(array $ids): int
    {
        return $this->states->deleteMultiple($ids);
    }
}
