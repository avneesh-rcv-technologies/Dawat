<?php

namespace Modules\MobileConfiguration\Repositories;

use Modules\MobileConfiguration\Models\MobileBanner;
use Modules\MobileConfiguration\Repositories\Interfaces\MobileBannerRepositoryInterface;

class MobileBannerRepository implements MobileBannerRepositoryInterface
{
    public function create(array $data): MobileBanner
    {
        return MobileBanner::create($data);
    }

    public function update(MobileBanner $banner, array $data): MobileBanner
    {
        $banner->update($data);
        return $banner;
    }

    public function delete(MobileBanner $banner): void
    {
        $banner->delete();
    }

    public function bulkDelete(array $ids): void
    {
        MobileBanner::whereIn('id', $ids)->delete();
    }

    public function updateStatus(array $ids, bool $status): void
    {
        MobileBanner::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function getActiveBanners()
    {
        return MobileBanner::where('status', 1)
            ->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPaginatedBanners(int $perPage = 15)
    {
        return MobileBanner::orderBy('position')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}