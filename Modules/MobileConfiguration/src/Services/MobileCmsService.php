<?php

namespace Modules\MobileConfiguration\Services;

use Illuminate\Http\Request;
use Modules\MobileConfiguration\Models\MobileCms;
use Modules\MobileConfiguration\Repositories\Interfaces\MobileCmsRepositoryInterface;

class MobileCmsService
{
    public function __construct(
        protected MobileCmsRepositoryInterface $repository
    ) {}

    public function create(Request $request): MobileCms
    {
        $data = $this->prepareData($request);
        $mobileCms = $this->repository->create($data);
        $this->handleImage($request, $mobileCms);
        return $mobileCms;
    }

    public function update(Request $request, MobileCms $mobileCms): MobileCms
    {
        $data = $this->prepareData($request);
        
        if ($request->hasFile('image')) {
            fn_upload_image(
                $request->file('image'),
                'mobile_cms',
                $mobileCms->id,
                true // Remove existing image
            );
        }

        return $this->repository->update($mobileCms, $data);
    }

    public function delete(MobileCms $mobileCms): void
    {
        // Delete associated images using helper
        $images = fn_get_images('mobile_cms', $mobileCms->id);
        if ($images && $images->count() > 0) {
            foreach ($images as $image) {
                // Delete individual files if needed
            }
        }
        
        $this->repository->delete($mobileCms);
    }

    public function bulkDelete(array $ids): void
    {
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
    }

    public function updateStatus(array $ids, bool $status): void
    {
        $this->repository->updateStatus($ids, $status);
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

    protected function prepareData(Request $request): array
    {
        $data = $request->only([
            'name',
            'cms_page_id',
            'status',
            'position',
        ]);
        $data['status'] = $request->boolean('status');
        $data['position'] = $data['position'] ?? 0; // Default to 0 if not provided
        return $data;
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