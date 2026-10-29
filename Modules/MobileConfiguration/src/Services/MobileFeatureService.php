<?php

namespace Modules\MobileConfiguration\Services;

use Illuminate\Http\Request;
use Modules\MobileConfiguration\Models\MobileFeature;
use Modules\MobileConfiguration\Repositories\Interfaces\MobileFeatureRepositoryInterface;
// use Modules\Catalog\Services\CategoryService;
use Modules\Catalog\Services\ProductService;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

class MobileFeatureService
{
    public function __construct(
        protected MobileFeatureRepositoryInterface $repository,
        // protected CategoryService $categoryService,
        // protected ProductService $productService
    ) {
    }

    public function create(Request $request): MobileFeature
    {
        $data = $this->prepareData($request);
        return $this->repository->create($data);
    }

    public function update(Request $request, MobileFeature $feature): MobileFeature
    {
        $data = $this->prepareData($request, $feature->id);
        return $this->repository->update($feature, $data);
    }

    public function delete(MobileFeature $feature): void
    {
        $this->repository->delete($feature);
    }

    public function bulkDelete(array $ids): void
    {
        $this->repository->bulkDelete($ids);
    }

    public function updateStatus(array $ids, bool $status): void
    {
        $this->repository->updateStatus($ids, $status);
    }

    public function getActiveFeatures()
    {
        return $this->repository->getActiveFeatures();
    }

    public function getPaginatedFeatures(int $perPage = 15)
    {
        return $this->repository->getPaginatedFeatures($perPage);
    }

    public function getBySlug(string $slug): ?MobileFeature
    {
        return $this->repository->getBySlug($slug);
    }

    public function getFeatureWithItems(string $slug): ?array
    {
        $feature = $this->repository->getBySlug($slug);

        if (!$feature || !$feature->status) {
            return null;
        }

        return [
            'feature' => $feature,
            'items' => $this->getFeatureItems($feature)
        ];
    }

    /**
     * Get feature items using proper Catalog services
     */
    public function getFeatureItems(MobileFeature $feature)
    {
        if ($feature->selection_type === 'custom' && !empty($feature->custom_items)) {
            if ($feature->type === 'product') {
                return $this->getCustomProducts($feature->custom_items);
            } else {
                return $this->getCustomCategories($feature->custom_items);
            }
        }

        // For newest/oldest
        if ($feature->type === 'product') {
            return $this->getProductsBySelection($feature->selection_type, $feature->items_count ?? 10);
        } else {
            return $this->getCategoriesBySelection($feature->selection_type, $feature->items_count ?? 10);
        }
    }

    /**
     * Get custom selected products with descriptions
     */
    protected function getCustomProducts(array $productIds)
    {
        return Product::with([
            'description' => function ($query) {
                $query->where('language_code', 'en');
            }
        ])
            ->whereIn('id', $productIds)
            ->where('status', 'A')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->description->name ?? 'Unnamed Product',
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'status' => $product->status,
                    'type' => 'product',
                    'images' => collect(fn_get_images('product', $product->id))->pluck('url')->toArray(),
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });
    }

    /**
     * Get custom selected categories
     */
    protected function getCustomCategories(array $categoryIds)
    {
        return Category::whereIn('id', $categoryIds)
            ->where('status', 'A')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'status' => $category->status,
                    'type' => 'category',
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ];
            });
    }

    /**
     * Get products by selection type (newest/oldest)
     */
    protected function getProductsBySelection(string $selectionType, int $limit)
    {
        $query = Product::with([
            'description' => function ($query) {
                $query->where('language_code', 'en');
            }
        ])
            ->where('status', 'A');

        if ($selectionType === 'newest') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('created_at', 'asc');
        }

        return $query->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->description->name ?? 'Unnamed Product',
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'status' => $product->status,
                    'type' => 'product',
                   'images' => collect(fn_get_images('product', $product->id))->pluck('url')->toArray(),
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });
    }

    /**
     * Get categories by selection type (newest/oldest)
     */
    protected function getCategoriesBySelection(string $selectionType, int $limit)
    {
        $query = Category::where('status', 'A');

        if ($selectionType === 'newest') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('created_at', 'asc');
        }

        return $query->limit($limit)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'status' => $category->status,
                    'type' => 'category',
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ];
            });
    }

    protected function prepareData(Request $request, ?int $excludeId = null): array
    {
        $data = $request->only([
            'name',
            'slug',
            'used_for',
            'type',
            'selection_type',
            'items_count',
            'position',
            'status',
        ]);

        // Convert status to boolean
        $data['status'] = $request->boolean('status');

        // Handle items_count for custom selection
        if ($request->selection_type === 'custom') {
            $data['items_count'] = 0; // Set to 0 for custom selection
        } else {
            $data['items_count'] = $request->input('items_count', 10);
        }

        // Handle custom items
        if ($request->selection_type === 'custom') {
            $customItems = [];
            if ($request->type === 'product') {
                $customItems = $request->input('custom_products', []);
            } else {
                $customItems = $request->input('custom_categories', []);
            }
            $data['custom_items'] = array_filter($customItems);
        } else {
            $data['custom_items'] = null;
        }

        return $data;
    }
}