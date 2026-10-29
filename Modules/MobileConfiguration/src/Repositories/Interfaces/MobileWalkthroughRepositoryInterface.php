<?php

namespace Modules\MobileConfiguration\Repositories\Interfaces;

use Modules\MobileConfiguration\Models\MobileWalkthrough;

interface MobileWalkthroughRepositoryInterface
{
    public function create(array $data): MobileWalkthrough;

    public function update(MobileWalkthrough $walkthrough, array $data): MobileWalkthrough;

    public function delete(MobileWalkthrough $walkthrough): void;

    public function bulkDelete(array $ids): void;

    public function updateStatus(array $ids, bool $status): void;

    public function getActiveWalkthroughs();

    public function getPaginatedWalkthroughs(int $perPage = 15);
}