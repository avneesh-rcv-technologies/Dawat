<?php

namespace Modules\MobileConfiguration\Repositories\Interfaces;

use Modules\MobileConfiguration\Models\MobileBanner;

interface MobileBannerRepositoryInterface
{
    public function create(array $data): MobileBanner;

    public function update(MobileBanner $banner, array $data): MobileBanner;

    public function delete(MobileBanner $banner): void;

    public function bulkDelete(array $ids): void;

    public function updateStatus(array $ids, bool $status): void;

    public function getActiveBanners();

    public function getPaginatedBanners(int $perPage = 15);
}