<?php

namespace Modules\MobileConfiguration\Repositories\Interfaces;

use Modules\MobileConfiguration\Models\MobileHomepageLayout;

interface MobileHomepageLayoutRepositoryInterface
{
    public function create(array $data): MobileHomepageLayout;
    public function update(MobileHomepageLayout $layout, array $data): MobileHomepageLayout;
    public function delete(MobileHomepageLayout $layout): void;
    public function bulkDelete(array $ids): void;
    public function updateStatus(array $ids, bool $status): void;
    public function getActiveLayouts();
    public function getPaginatedLayouts(int $perPage = 15);
    public function getBySlug(string $slug): ?MobileHomepageLayout;
    public function getDefaultLayout(): ?MobileHomepageLayout;
    public function setAsDefault(int $id): void;

    /**
     * Retrieve all layout slugs (used for cache invalidation).
     *
     * @return string[]
     */
    public function getAllSlugs(): array;
}