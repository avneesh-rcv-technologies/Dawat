<?php

namespace Modules\Currencies\Repositories;

use Modules\Currencies\Models\Currency;

class CurrencyRepository
{
    public function all(array $filters = [])
    {
        $query = Currency::query();

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

    public function find($id)
    {
        return Currency::findOrFail($id);
    }

    public function create(array $data)
    {
        return Currency::create($data);
    }

    public function update($id, array $data)
    {
        $model = Currency::findOrFail($id);
        $model->fill($data);
        $model->save();
        return $model;
    }

    public function delete($id)
    {
        $model = Currency::findOrFail($id);
        return $model->delete();
    }

    public function deleteMultiple(array $ids): int
    {
        return Currency::whereIn('id', $ids)->delete();
    }
}
