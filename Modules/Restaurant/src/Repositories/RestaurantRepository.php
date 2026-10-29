<?php

namespace Modules\Restaurant\Repositories;

use Modules\Restaurant\Services\RestaurantCacheService;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Acl\Models\Admin;
use Illuminate\Support\Facades\DB;

class RestaurantRepository
{
    public function __construct(
        private RestaurantCacheService $cacheService
    ) {}
    public function query()
    {
        return Restaurant::query();
    }

    public function findById(int $id): Restaurant
    {
        return Restaurant::with(['hours', 'holidays', 'admin'])->findOrFail($id);
    }

    public function findByIdWithRelations(int $id): Restaurant
    {
        return Restaurant::with(['hours', 'holidays'])->findOrFail($id);
    }

    public function create(array $data): Restaurant
    {
        $restaurant =  Restaurant::create($data);
        $this->cacheService->forgetList();
        return $restaurant;
    }

    public function update(int $id, array $data): Restaurant
    {
        $restaurant = $this->findById($id);
        $restaurant->update($data);
        $this->cacheService->forgetDetail($id);
        $this->cacheService->forgetList();
        return $restaurant;
    }

    public function delete(int $id): bool
    {
        $restaurant = $this->findById($id);

        // Delete related data
        $restaurant->holidays()->delete();
        $restaurant->hours()->delete();
        $deleted = $restaurant->delete();
        $this->cacheService->forgetDetail($id);
        $this->cacheService->forgetList();
        return $deleted;
    }

    public function bulkDelete(array $ids): array
    {
        $restaurants = Restaurant::whereIn('id', $ids)
            ->select('id', 'admin_id')
            ->get();

        // Delete related tables
        DB::table('restaurant_holidays')->whereIn('restaurant_id', $ids)->delete();
        DB::table('restaurant_hours')->whereIn('restaurant_id', $ids)->delete();
        Restaurant::whereIn('id', $ids)->delete();
        foreach ($ids as $id) $this->cacheService->forgetDetail($id);
        $this->cacheService->forgetList();
        return $restaurants->toArray();
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        $count = Restaurant::whereIn('id', $ids)->update(['status' => $status, 'updated_at' => now()]);
        foreach ($ids as $id) $this->cacheService->forgetDetail($id);
        $this->cacheService->forgetList();
        return $count;
    }

    public function getByAdminId(int $adminId)
    {
        return Restaurant::where('admin_id', $adminId)->get();
    }

    public function getRestaurantView(int $id)
    {
        return DB::table('restaurants')
            ->leftJoin('restaurant_hours', 'restaurants.id', '=', 'restaurant_hours.restaurant_id')
            ->leftJoin('admins', 'restaurants.admin_id', '=', 'admins.id')
            ->select(
                'restaurants.*',
                'restaurant_hours.hours',
                'admins.name as admin_name',
                'admins.email as admin_email'
            )
            ->where('restaurants.id', $id)
            ->first();
    }

    public function getHolidays(int $restaurantId)
    {
        return DB::table('restaurant_holidays')
            ->where('restaurant_id', $restaurantId)
            ->orderBy('date')
            ->get();
    }

    public function getAll(array $filters = [])
    {
        return $this->cacheService->rememberList(function () use ($filters) {
            \Log::info('DB HIT - restaurants fetched from database');


            $userLat    = $filters['user_lat']    ?? null;
            $userLng    = $filters['user_lng']    ?? null;
            $radiusMiles = (float) ($filters['radius_miles'] ?? 0);
            $query = Restaurant::query()
                ->with(['hours', 'holidays'])
                ->where('status', 'active')
                ->where('onboarding_status', OnboardingStatus::APPROVED->value)
                ->where('approval_status', 'approved');


            if ($userLat !== null && $userLng !== null && $radiusMiles > 0) {
                // Haversine formula — distance in miles
                $query->selectRaw("
        restaurants.*,
        ( 3959 * acos(
            cos(radians(?)) * cos(radians(latitude))
            * cos(radians(longitude) - radians(?))
            + sin(radians(?)) * sin(radians(latitude))
        )) AS distance_miles
    ", [$userLat, $userLng, $userLat])
                    ->having('distance_miles', '<=', $radiusMiles)
                    ->orderBy('distance_miles', 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['food_type'])) {
                $query->where('food_type', $filters['food_type']);
            }

            if (!empty($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('city', 'like', '%' . $filters['search'] . '%');
                });
            }

            $perPage = $filters['per_page'] ?? 15;

            return $query->orderBy('created_at', 'desc')->paginate($perPage);
        });
    }

    public function checkOwnership(int $restaurantId, int $adminId): bool
    {
        return Restaurant::where('id', $restaurantId)
            ->where('admin_id', $adminId)
            ->exists();
    }

    public function getOwnedRestaurantIds(int $adminId, array $ids): array
    {
        return Restaurant::where('admin_id', $adminId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->toArray();
    }
}
