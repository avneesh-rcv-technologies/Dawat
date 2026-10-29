<?php

namespace Modules\MobileConfiguration\Repositories;

use Modules\MobileConfiguration\Models\MobileWalkthrough;
use Modules\MobileConfiguration\Repositories\Interfaces\MobileWalkthroughRepositoryInterface;

class MobileWalkthroughRepository implements MobileWalkthroughRepositoryInterface
{
    public function create(array $data): MobileWalkthrough
    {
        return MobileWalkthrough::create($data);
    }

    public function update(MobileWalkthrough $walkthrough, array $data): MobileWalkthrough
    {
        $walkthrough->update($data);
        return $walkthrough;
    }

    public function delete(MobileWalkthrough $walkthrough): void
    {
        $walkthrough->delete();
    }

    public function bulkDelete(array $ids): void
    {
        MobileWalkthrough::whereIn('id', $ids)->delete();
    }

    public function updateStatus(array $ids, bool $status): void
    {
        MobileWalkthrough::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function getActiveWalkthroughs()
    {
        return MobileWalkthrough::where('status', 1)
            ->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPaginatedWalkthroughs(int $perPage = 15)
    {
        return MobileWalkthrough::orderBy('position')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}