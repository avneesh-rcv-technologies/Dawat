<?php

namespace Modules\Countries\Repositories;

use Modules\Countries\Models\City;

class CityRepository
{
    public function find(int $id): ?City
    {
        return City::find($id);
    }

    /**
     * Get list of cities with simple filters.
     * Supported filters: id, ids, country_id, state_id, name, q (search), order_by, order_dir, paginate, per_page, limit
     */
    public function all(array $filters = [])
    {
        $query = City::query();

        if (!empty($filters['id'])) {
            return $query->where('id', $filters['id'])->first();
        }

        if (!empty($filters['ids'])) {
            $query->whereIn('id', (array)$filters['ids']);
        }

        if (!empty($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        if (!empty($filters['state_id'])) {
            $query->where('state_id', $filters['state_id']);
        }

        if (!empty($filters['name'])) {
            $query->where('name', $filters['name']);
        }

        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%");
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

    public function create(array $data): City
    {
        return City::create($data);
    }

    public function update(int $id, array $data): ?City
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

    public function deleteMultiple(array $ids): int
    {
        return City::whereIn('id', $ids)->delete();
    }
}
