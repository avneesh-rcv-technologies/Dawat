<?php

namespace Modules\Cms\Repositories\Interfaces;

use Modules\Cms\Models\CmsPage;

interface CmsPageRepositoryInterface
{
    public function create(array $data): CmsPage;

    public function update(CmsPage $page, array $data): CmsPage;

    public function delete(CmsPage $page): void;

    public function bulkDelete(array $ids): void;

    public function updateStatus(array $ids, bool $status): void;

    public function getActivePages();

    public function getPaginatedPages(int $perPage = 15);

    public function getBySlug(string $slug): ?CmsPage;

    public function slugExists(string $slug, ?int $excludeId = null): bool;
}