<?php

namespace Modules\Restaurant\Services;

use Modules\Restaurant\Models\RestaurantHoliday;

class RestaurantHolidayService
{
    public function listForRestaurant(int $restaurantId, array $filters = [])
    {
        $query = RestaurantHoliday::query()
            ->where('restaurant_id', $restaurantId);

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhereDate('date', $search);
            });
        }

        if (array_key_exists('is_closed', $filters) && $filters['is_closed'] !== '') {
            $query->where('is_closed', (bool) $filters['is_closed']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        $sortBy = $filters['sort_by'] ?? 'date';
        $sortBy = in_array($sortBy, ['id', 'date', 'reason', 'is_closed', 'created_at'], true) ? $sortBy : 'date';

        $sortDirValue = $filters['sort_dir'] ?? 'asc';
        $sortDir = in_array(strtolower((string) $sortDirValue), ['asc', 'desc'], true)
            ? strtolower((string) $sortDirValue)
            : 'asc';

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage)->appends($filters);
    }

    public function showForRestaurant(int $restaurantId, int $holidayId): RestaurantHoliday
    {
        return RestaurantHoliday::query()
            ->where('restaurant_id', $restaurantId)
            ->findOrFail($holidayId);
    }

    public function createForRestaurant(int $restaurantId, array $data): RestaurantHoliday
    {
        $date = $this->normalizeDate($data['date'] ?? null);

        if ($this->existsForRestaurantDate($restaurantId, $date, null)) {
            throw new \RuntimeException('Holiday already exists for this date.');
        }

        return RestaurantHoliday::query()->create([
            'restaurant_id' => $restaurantId,
            'date' => $date,
            'reason' => isset($data['reason']) ? trim((string) $data['reason']) : null,
            'is_closed' => (bool) ($data['is_closed'] ?? true),
        ]);
    }

    public function updateForRestaurant(int $restaurantId, int $holidayId, array $data): RestaurantHoliday
    {
        $holiday = $this->showForRestaurant($restaurantId, $holidayId);
        $date = $this->normalizeDate($data['date'] ?? $holiday->date->format('Y-m-d'));

        if ($this->existsForRestaurantDate($restaurantId, $date, $holidayId)) {
            throw new \RuntimeException('Another holiday already exists for this date.');
        }

        $holiday->fill([
            'date' => $date,
            'reason' => array_key_exists('reason', $data) ? trim((string) $data['reason']) : $holiday->reason,
            'is_closed' => array_key_exists('is_closed', $data) ? (bool) $data['is_closed'] : $holiday->is_closed,
        ]);

        $holiday->save();

        return $holiday->fresh();
    }

    public function deleteForRestaurant(int $restaurantId, int $holidayId): bool
    {
        $holiday = $this->showForRestaurant($restaurantId, $holidayId);

        return (bool) $holiday->delete();
    }

    public function bulkDeleteForRestaurant(int $restaurantId, array $ids): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === []) {
            return 0;
        }

        return (int) RestaurantHoliday::query()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('id', $ids)
            ->delete();
    }

    protected function normalizeDate($value): string
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('Holiday date is required.');
        }

        return \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    protected function existsForRestaurantDate(int $restaurantId, string $date, ?int $excludeId = null): bool
    {
        $query = RestaurantHoliday::query()
            ->where('restaurant_id', $restaurantId)
            ->whereDate('date', $date);

        if ($excludeId) {
            $query->whereKeyNot($excludeId);
        }

        return $query->exists();
    }
}
