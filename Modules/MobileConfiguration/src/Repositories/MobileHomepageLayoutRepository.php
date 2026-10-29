<?php

namespace Modules\MobileConfiguration\Repositories;

use Modules\MobileConfiguration\Models\MobileHomepageLayout;
use Modules\MobileConfiguration\Repositories\Interfaces\MobileHomepageLayoutRepositoryInterface;

class MobileHomepageLayoutRepository implements MobileHomepageLayoutRepositoryInterface
{
    public function create(array $data): MobileHomepageLayout
    {
        return MobileHomepageLayout::create($data);
    }

    public function update(MobileHomepageLayout $layout, array $data): MobileHomepageLayout
    {
        $layout->update($data);
        return $layout;
    }

    public function delete(MobileHomepageLayout $layout): void
    {
        $layout->delete();
    }

    public function bulkDelete(array $ids): void
    {
        MobileHomepageLayout::whereIn('id', $ids)->delete();
    }

    public function updateStatus(array $ids, bool $status): void
    {
        MobileHomepageLayout::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function getActiveLayouts()
    {
        return MobileHomepageLayout::active()
            ->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPaginatedLayouts(int $perPage = 15)
    {
        return MobileHomepageLayout::orderBy('position')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getBySlug(string $slug): ?MobileHomepageLayout
    {
        return MobileHomepageLayout::where('slug', $slug)->active()->first();
    }

    public function getDefaultLayout(): ?MobileHomepageLayout
    {
        return MobileHomepageLayout::default()->active()->first();
    }

    public function setAsDefault(int $id): void
    {
        // Remove default from all layouts
        MobileHomepageLayout::where('is_default', true)->update(['is_default' => false]);
        
        // Set new default
        MobileHomepageLayout::where('id', $id)->update(['is_default' => true]);
    }

    public function getAllSlugs(): array
    {
        return MobileHomepageLayout::pluck('slug')->toArray();
    }
}