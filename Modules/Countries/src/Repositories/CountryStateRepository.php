<?php

namespace Modules\Countries\Repositories;

use Modules\Countries\Models\CountryState;

class CountryStateRepository
{
    public function find(int $id): ?CountryState
    {
        return CountryState::find($id);
    }

    /**
     * Supported filters: id, ids, country_id, country_code, code, q, order_by, order_dir, paginate, per_page, limit
     */
    public function all(array $filters = [])
    {
        $query = CountryState::query();

        if (!empty($filters['id'])) {
            return $query->where('id', $filters['id'])->first();
        }

        if (!empty($filters['ids'])) {
            $query->whereIn('id', (array)$filters['ids']);
        }

        if (!empty($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        if (!empty($filters['country_code'])) {
            $query->where('country_code', $filters['country_code']);
        }

        if (!empty($filters['code'])) {
            $query->where('code', $filters['code']);
        }

        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qry) use ($q) {
                $qry->where('default_name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $orderBy = $filters['order_by'] ?? 'default_name';
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

    public function create(array $data): CountryState
    {
        return CountryState::create($data);
    }

    public function update(int $id, array $data): ?CountryState
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
        return CountryState::whereIn('id', $ids)->delete();
    }
}
