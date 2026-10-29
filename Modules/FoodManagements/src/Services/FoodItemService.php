<?php

namespace Modules\FoodManagements\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Modules\FoodManagements\Repositories\FoodItemRepository;
use Modules\FoodManagements\Models\FoodItem;

class FoodItemService
{
    protected FoodItemRepository $repository;

    public function __construct(FoodItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getById(int $id): FoodItem
    {
        return $this->repository->findById($id);
    }

    public function create(Request $request, array $data): FoodItem
    {
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['is_active'] = $data['is_active'] ?? false;
        $data['is_available'] = $data['is_available'] ?? true;
        $data['in_stock'] = $data['in_stock'] ?? true;
        $data['quantity_type'] = $data['quantity_type'] ?? 'manual';

        $foodItem = $this->repository->create($data);

        // Categories (parent OR child)
        if ($request->filled('categories')) {
            $foodItem->categories()->sync($request->categories);
        }

        if ($request->filled('tags')) {
            $foodItem->tags()->sync($request->tags);
        }

        if ($request->filled('option_groups')) {
            $foodItem->optionGroups()->sync($request->option_groups);
        }

        if ($request->hasFile('images')) {
            $this->handleImageUploads($request, $foodItem);
        }

        return $foodItem;
    }

    public function update(int $id, Request $request, array $data): FoodItem
    {
        if (isset($data['name'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $id);
        }

        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_available'] = $data['is_available'] ?? true;
        $data['in_stock'] = $data['in_stock'] ?? true;
        $data['quantity_type'] = $data['quantity_type'] ?? 'manual';

        return $this->repository->update($id, $data);
    }

    /* ================= IMAGE UPLOAD ================= */

    public function handleImageUploads(Request $request, FoodItem $foodItem): void
    {
        $images = $request->file('images');

        if (!$images) {
            return;
        }

        // Use file manager module to handle image uploads
        foreach ($images as $index => $image) {
            // This should integrate with your file manager module
            // For now, we'll store the image and let the file manager handle it
            $path = $image->store('food_items', 'public');
            
            // You may need to call file manager functions here
            // Example: fn_save_image('food_image', $foodItem->id, $path, $index === 0);
        }
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->repository->bulkDelete($ids);
    }

    public function bulkStatusUpdate(array $ids, bool $status): int
    {
        return $this->repository->toggleStatus($ids, $status);
    }

    /**
     * Get food items by category with filters and pagination
     */
    public function getFoodItemsByCategory(int $categoryId, array $filters = [], int $perPage = 20, int $page = 1)
    {
        return $this->repository->getFoodItemsByCategory($categoryId, $filters, $perPage, $page);
    }

    /**
     * Get food item with all details (images, categories, tags, option groups)
     */
    public function getFoodItemWithDetails(int $itemId)
    {
        $item = $this->repository->findById($itemId);
        $taxCalculation = $item->getTaxCalculation();

        return [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'description' => $item->description,
            'long_description' => $item->long_description,
            'sale_price' => $item->sale_price,
            'cost_price' => $item->cost_price,
            'list_price' => $item->list_price,
            'display_price' => $taxCalculation['display_price'],
            'tax_amount' => $taxCalculation['tax_amount'],
            'tax_rate' => $taxCalculation['tax_rate'],
            'is_tax_inclusive' => $taxCalculation['is_tax_inclusive'],
            'formatted_price' => $item->getFormattedPriceWithTax(),
            'in_stock' => $item->in_stock,
            'quantity_type' => $item->quantity_type,
            'quantity' => $item->quantity,
            'quantity_label' => $item->quantity_label,
            'formatted_quantity' => $item->formatted_quantity,
            'is_available' => $item->is_available,
            'is_active' => $item->is_active,
            'image' => fn_get_image('food_image', $item->id)['url'] ?? '',
            'categories' => $item->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'parent' => $category->parent ? [
                        'id' => $category->parent->id,
                        'name' => $category->parent->name,
                        'slug' => $category->parent->slug,
                    ] : null,
                ];
            }),
            'tags' => $item->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ];
            }),
            'option_groups' => $item->optionGroups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'is_required' => $group->is_required,
                    'min' => $group->min,
                    'max' => $group->max,
                    'options' => $group->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'name' => $option->name,
                            'price' => $option->price ?? 0,
                        ];
                    }),
                ];
            }),
        ];
    }

    /**
     * Get food items by tag with filters and pagination
     */
    public function getFoodItemsByTag(int $tagId, array $filters = [], int $perPage = 20, int $page = 1)
    {
        return $this->repository->getFoodItemsByTag($tagId, $filters, $perPage, $page);
    }

    /* ================= SLUG ================= */

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (
            FoodItem::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }
}
