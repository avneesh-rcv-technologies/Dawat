<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodEvent;
use Modules\FoodManagements\Models\FoodItem;

class RestaurantFoodEventService
{
    public function listForRestaurant(int $restaurantId, array $filters = [])
    {
        $query = FoodEvent::query()
            ->with(['foodCategories' => function ($q) use ($restaurantId) {
                $q->where('restaurant_id', $restaurantId);
            }, 'foodItems.categories'])
            ->where(function (Builder $q) use ($restaurantId) {
                $q->whereHas('foodCategories', function ($sub) use ($restaurantId) {
                    $sub->where('restaurant_id', $restaurantId);
                })->orWhereHas('foodItems.categories', function ($sub) use ($restaurantId) {
                    $sub->where('restaurant_id', $restaurantId);
                });
            })
            ->distinct()
            ->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_dir'] ?? 'desc');

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (array_key_exists('status', $filters) && $filters['status'] !== '') {
            $status = strtolower((string) $filters['status']);
            $query->where('status', $status === 'active' || $status === '1' || $status === 'true' ? 1 : 0);
        }

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        return $query->paginate($perPage)->appends($filters);
    }

    public function showForRestaurant(int $restaurantId, int $eventId): FoodEvent
    {
        return FoodEvent::query()
            ->with([
                'foodCategories' => function ($q) use ($restaurantId) {
                    $q->where('restaurant_id', $restaurantId);
                },
                'foodItems.categories' => function ($q) use ($restaurantId) {
                    $q->where('restaurant_id', $restaurantId);
                },
            ])
            ->where(function (Builder $q) use ($restaurantId, $eventId) {
                $q->where('id', $eventId)
                    ->whereHas('foodCategories', function ($sub) use ($restaurantId) {
                        $sub->where('restaurant_id', $restaurantId);
                    })
                    ->orWhere(function ($sub) use ($restaurantId, $eventId) {
                        $sub->where('id', $eventId)
                            ->whereHas('foodItems.categories', function ($inner) use ($restaurantId) {
                                $inner->where('restaurant_id', $restaurantId);
                            });
                    });
            })
            ->firstOrFail();
    }

    public function createForRestaurant(int $restaurantId, array $data): FoodEvent
    {
        $slug = trim((string) ($data['slug'] ?? ($data['name'] ?? '')));
        if ($slug !== '' && $this->slugExistsForRestaurant($slug, null)) {
            throw ValidationException::withMessages([
                'slug' => ['The slug has already been taken.'],
            ]);
        }

        $event = FoodEvent::query()->create([
            'name' => trim((string) ($data['name'] ?? '')),
            'slug' => $this->generateUniqueSlug($slug ?: (string) ($data['name'] ?? 'food-event'), null),
            'description' => $data['description'] ?? null,
            'status' => isset($data['status']) ? (bool) $data['status'] : true,
        ]);

        $this->syncRestaurantCategoryLinks($event, $restaurantId, $data['food_categories'] ?? []);
        $this->syncRestaurantItemLinks($event, $restaurantId, $data['food_items'] ?? []);

        return $event->fresh(['foodCategories', 'foodItems.categories']);
    }

    public function updateForRestaurant(int $restaurantId, int $eventId, array $data): FoodEvent
    {
        $event = $this->showForRestaurant($restaurantId, $eventId);

        $payload = [];

        if (array_key_exists('name', $data)) {
            $payload['name'] = trim((string) $data['name']);
        }

        if (array_key_exists('slug', $data)) {
            $slug = trim((string) $data['slug']);
            if ($slug !== '' && $this->slugExistsForRestaurant($slug, $event->id)) {
                throw ValidationException::withMessages([
                    'slug' => ['The slug has already been taken.'],
                ]);
            }
            $payload['slug'] = $slug ?: $this->generateUniqueSlug($event->name, $event->id);
        }

        if (array_key_exists('description', $data)) {
            $payload['description'] = $data['description'];
        }

        if (array_key_exists('status', $data)) {
            $payload['status'] = (bool) $data['status'];
        }

        if (!empty($payload['name']) && $payload['name'] !== $event->name) {
            $payload['slug'] = $this->generateUniqueSlug($payload['name'], $event->id);
        }

        if (!empty($payload)) {
            $event->fill($payload);
            $event->save();
        }

        if (array_key_exists('food_categories', $data)) {
            $this->syncRestaurantCategoryLinks($event, $restaurantId, $data['food_categories'] ?? []);
        }

        if (array_key_exists('food_items', $data)) {
            $this->syncRestaurantItemLinks($event, $restaurantId, $data['food_items'] ?? []);
        }

        return $event->fresh(['foodCategories', 'foodItems.categories']);
    }

    public function deleteForRestaurant(int $restaurantId, int $eventId): bool
    {
        $event = $this->showForRestaurant($restaurantId, $eventId);

        return (bool) $event->delete();
    }

    public function bulkDeleteForRestaurant(int $restaurantId, array $ids): int
    {
        $eventIds = $this->filterEventIdsForRestaurant($restaurantId, $ids);

        if ($eventIds === []) {
            return 0;
        }

        return (int) FoodEvent::query()->whereIn('id', $eventIds)->delete();
    }

    public function toggleStatusForRestaurant(int $restaurantId, array $ids, bool $status): int
    {
        $eventIds = $this->filterEventIdsForRestaurant($restaurantId, $ids);

        if ($eventIds === []) {
            return 0;
        }

        return (int) FoodEvent::query()->whereIn('id', $eventIds)->update(['status' => $status]);
    }

    public function getRestaurantEventItems(int $restaurantId, int $eventId)
    {
        $event = $this->showForRestaurant($restaurantId, $eventId);

        $items = $event->getAllFoodItems()
            ->filter(function ($item) use ($restaurantId) {
                return $item->categories()->where('restaurant_id', $restaurantId)->exists();
            })
            ->values();

        return $items;
    }

    public function getRestaurantEventCategories(int $restaurantId, int $eventId)
    {
        $event = $this->showForRestaurant($restaurantId, $eventId);

        return $event->foodCategories()
            ->where('restaurant_id', $restaurantId)
            ->get();
    }

    public function getRestaurantItemLinks(FoodEvent $event, int $restaurantId)
    {
        return $event->foodItems()
            ->whereHas('categories', function ($q) use ($restaurantId) {
                $q->where('restaurant_id', $restaurantId);
            })
            ->get();
    }

    public function getItemsListForRestaurant(int $restaurantId, array $filters = [])
    {
        $query = FoodItem::query()
            ->with(['categories' => function ($q) use ($restaurantId) {
                $q->where('restaurant_id', $restaurantId);
            }])
            ->whereHas('categories', function ($q) use ($restaurantId) {
                $q->where('restaurant_id', $restaurantId);
            });

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $status = strtolower((string) $filters['status']);
            $query->where('is_active', $status === 'active' || $status === '1' || $status === 'true' ? 1 : 0);
        }

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        return $query->orderBy('name', 'asc')->paginate($perPage)->appends($filters);
    }

    protected function filterEventIdsForRestaurant(int $restaurantId, array $ids): array
    {
        $eventIds = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($eventIds === []) {
            return [];
        }

        return FoodEvent::query()
            ->whereIn('id', $eventIds)
            ->where(function (Builder $query) use ($restaurantId) {
                $query->whereHas('foodCategories', function ($sub) use ($restaurantId) {
                    $sub->where('restaurant_id', $restaurantId);
                })->orWhereHas('foodItems.categories', function ($sub) use ($restaurantId) {
                    $sub->where('restaurant_id', $restaurantId);
                });
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function syncRestaurantCategoryLinks(FoodEvent $event, int $restaurantId, array $categoryIds): void
    {
        $allowedCategoryIds = FoodCategory::query()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('id', array_filter(array_map('intval', $categoryIds)))
            ->pluck('id')
            ->all();

        $event->foodCategories()->sync($allowedCategoryIds);
    }

    protected function syncRestaurantItemLinks(FoodEvent $event, int $restaurantId, array $foodItemIds): void
    {
        $allowedItemIds = FoodItem::with('categories')
            ->whereIn('id', $foodItemIds)
            ->whereHas('categories', function ($q) use ($restaurantId) {
                $q->where('restaurant_id', $restaurantId);
            })
            ->pluck('id')->values()->all();



        $event->foodItems()->sync($allowedItemIds);
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name ?: 'food-event');
        $slug = $base ?: 'food-event';
        $counter = 1;

        while (FoodEvent::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    protected function slugExistsForRestaurant(string $slug, ?int $ignoreId = null): bool
    {
        return FoodEvent::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists();
    }
}
