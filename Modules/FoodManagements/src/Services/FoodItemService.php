<?php

namespace Modules\FoodManagements\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Filemanager\Exceptions\FileServiceException;
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
        return DB::transaction(function () use ($request, $data) {
            $data = $this->checkDietaryCertificates($data);
            [$attributes, $servingOptions, $categoryId] = $this->generalAttributes($data);
            $attributes['slug'] = $this->generateUniqueSlug($attributes['name']);
            $attributes['is_active'] = $attributes['is_active'] ?? false;
            $attributes['is_available'] = $attributes['is_available'] ?? true;
            $attributes['in_stock'] = $attributes['in_stock'] ?? true;
            $attributes['quantity_type'] = $attributes['quantity_type'] ?? 'manual';

            $foodItem = $this->repository->create($attributes);
            $this->saveGeneralRelations($foodItem, $request, $servingOptions, $categoryId);

            if ($request->filled('tags')) {
                $foodItem->tags()->sync($request->tags);
            }
            if ($request->filled('option_groups')) {
                $foodItem->optionGroups()->sync($request->option_groups);
            }
            if ($request->hasFile('images')) {
                $this->handleImageUploads($request, $foodItem);
            }

            return $foodItem->load('servingOptions');
        });
    }

    public function update(int $id, Request $request, array $data): FoodItem
    {
        return DB::transaction(function () use ($id, $request, $data) {
            $data = $this->checkDietaryCertificates($data, $this->getById($id));
            [$attributes, $servingOptions, $categoryId] = $this->generalAttributes($data);
            if (isset($attributes['name'])) {
                $attributes['slug'] = $this->generateUniqueSlug($attributes['name'], $id);
            }

            $foodItem = $this->repository->update($id, $attributes);
            $this->saveGeneralRelations($foodItem, $request, $servingOptions, $categoryId);

            return $foodItem->load('servingOptions');
        });
    }

    private function checkDietaryCertificates(array $data, ?FoodItem $item = null): array
    {
        $categoryRestaurant = isset($data['category_id'])
            ? \Modules\FoodManagements\Models\FoodCategory::whereKey($data['category_id'])->value('restaurant_id') : null;
        $restaurantId = $data['restaurant_id'] ?? $item?->restaurant_id ?? $categoryRestaurant;
        if ($restaurantId) {
            \Modules\Restaurant\Models\Restaurant::whereKey($restaurantId)->lockForUpdate()->firstOrFail();
            if ($categoryRestaurant && (int) $categoryRestaurant !== (int) $restaurantId) {
                throw \Illuminate\Validation\ValidationException::withMessages(['category_id' => 'Choose a category belonging to this restaurant.']);
            }
            $data['restaurant_id'] = $restaurantId;
        }
        app(\Modules\DietaryManagment\Services\LabelCertificateEligibility::class)
            ->assertAllowed($restaurantId ? (int) $restaurantId : null, $data['dietary_label_ids'] ?? $item?->dietary_label_ids ?? []);
        return $data;
    }

    private function generalAttributes(array $data): array
    {
        $servingOptions = $data['serving_options'] ?? null;
        $categoryId = $data['category_id'] ?? null;
        unset($data['serving_options'], $data['category_id'], $data['food_image'], $data['replace_images'], $data['remove_image_ids'], $data['features']);

        if ($servingOptions) {
            // Keep existing price consumers working with the first available serving option.
            $primary = collect($servingOptions)->firstWhere('is_available', true) ?? reset($servingOptions);
            $data['sale_price'] = $primary['discount_price'] ?? $primary['price'];
            $data['list_price'] = $primary['price'];
        }

        return [$data, $servingOptions, $categoryId];
    }

    private function saveGeneralRelations(FoodItem $foodItem, Request $request, ?array $servingOptions, $categoryId): void
    {
        if ($categoryId !== null) {
            $foodItem->categories()->sync([$categoryId]);
        } elseif ($request->filled('categories')) {
            $foodItem->categories()->sync($request->categories);
        }

        if ($servingOptions !== null) {
            $keptIds = [];
            foreach (array_values($servingOptions) as $position => $option) {
                $optionId = $option['id'] ?? null;
                unset($option['id']);
                $option['sort_order'] = $position;
                $option = app(ServingOptionConfiguration::class)->normalize($option);

                if ($optionId) {
                    // Scope updates to this item so another item's option cannot be changed.
                    $model = $foodItem->servingOptions()->findOrFail($optionId);
                    $model->update($option);
                } else {
                    $model = $foodItem->servingOptions()->create($option);
                }
                $keptIds[] = $model->id;
            }
            $foodItem->servingOptions()->whereNotIn('id', $keptIds)->delete();
        }

        if ($request->hasFile('food_image')) {
            $this->appendImages($foodItem, $request->file('food_image'), replace: $request->boolean('replace_images'));
        }
    }

    /* ================= IMAGE UPLOAD ================= */

    public function handleImageUploads(Request $request, FoodItem $foodItem): void
    {
        if ($request->hasFile('images')) {
            $this->appendImages($foodItem, $request->file('images'), 'images');
        }
    }

    public function appendImages(FoodItem $foodItem, array $images, string $field = 'food_image', bool $replace = false): void
    {
        // Validate the batch before adding files; existing gallery images are preserved.
        FoodImageUploadPolicy::validateFiles($images, $field);
        if ($images === []) {
            return;
        }
        DB::transaction(function () use ($foodItem, $images, $field, $replace) {
            FoodItem::whereKey($foodItem->id)->lockForUpdate()->firstOrFail();
            $links = \Modules\Filemanager\Models\FileLink::with('file')->where('object_type', 'food_image')
                ->where('object_id', $foodItem->id)->lockForUpdate()->get();
            FoodImageUploadPolicy::assertCount($replace ? 0 : $links->count(), count($images), $field);
            $position = $replace ? 0 : ((int) ($links->max('position') ?? -1)) + 1;
            $files = app(\Modules\Filemanager\Services\FileService::class);
            // Check the entire batch before writing any files or removing old links.
            try {
                foreach ($images as $image) {
                    $files->assertValidUpload($image);
                }
                foreach (array_values($images) as $image) {
                    $files->uploadFile($image, 'food_image', $foodItem->id, 'A', $position++);
                }
            } catch (FileServiceException $e) {
                throw ValidationException::withMessages([
                    $field => 'The image could not be uploaded. Please choose a valid JPG, JPEG, PNG or WebP image and try again.',
                ]);
            }
            if ($replace) {
                foreach ($links as $link) {
                    $files->deleteFileLinkAfterCommit($link);
                }
            }
            $foodItem->touch();
        });
    }

    /**
     * Replace a food item's feature values. Each entry is feature_id => value(s);
     * numeric values matching one of the feature's variants are stored as variants.
     */
    /**
     * @param array|null $onlyFeatureIds When given, only these features' values are replaced
     *                                   (e.g. the ones shown on a form); others are kept.
     */
    public function syncFeatures(int $foodItemId, array $featureValues, ?array $onlyFeatureIds = null): void
    {
        // Features belong to a restaurant: only the item's own restaurant's features can be set.
        $restaurantId = FoodItem::whereKey($foodItemId)->value('restaurant_id');
        $allowed = \Modules\FoodManagements\Models\FoodFeature::forRestaurant($restaurantId ? (int) $restaurantId : null)
            ->where('feature_type', '!=', 'G')
            ->whereIn('feature_id', array_map('intval', array_keys($featureValues)))
            ->pluck('feature_id')->map(fn ($id) => (int) $id)->all();
        $featureValues = array_filter($featureValues, fn ($featureId) => in_array((int) $featureId, $allowed, true), ARRAY_FILTER_USE_KEY);

        DB::transaction(function () use ($foodItemId, $featureValues, $onlyFeatureIds) {
            \Modules\FoodManagements\Models\FoodFeatureValue::where('food_item_id', $foodItemId)
                ->when($onlyFeatureIds !== null, fn ($query) => $query->whereIn('feature_id', $onlyFeatureIds))
                ->delete();

            foreach ($featureValues as $featureId => $data) {
                if ($data === null || $data === '' || $data === []) continue;
                $values = is_array($data) ? $data : [$data];

                foreach ($values as $val) {
                    if ($val === null || $val === '') continue;

                    $isVariant = is_numeric($val) && \Modules\FoodManagements\Models\FoodFeatureVariant::where('variant_id', $val)
                        ->where('feature_id', $featureId)
                        ->exists();

                    \Modules\FoodManagements\Models\FoodFeatureValue::create([
                        'feature_id'   => $featureId,
                        'food_item_id' => $foodItemId,
                        'variant_id'   => $isVariant ? $val : null,
                        'value'        => $isVariant ? '' : (string) $val,
                        'value_int'    => !$isVariant && is_numeric($val) ? (float) $val : null,
                        'lang_code'    => 'en',
                    ]);
                }
            }
        });
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
     * Get food items filtered by one or more dietary label ids.
     */
    public function getFoodItemsByDietaryLabels(array $dietaryLabelIds = [], array $filters = [], int $perPage = 20, int $page = 1)
    {
        return $this->repository->getFoodItemsByDietaryLabels($dietaryLabelIds, $filters, $perPage, $page);
    }

    /**
     * Get food item with all details (images, categories, tags, option groups)
     */
    public function getFoodItemWithDetails(int $itemId)
    {
        $item = $this->repository->findById($itemId);
        $item->loadMissing(FoodItemFeatureData::RELATIONS);
        $taxCalculation = $item->getTaxCalculation();

        return [
            'id' => $item->id,
            'restaurant_id' => $item->restaurant_id,
            'name' => $item->name,
            'slug' => $item->slug,
            'description' => $item->description,
            'average_rating' => (float) ($item->average_rating ?? 0),
            'rating_count' => (int) ($item->rating_count ?? 0),
            'dietary_label_ids' => $item->dietary_label_ids ?? [],
            'features' => FoodItemFeatureData::forItem($item, public: true),
            'tax_percentage' => $item->tax_percentage,
            'serving_options' => $item->servingOptions->map(function ($option) {
                $basePrice = (float) ($option->price ?? 0);
                $discountPrice = $option->discount_price !== null && $option->discount_price !== '' ? (float) $option->discount_price : null;
                $unitPrice = $discountPrice !== null && $discountPrice > 0 ? $discountPrice : $basePrice;
                $serveType = $option->serve_type ?? 'package';
                $title = $option->serving_name ?: ucfirst(str_replace('_', ' ', $serveType));
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
                    if ($option->unit && $option->quantity === null) {
                        $subtitleParts[] = $option->unit;
                    }

                    $subtitle = $subtitleParts ? implode(' • ', $subtitleParts) : 'Price per serving';
                }
                $perPersonPrice = $serveType === 'per_person' ? $unitPrice : ($option->serves > 0 ? round($unitPrice / $option->serves, 2) : $unitPrice);
                $currencySymbol = fn_format_default_currency() ?: '$';
                $unitPriceText = $currencySymbol.number_format($unitPrice, 2, '.', '');

                $displayMeta = [
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'price_label' => $discountPrice !== null && $discountPrice > 0 ? 'Special offer' : ($serveType === 'package' ? 'Catering order' : ($serveType === 'per_person' ? 'Per guest' : 'Price')),
                    'unit_label' => $serveType === 'per_person' ? 'Per guest' : ($option->serves ? 'Serves '.$option->serves : ($option->quantity !== null && $option->quantity !== '' && $option->unit ? $option->quantity.' '.$option->unit : ($option->unit ?: 'Each'))),
                    'unit_price_text' => $unitPriceText,
                    'per_person_price' => number_format($perPersonPrice, 2, '.', ''),
                ];

                return [
                    'id' => $option->id,
                    'serve_type' => $serveType,
                    'serve_type_label' => ucfirst(str_replace('_', ' ', $serveType)),
                    'serving_name' => $option->serving_name,
                    'quantity' => $option->quantity,
                    'unit' => $option->unit,
                    'serves' => $option->serves,
                    'min_guests' => $option->min_guests,
                    'max_guests' => $option->max_guests,
                    'price' => $basePrice,
                    'discount_price' => $discountPrice,
                    'is_available' => (bool) $option->is_available,
                    'display' => $displayMeta,
                ];
            })->values(),
            'image' => fn_get_image('food_image', $item->id)['url'] ?? '',
            'images' => $item->getImagesAttribute(),
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
