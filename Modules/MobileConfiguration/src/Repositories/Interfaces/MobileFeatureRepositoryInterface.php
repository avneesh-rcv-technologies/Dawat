<?php

namespace Modules\MobileConfiguration\Repositories\Interfaces;

use Modules\MobileConfiguration\Models\MobileFeature;

interface MobileFeatureRepositoryInterface
{
    public function create(array $data): MobileFeature;

    public function update(MobileFeature $feature, array $data): MobileFeature;

    public function delete(MobileFeature $feature): void;

    public function bulkDelete(array $ids): void;

    public function updateStatus(array $ids, bool $status): void;

    public function getActiveFeatures();

    public function getPaginatedFeatures(int $perPage = 15);

    public function getBySlug(string $slug): ?MobileFeature;

    public function slugExists(string $slug, ?int $excludeId = null): bool;
}