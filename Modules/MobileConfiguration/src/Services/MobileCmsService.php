<?php

namespace Modules\MobileConfiguration\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Cms\Models\CmsPage;
use Modules\MobileConfiguration\Models\MobileCms;
use Modules\MobileConfiguration\Repositories\Interfaces\MobileCmsRepositoryInterface;

class MobileCmsService
{
    public function __construct(
        protected MobileCmsRepositoryInterface $repository
    ) {}

    /**
     * Create the CMS page and its mobile entry in one step.
     */
    public function create(Request $request): MobileCms
    {
        $mobileCms = DB::transaction(function () use ($request) {
            $page = CmsPage::create($this->preparePageData($request));

            return $this->repository->create($this->prepareData($request, $page));
        });

        $this->handleImage($request, $mobileCms);

        return $mobileCms;
    }

    public function update(Request $request, MobileCms $mobileCms): MobileCms
    {
        $mobileCms = DB::transaction(function () use ($request, $mobileCms) {
            $page = $mobileCms->cmsPage;
            $pageData = $this->preparePageData($request);

            if ($page) {
                $page->update($pageData);
            } else {
                $page = CmsPage::create($pageData);
            }

            return $this->repository->update($mobileCms, $this->prepareData($request, $page));
        });

        $this->handleImage($request, $mobileCms);

        return $mobileCms;
    }

    public function delete(MobileCms $mobileCms): void
    {
        $pageId = $mobileCms->cms_page_id;

        // Delete associated images using helper
        $images = fn_get_images('mobile_cms', $mobileCms->id);
        if ($images && $images->count() > 0) {
            foreach ($images as $image) {
                // Delete individual files if needed
            }
        }
        
        $this->repository->delete($mobileCms);
        $this->deleteUnusedPages([$pageId]);
    }

    public function bulkDelete(array $ids): void
    {
        $pageIds = MobileCms::whereIn('id', $ids)->pluck('cms_page_id')->all();

        foreach ($ids as $id) {
            // Delete associated images for each mobile cms
            $images = fn_get_images('mobile_cms', $id);
            if ($images && $images->count() > 0) {
                foreach ($images as $image) {
                    // Delete individual files if needed
                }
            }
        }
        $this->repository->bulkDelete($ids);
        $this->deleteUnusedPages($pageIds);
    }

    public function updateStatus(array $ids, bool $status): void
    {
        $this->repository->updateStatus($ids, $status);

        // Keep the page served by the CMS API in step with the mobile entry.
        $pageIds = MobileCms::whereIn('id', $ids)->pluck('cms_page_id')->filter()->all();
        CmsPage::whereIn('id', $pageIds)->update(['status' => $status]);
    }

    public function getActiveCms()
    {
        return $this->repository->getActiveCms();
    }

    public function getPaginatedCms(int $perPage = 15)
    {
        return $this->repository->getPaginatedCms($perPage);
    }

    public function findById(int $id): ?MobileCms
    {
        return $this->repository->findById($id);
    }

    protected function prepareData(Request $request, CmsPage $page): array
    {
        return [
            'name' => $request->input('page_title'),
            'cms_page_id' => $page->id,
            'status' => $request->boolean('status'),
            'position' => (int) ($request->input('position') ?? 0),
        ];
    }

    protected function preparePageData(Request $request): array
    {
        $data = $request->only([
            'page_title',
            'description',
            'meta_title',
            'meta_description',
            'meta_keywords',
        ]);
        $data['status'] = $request->boolean('status');

        // An empty slug lets CmsPage generate a unique one from the title.
        if ($request->filled('slug')) {
            $data['slug'] = $request->input('slug');
        }

        return $data;
    }

    /**
     * Pages are owned by their mobile entry, so remove them once nothing points at them.
     */
    protected function deleteUnusedPages(array $pageIds): void
    {
        $pageIds = array_filter($pageIds);
        if (!$pageIds) {
            return;
        }

        $stillUsed = MobileCms::whereIn('cms_page_id', $pageIds)->pluck('cms_page_id')->all();
        CmsPage::whereIn('id', array_diff($pageIds, $stillUsed))->delete();
    }

    protected function handleImage(Request $request, MobileCms $mobileCms): void
    {
        if ($request->hasFile('image')) {
            fn_upload_image(
                $request->file('image'),
                'mobile_cms',
                $mobileCms->id,
                true // Remove existing image
            );
        }
    }
}