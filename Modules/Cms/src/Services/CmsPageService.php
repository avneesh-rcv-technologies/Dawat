<?php

namespace Modules\Cms\Services;

use Illuminate\Http\Request;
use Modules\Cms\Models\CmsPage;
use Modules\Cms\Repositories\Interfaces\CmsPageRepositoryInterface;

class CmsPageService
{
    public function __construct(
        protected CmsPageRepositoryInterface $repository
    ) {}

    public function create(Request $request): CmsPage
    {
        $data = $this->prepareData($request);
        return $this->repository->create($data);
    }

    public function update(Request $request, CmsPage $page): CmsPage
    {
        $data = $this->prepareData($request, $page->id);
        return $this->repository->update($page, $data);
    }

    public function delete(CmsPage $page): void
    {
        $this->repository->delete($page);
    }

    public function bulkDelete(array $ids): void
    {
        $this->repository->bulkDelete($ids);
    }

    public function updateStatus(array $ids, bool $status): void
    {
        $this->repository->updateStatus($ids, $status);
    }

    public function getActivePages()
    {
        return $this->repository->getActivePages();
    }

    public function getPaginatedPages(int $perPage = 15)
    {
        return $this->repository->getPaginatedPages($perPage);
    }

    public function getBySlug(string $slug): ?CmsPage
    {
        return $this->repository->getBySlug($slug);
    }

    protected function prepareData(Request $request, ?int $excludeId = null): array
    {
        $data = $request->only([
            'page_title',
            'description',
            'slug',
            'status',
            'meta_title',
            'meta_description',
            'meta_keywords',
        ]);

        // Convert status to boolean
        $data['status'] = $request->boolean('status');

        return $data;
    }
}