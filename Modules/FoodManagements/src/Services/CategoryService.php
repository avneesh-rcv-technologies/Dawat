<?php

namespace Modules\FoodManagements\Services;

use Illuminate\Support\Str;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Repositories\FoodCategoryRepository;

class CategoryService
{
    protected FoodCategoryRepository $repository;

    public function __construct(FoodCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data)
    {
        // Generate unique slug
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        if (isset($data['image'])) {
            $data['image'] = $this->storeImage($data['image']);
        }

        return $this->repository->create($data);
    }

    public function find(int $id)
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data)
    {
        // Generate unique slug if name is being updated
        if (isset($data['name'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $id);
        }

        if (isset($data['image'])) {
            $data['image'] = $this->storeImage($data['image']);
        }
        return $this->repository->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->repository->delete($id);
    }

    public function bulkDelete(array $ids)
    {
        return $this->repository->bulkDelete($ids);
    }

    public function bulkStatusUpdate(array $ids, bool $status): void
    {
        $this->repository->bulkStatusUpdate($ids, $status);
    }

    public function getParentCategories(?int $restaurantId = null)
    {
        return $this->repository->getRootActive($restaurantId);
    }

    /**
     * Get active root categories with their subcategories
     */
    public function getActiveCategoriesWithSubcategories(?int $restaurantId = null)
    {
        return $this->repository->getActiveCategoriesWithSubcategories($restaurantId);
    }

    /**
     * Get complete menu structure optimized for frontend
     */
    public function getCompleteMenu(?string $foodType = null, bool $includeItems = false)
    {
        return $this->repository->getCompleteMenu($foodType, $includeItems);
    }

    /* ================= PRIVATE ================= */

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (
            FoodCategory::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    private function storeImage($image): string
    {
        return $image->store('food_categories', 'public');
    }
}
