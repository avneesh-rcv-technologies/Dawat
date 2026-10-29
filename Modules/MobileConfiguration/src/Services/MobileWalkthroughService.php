<?php

namespace Modules\MobileConfiguration\Services;

use Illuminate\Http\Request;
use Modules\MobileConfiguration\Models\MobileWalkthrough;
use Modules\MobileConfiguration\Repositories\Interfaces\MobileWalkthroughRepositoryInterface;

class MobileWalkthroughService
{
    public function __construct(
        protected MobileWalkthroughRepositoryInterface $repository
    ) {}

    public function create(Request $request): MobileWalkthrough
    {
        $data = $this->prepareData($request);
        $walkthrough = $this->repository->create($data);
        $this->handleImage($request, $walkthrough);
        return $walkthrough;
    }

    public function update(Request $request, MobileWalkthrough $walkthrough): MobileWalkthrough
    {
        $data = $this->prepareData($request);
        
        if ($request->hasFile('image')) {
            fn_upload_image(
                $request->file('image'),
                'mobile_walkthrough',
                $walkthrough->id,
                true // Remove existing image
            );
        }

        return $this->repository->update($walkthrough, $data);
    }

    public function delete(MobileWalkthrough $walkthrough): void
    {
        // Delete associated images using helper
        $images = fn_get_images('mobile_walkthrough', $walkthrough->id);
        if ($images && $images->count() > 0) {
            foreach ($images as $image) {
                // Delete individual files if needed
            }
        }
        
        $this->repository->delete($walkthrough);
    }

    public function bulkDelete(array $ids): void
    {
        foreach ($ids as $id) {
            // Delete associated images for each walkthrough
            $images = fn_get_images('mobile_walkthrough', $id);
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

    public function getActiveWalkthroughs()
    {
        return $this->repository->getActiveWalkthroughs();
    }

    public function getPaginatedWalkthroughs(int $perPage = 15)
    {
        return $this->repository->getPaginatedWalkthroughs($perPage);
    }

    protected function prepareData(Request $request): array
    {
        $data = $request->only([
            'title',
            'description',
            'status',
            'position',
        ]);

        // Convert status to boolean
        $data['status'] = $request->boolean('status');

        return $data;
    }

    protected function handleImage(Request $request, MobileWalkthrough $walkthrough): void
    {
        if ($request->hasFile('image')) {
            fn_upload_image(
                $request->file('image'),
                'mobile_walkthrough',
                $walkthrough->id,
                true // Remove existing image
            );
        }
    }
}