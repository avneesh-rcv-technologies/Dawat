<?php

namespace Modules\MobileConfiguration\Repositories;

use Modules\MobileConfiguration\Models\MobileCms;
use Modules\MobileConfiguration\Repositories\Interfaces\MobileCmsRepositoryInterface;

class MobileCmsRepository implements MobileCmsRepositoryInterface
{
    public function create(array $data): MobileCms
    {
        return MobileCms::create($data);
    }

    public function update(MobileCms $mobileCms, array $data): MobileCms
    {
        $mobileCms->update($data);
        return $mobileCms;
    }

    public function delete(MobileCms $mobileCms): void
    {
        $mobileCms->delete();
    }

    public function bulkDelete(array $ids): void
    {
        MobileCms::whereIn('id', $ids)->delete();
    }

    public function updateStatus(array $ids, bool $status): void
    {
        MobileCms::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function getActiveCms()
    {
        return MobileCms::with('cmsPage')
            ->where('status', 1)
            ->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPaginatedCms(int $perPage = 15)
    {
        return MobileCms::with('cmsPage')
            ->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?MobileCms
    {
        return MobileCms::with('cmsPage')->find($id);
    }
}