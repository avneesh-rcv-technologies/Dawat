<?php

namespace Modules\FoodManagements\Repositories;

use Modules\FoodManagements\Models\FoodItem;
use Modules\FoodManagements\Services\FoodItemFeatureData;

class FoodItemRepository
{
    public function getAll()
    {
        return FoodItem::latest();
    }

    public function findById(int $id): FoodItem
    {
        return FoodItem::with([
            'categories.parent', // 👈 IMPORTANT
            'tags',
            'optionGroups.options',
            'servingOptions',
        ])->findOrFail($id);
    }

    public function create(array $data): FoodItem
    {
        return FoodItem::create($data);
    }

    public function update(int $id, array $data): FoodItem
    {
        $item = FoodItem::findOrFail($id);
        $item->update($data);

        return $item->load([
            'categories.parent',
            'tags',
            'optionGroups.options',
        ]);
    }

    public function delete(int $id): bool
    {
        return FoodItem::where('id', $id)->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return FoodItem::whereIn('id', $ids)->delete();
    }

    public function toggleStatus(array $ids, bool $status): int
    {
        return FoodItem::whereIn('id', $ids)
            ->update(['is_active' => $status]);
    }

     /**
     * Get food items by category with filters and pagination
     */
    public function getFoodItemsByCategory(int $categoryId, array $filters = [], int $perPage = 20, int $page = 1)
    {
        // Collect the category + all its children IDs
        $childIds = \Modules\FoodManagements\Models\FoodCategory::where('parent_id', $categoryId)
            ->pluck('id')
            ->toArray();
        $allCategoryIds = array_merge([$categoryId], $childIds);

        $query = FoodItem::whereHas('categories', function ($q) use ($allCategoryIds) {
            $q->whereIn('food_categories.id', $allCategoryIds);
        })
        ->where('is_active', true)
        ->where('is_available', true)
        ->where('in_stock', true)
        ->with(['tags', 'categories', 'servingOptions', ...FoodItemFeatureData::RELATIONS]);

        // Filter by restaurant via category
        if (!empty($filters['restaurant_id'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('food_categories.restaurant_id', $filters['restaurant_id']);
            });
        }

        // Filter by food type
        if (!empty($filters['food_type']) && $filters['food_type'] !== 'all') {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('food_type', $filters['food_type'])
                  ->orWhere('food_type', 'all');
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('sale_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('sale_price', '<=', $filters['max_price']);
        }

        if (!empty($filters['tags'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('food_tags.id', $filters['tags']);
            });
        }

        // Filter by feature variant (e.g. size=Large, spice=Medium)
        if (!empty($filters['variant_id'])) {
            $query->whereHas('featureValues', function ($q) use ($filters) {
                $q->where('variant_id', $filters['variant_id']);
            });
        }

        $paginator = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => collect($paginator->items())->map(function ($item) {
                return $this->formatFoodItem($item);
            }),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'next_page' => $paginator->nextPageUrl(),
                'prev_page' => $paginator->previousPageUrl(),
            ]
        ];
    }

    /**
     * Get food items by tag with filters and pagination
     */
    public function getFoodItemsByTag(int $tagId, array $filters = [], int $perPage = 20, int $page = 1)
    {
        $query = FoodItem::whereHas('tags', function ($q) use ($tagId) {
            $q->where('food_tags.id', $tagId);
        })
        ->where('is_active', true)
        ->where('is_available', true)
        ->where('in_stock', true)
        ->with(['tags', 'categories', 'servingOptions', ...FoodItemFeatureData::RELATIONS]);

        // Apply filters
        if (!empty($filters['food_type']) && $filters['food_type'] !== 'all') {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('food_type', $filters['food_type'])
                  ->orWhere('food_type', 'all');
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('sale_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('sale_price', '<=', $filters['max_price']);
        }

        $paginator = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => collect($paginator->items())->map(function ($item) {
                return $this->formatFoodItem($item);
            }),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'next_page' => $paginator->nextPageUrl(),
                'prev_page' => $paginator->previousPageUrl(),
            ]
        ];
    }

    /**
     * Get food items by one or more dietary label ids.
     */
    public function getFoodItemsByDietaryLabels(array $dietaryLabelIds = [], array $filters = [], int $perPage = 20, int $page = 1)
    {
        $query = FoodItem::query()
            ->where('is_active', true)
            ->where('is_available', true)
            ->where('in_stock', true)
            ->with(['tags', 'categories', 'servingOptions', ...FoodItemFeatureData::RELATIONS]);

        if (!empty($filters['restaurant_id'])) {
            $query->where('restaurant_id', (int) $filters['restaurant_id']);
        }

        if (!empty($filters['food_type']) && $filters['food_type'] !== 'all') {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('food_type', $filters['food_type'])
                  ->orWhere('food_type', 'all');
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('sale_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('sale_price', '<=', $filters['max_price']);
        }

        if (!empty($filters['tags'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('food_tags.id', $filters['tags']);
            });
        }

        if (!empty($dietaryLabelIds)) {
            $query->where(function ($q) use ($dietaryLabelIds) {
                foreach ($dietaryLabelIds as $id) {
                    $q->orWhereJsonContains('dietary_label_ids', (string) $id);
                }
            });
        }

        $paginator = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => collect($paginator->items())->map(function ($item) {
                return $this->formatFoodItem($item);
            }),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'next_page' => $paginator->nextPageUrl(),
                'prev_page' => $paginator->previousPageUrl(),
            ]
        ];
    }

    /**
     * Format food item for API response
     */
    private function formatFoodItem($item)
    {
        $singleImage = fn_get_image('food_image_single', $item->id);
        $galleryImage = fn_get_image('food_image', $item->id);

        return [
            'id' => $item->id,
            'restaurant_id' => $item->restaurant_id,
            'name' => $item->name,
            'slug' => $item->slug,
            'description' => $item->description,
            'tax_percentage' => $item->tax_percentage,
            'dietary_label_ids' => $item->dietary_label_ids ?? [],
            'features' => FoodItemFeatureData::forItem($item, public: true),
            'primary_image' => $galleryImage['url'] ?? $singleImage['url'] ?? '',
            'image' => $item->image ? array_merge($item->image, ['object_type' => 'food_image']) : null,
            'images' => $item->images ?? [],
            'tags' => $item->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ];
            })->values()->all(),
            'categories' => $item->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image_url' => fn_get_image('category_image', $category->id)['url'] ?? $category->image_url ?? null,
                ];
            })->values()->all(),
            'serving_options' => $item->servingOptions->map(function ($option) {
                return $this->formatServingOption($option);
            })->values()->all(),
        ];
    }

    private function getCurrencySymbol(): string
    {
        return fn_format_default_currency() ?: '$';
    }

    private function formatServingOption($option): array
    {
        $basePrice = (float) ($option->price ?? 0);
        $discountPrice = $option->discount_price !== null && $option->discount_price !== ''
            ? (float) $option->discount_price
            : null;
        $unitPrice = $discountPrice !== null && $discountPrice > 0 ? $discountPrice : $basePrice;
        $serveType = $option->serve_type ?? 'package';
        $title = $option->serving_name ?: ucfirst(str_replace('_', ' ', $serveType));
        $perPersonPrice = $serveType === 'per_person' ? $unitPrice : ($option->serves > 0 ? round($unitPrice / $option->serves, 2) : $unitPrice);
        $currencySymbol = $this->getCurrencySymbol();
        $displayPrice = $currencySymbol.number_format($unitPrice, 2, '.', '');

        $displayMeta = $this->buildServingDisplayMeta($option, $serveType, $unitPrice, $discountPrice, $perPersonPrice, $displayPrice);

        return [
            'id' => $option->id,
            'food_item_id' => $option->food_item_id,
            'serve_type' => $serveType,
            'serve_type_label' => ucfirst(str_replace('_', ' ', $serveType)),
            'serving_name' => $option->serving_name,
            'quantity' => $option->quantity,
            'unit' => $option->unit,
            'serves' => $option->serves,
            'min_guests' => $option->min_guests,
            'max_guests' => $option->max_guests,
            'price' => (float) $basePrice,
            'discount_price' => $discountPrice !== null ? (float) $discountPrice : null,
            'is_available' => (bool) $option->is_available,
            'sort_order' => $option->sort_order,
            'display' => [
                'title' => $displayMeta['title'],
                'subtitle' => $displayMeta['subtitle'],
                'price_label' => $displayMeta['price_label'],
                'unit_label' => $displayMeta['unit_label'],
                'unit_price_text' => $displayMeta['unit_price_text'],
                'per_person_price' => $this->formatPriceValue($perPersonPrice),
            ],
        ];
    }

    private function buildServingDisplayMeta($option, string $serveType, float $unitPrice, ?float $discountPrice, float $perPersonPrice, string $displayPrice): array
    {
        $title = $option->serving_name ?: ($serveType === 'per_person' ? 'Per guest' : ($serveType === 'package' ? 'Catering order' : ucfirst(str_replace('_', ' ', $serveType))));

        $subtitleParts = [];
        if ($serveType === 'per_person') {
            $subtitle = 'Per guest';
        } else {
            if ($option->serves) {
                $subtitleParts[] = 'Serves '.$option->serves;
            }
            if ($option->quantity !== null && $option->quantity !== '' && $option->unit) {
                $subtitleParts[] = $option->quantity.' '.$option->unit;
            }
            if ($option->unit && ($option->quantity === null || $option->quantity === '')) {
                $subtitleParts[] = $option->unit;
            }

            $subtitle = $subtitleParts ? implode(' • ', $subtitleParts) : 'Price per serving';
        }
        $priceLabel = $discountPrice !== null && $discountPrice > 0 ? 'Special offer' : ($serveType === 'package' ? 'Catering order' : ($serveType === 'per_person' ? 'Per guest' : 'Price'));

        $unitLabel = $serveType === 'per_person'
            ? 'Per guest'
            : ($option->serves ? 'Serves '.$option->serves : ($option->quantity !== null && $option->quantity !== '' && $option->unit ? $option->quantity.' '.$option->unit : ($option->unit ?: 'Each')));

        $quantityLabel = $serveType === 'per_person'
            ? ($option->serves ? $option->serves.' guests' : 'Per guest')
            : ($option->serves ? 'Serves '.$option->serves : ($option->quantity !== null && $option->quantity !== '' && $option->unit ? $option->quantity.' '.$option->unit : 'Standard serving'));

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'price_label' => $priceLabel,
            'unit_label' => $unitLabel,
            'unit_price_text' => $displayPrice,
        ];
    }

    private function formatPriceValue(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function calculateFinalPrice($basePrice, $taxPercentage = 0): float
    {
        $base = (float) ($basePrice ?? 0);
        $tax = (float) ($taxPercentage ?? 0);

        return round($base * (1 + ($tax / 100)), 2);
    }
}
