<?php

namespace Modules\Cms\Repositories;

use Modules\Cms\Models\CmsPage;
use Modules\Cms\Repositories\Interfaces\CmsPageRepositoryInterface;

class CmsPageRepository implements CmsPageRepositoryInterface
{
    public function create(array $data): CmsPage
    {
        return CmsPage::create($data);
    }

    public function update(CmsPage $page, array $data): CmsPage
    {
        $page->update($data);
        return $page;
    }

    public function delete(CmsPage $page): void
    {
        $page->delete();
    }

    public function bulkDelete(array $ids): void
    {
        CmsPage::whereIn('id', $ids)->delete();
    }

    public function updateStatus(array $ids, bool $status): void
    {
        CmsPage::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function getActivePages()
    {
        return CmsPage::where('status', 1)
            ->orderBy('page_title')
            ->get();
    }

    public function getPaginatedPages(int $perPage = 15)
    {
        return CmsPage::orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getBySlug(string $slug): ?CmsPage
    {
        return CmsPage::where('slug', $slug)->first();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        return CmsPage::where('slug', $slug)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}