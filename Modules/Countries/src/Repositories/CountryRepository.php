<?php

namespace Modules\Countries\Repositories;

use Modules\Countries\Models\Country;

class CountryRepository
{
    public function find(int $id): ?Country
    {
        return Country::find($id);
    }

    public function getAllCountries()
    {
        return Country::select('id', 'name')->orderBy('name')->get();
    }
    
    /**
     * Get list of countries with simple filters.
     * Supported filters: id, ids, code, name, q (search), order_by, order_dir, paginate, per_page, limit
     */
    public function all(array $filters = [])
    {
        $query = Country::query();

        if (!empty($filters['id'])) {
            return $query->where('id', $filters['id'])->first();
        }

        if (!empty($filters['ids'])) {
            $query->whereIn('id', (array)$filters['ids']);
        }

        if (!empty($filters['code'])) {
            $query->where('code', $filters['code']);
        }

        if (!empty($filters['name'])) {
            $query->where('name', $filters['name']);
        }

        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $orderBy = $filters['order_by'] ?? 'name';
        $orderDir = $filters['order_dir'] ?? 'asc';

        $query->orderBy($orderBy, $orderDir);

        if (!empty($filters['paginate'])) {
            $perPage = $filters['per_page'] ?? 15;
            return $query->paginate($perPage);
        }

        if (!empty($filters['limit'])) {
            $query->limit(intval($filters['limit']));
        }

        return $query->get();
    }

    public function create(array $data): Country
    {
        return Country::create($data);
    }

    public function update(int $id, array $data): ?Country
    {
        $model = $this->find($id);
        if (!$model) {
            return null;
        }
        $model->fill($data);
        $model->save();
        return $model;
    }

    public function delete(int $id): bool
    {
        $model = $this->find($id);
        if (!$model) {
            return false;
        }
        return (bool)$model->delete();
    }

    public function deleteByCodes(array $codes): int
    {
        return Country::whereIn('code', $codes)->delete();
    }

    public function multipleDelete(array $ids): int
    {
        return Country::whereIn('id', $ids)->delete();
    }
}
