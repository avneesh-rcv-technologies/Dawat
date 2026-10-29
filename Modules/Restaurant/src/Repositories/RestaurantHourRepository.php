<?php

namespace Modules\Restaurant\Repositories;

use Illuminate\Support\Facades\DB;

class RestaurantHourRepository
{
    // public function updateHours(int $restaurantId, array $hours): bool
    // {
    //     return DB::table('restaurant_hours')
    //         ->where('restaurant_id', $restaurantId)
    //         ->update([
    //             'hours' => json_encode($hours),
    //             'updated_at' => now(),
    //         ]);
    // }

    public function updateHours(int $restaurantId, array $hours, bool $isOpenWholeDay = false): bool
    {
        return DB::table('restaurant_hours')
            ->updateOrInsert(
                ['restaurant_id' => $restaurantId],
                [
                    'hours' => json_encode($hours),
                    'is_open_whole_day' => $isOpenWholeDay,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
    }


    public function createHours(int $restaurantId, array $hours, bool $isOpenWholeDay = false): int
    {
        return DB::table('restaurant_hours')->insertGetId([
            'restaurant_id' => $restaurantId,
            'hours' => json_encode($hours),
            'is_open_whole_day' => $isOpenWholeDay,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getHours(int $restaurantId)
    {
        return DB::table('restaurant_hours')
            ->where('restaurant_id', $restaurantId)
            ->first();
    }
}
