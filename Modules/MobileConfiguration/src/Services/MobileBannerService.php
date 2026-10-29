<?php

namespace Modules\MobileConfiguration\Services;

use Illuminate\Http\Request;
use Modules\MobileConfiguration\Models\MobileBanner;
use Modules\MobileConfiguration\Repositories\Interfaces\MobileBannerRepositoryInterface;

class MobileBannerService
{
    public function __construct(
        protected MobileBannerRepositoryInterface $repository
    ) {}

    public function create(Request $request): MobileBanner
    {
        $data = $this->prepareData($request);
        $banner = $this->repository->create($data);
        $this->handleImage($request, $banner);
        return $banner;
    }

    public function update(Request $request, MobileBanner $banner): MobileBanner
    {
        $data = $this->prepareData($request);
        
        if ($request->hasFile('image')) {
            fn_upload_image(
                $request->file('image'),
                'mobile_banner',
                $banner->id,
                true // Remove existing image
            );
        }

        return $this->repository->update($banner, $data);
    }

    public function delete(MobileBanner $banner): void
    {
        // Delete associated images using helper
        $images = fn_get_images('mobile_banner', $banner->id);
        if ($images && $images->count() > 0) {
            foreach ($images as $image) {
                // Delete individual files if needed
            }
        }
        
        $this->repository->delete($banner);
    }

    public function bulkDelete(array $ids): void
    {
        foreach ($ids as $id) {
            // Delete associated images for each banner
            $images = fn_get_images('mobile_banner', $id);
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

    public function getActiveBanners()
    {
        return $this->repository->getActiveBanners();
    }

    public function getPaginatedBanners(int $perPage = 15)
    {
        return $this->repository->getPaginatedBanners($perPage);
    }

    protected function prepareData(Request $request): array
    {
        $data = $request->only([
            'name',
            'type',
            'type_value',
            'status',
            'position',
        ]);

        // Convert status to boolean
        $data['status'] = $request->boolean('status');

        // Handle type_value based on type
        if ($data['type'] === 'no_link') {
            $data['type_value'] = null;
        }

        return $data;
    }

    protected function handleImage(Request $request, MobileBanner $banner): void
    {
        if ($request->hasFile('image')) {
            fn_upload_image(
                $request->file('image'),
                'mobile_banner',
                $banner->id,
                true // Remove existing image
            );
        }
    }
}