<?php

namespace Modules\Countries\Services;

use Modules\Countries\Repositories\CountryRepository;

class CountryService
{
    protected CountryRepository $countries;

    public function __construct(CountryRepository $countries)
    {
        $this->countries = $countries;
    }

    public function index(array $filters = [])
    {
        return $this->countries->all($filters);
    }

    public function find(int $id)
    {
        return $this->countries->find($id);
    }

    public function show(int $id)
    {
        return $this->countries->find($id);
    }

    public function store(array $data)
    {
        return $this->countries->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->countries->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->countries->delete($id);
    }

    public function deleteMultiple(array $ids): int
    {
        return $this->countries->multipleDelete($ids);
    }

     public function getCountries()
    {
        return $this->countries->getAllCountries();
    }
}
