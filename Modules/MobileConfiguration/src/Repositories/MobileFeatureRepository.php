<?php

namespace Modules\MobileConfiguration\Repositories;

use Modules\MobileConfiguration\Models\MobileFeature;
use Modules\MobileConfiguration\Repositories\Interfaces\MobileFeatureRepositoryInterface;

class MobileFeatureRepository implements MobileFeatureRepositoryInterface
{
    public function create(array $data): MobileFeature
    {
        return MobileFeature::create($data);
    }

    public function update(MobileFeature $feature, array $data): MobileFeature
    {
        $feature->update($data);
        return $feature;
    }

    public function delete(MobileFeature $feature): void
    {
        $feature->delete();
    }

    public function bulkDelete(array $ids): void
    {
        MobileFeature::whereIn('id', $ids)->delete();
    }

    public function updateStatus(array $ids, bool $status): void
    {
        MobileFeature::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function getActiveFeatures()
    {
        return MobileFeature::where('status', 1)
            ->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPaginatedFeatures(int $perPage = 15)
    {
        return MobileFeature::orderBy('position')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getBySlug(string $slug): ?MobileFeature
    {
        return MobileFeature::where('slug', $slug)->first();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        return MobileFeature::where('slug', $slug)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}