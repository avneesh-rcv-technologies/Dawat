<?php
namespace Modules\Restaurant\Repositories;

    use Illuminate\Support\Facades\DB;

    class RestaurantHolidayRepository
    {
        public function deleteByRestaurantId(int $restaurantId): int
        {
            return DB::table('restaurant_holidays')
                ->where('restaurant_id', $restaurantId)
                ->delete();
        }

        public function createHolidays(int $restaurantId, array $holidays): void
        {
            foreach ($holidays as $holiday) {
                if (!empty($holiday['date'])) {
                    DB::table('restaurant_holidays')->insert([
                        'restaurant_id' => $restaurantId,
                        'date' => $holiday['date'],
                        'reason' => $holiday['reason'] ?? null,
                        'is_closed' => array_key_exists('is_closed', $holiday)
                            ? (bool) $holiday['is_closed']
                            : true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        public function getHolidays(int $restaurantId)
        {
            return DB::table('restaurant_holidays')
                ->where('restaurant_id', $restaurantId)
                ->orderBy('date')
                ->get();
        }
    }
