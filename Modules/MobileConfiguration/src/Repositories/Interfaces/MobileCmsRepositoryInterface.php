<?php

namespace Modules\MobileConfiguration\Repositories\Interfaces;

use Modules\MobileConfiguration\Models\MobileCms;

interface MobileCmsRepositoryInterface
{
    public function create(array $data): MobileCms;

    public function update(MobileCms $mobileCms, array $data): MobileCms;

    public function delete(MobileCms $mobileCms): void;

    public function bulkDelete(array $ids): void;

    public function updateStatus(array $ids, bool $status): void;

    public function getActiveCms();

    public function getPaginatedCms(int $perPage = 15);

    public function findById(int $id): ?MobileCms;
}