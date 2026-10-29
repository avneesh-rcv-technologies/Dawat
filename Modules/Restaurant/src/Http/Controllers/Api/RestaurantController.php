<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Restaurant\Services\RestaurantService;
use Modules\Filemanager\Services\FileService;
use Throwable;

class RestaurantController extends Controller
{
    public function __construct(
        protected RestaurantService $restaurantService,
        protected FileService $fileService
    ) {}

    /**
     * get restaurant list
     */
    public function index(Request $request)
    {
        // Get admin gallery images for home screen
        $adminGalleryRaw = $this->fileService->getFiles('admin_gallery', 1);

        // Format admin gallery
        $adminGallery = collect($adminGalleryRaw)->map(function ($image) {
            return [
                'id' => $image['id'],
                'url' => $image['url'],
                'position' => $image['position'] ?? 0,
            ];
        })->values()->all();

        // Get restaurants with filters
        $filters = $request->only(['food_type', 'search', 'per_page']);
        $filters['user_lat']  = $request->input('latitude');
        $filters['user_lng']  = $request->input('longitude');
        $filters['radius_miles'] = (float) fn_get_setting('general.restaurant_radius_miles', 0);

        $restaurants = $this->restaurantService->getAllRestaurants($filters);
        $items = $restaurants->map(function ($restaurant) {
            $image = fn_get_image('restaurant', $restaurant->id);
            \Log::info('Restaurant ID: ' . $restaurant->id . ' - Image: ' . ($image ? $image['url'] : 'No image'));
            // Get today's hours and check if currently open
            $todayHours = $this->getTodayHours($restaurant);
            $isOpen = $this->isRestaurantOpen($restaurant);

            return [
                'id'          => $restaurant->id,
                'name'        => $restaurant->name,
                'description' => $restaurant->description,
                'image'       => $image ? $image['url'] : null,
                // 'gallery_images' => $galleryImages,
                "food_type"  => $restaurant->food_type,
                "is_takeaway" => $restaurant->is_takeaway,
                "is_delivery" => $restaurant->is_delivery,
                "is_accepting_orders" => $restaurant->is_accepting_orders,
                "is_open" => $isOpen,
                "today_hours" => $todayHours,
                "holidays" => $restaurant->holidays ?? [],

                'distance_miles' => isset($restaurant->distance_miles)
                    ? round($restaurant->distance_miles, 2)
                    : null,

            ];
        });

        return response()->json([
            'success'      => true,
            'message'      => 'Restaurants fetched successfully.',
            'data'         => [
                'admin_gallery' => $adminGallery,
                'restaurants' => [
                    'items' => $items,
                    'current_page' => $restaurants->currentPage(),
                    'per_page'     => $restaurants->perPage(),
                    'total'        => $restaurants->total(),
                    'last_page'    => $restaurants->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Return primary address for the authenticated restaurant
     */
    public function address(Request $request)
    {
        try {
            $admin = $request->user('sanctum');
            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            $restaurantModel = \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->first();
            if (!$restaurantModel) {
                return response()->json(['success' => false, 'message' => 'Restaurant not found.'], 404);
            }

            $restaurant = $this->restaurantService->findRestaurantById($restaurantModel->id, ['addresses']);

            // Prefer the first (primary) address if exists
            $addressRecord = null;
            if (!empty($restaurant->addresses) && $restaurant->addresses->isNotEmpty()) {
                $addressRecord = $restaurant->addresses->first();
            }

            $address = [
                'address_line1' => $addressRecord->address_line_1 ?? $restaurant->address_line1 ?? null,
                'address_line2' => $addressRecord->address_line_2 ?? $restaurant->address_line2 ?? null,
                'city' => $addressRecord->city_name ?? $restaurant->city ?? null,
                'state' => $addressRecord->state_name ?? $restaurant->state ?? null,
                'country' => $addressRecord->country_name ?? $restaurant->country ?? null,
                'postal_code' => $addressRecord->zip_code ?? $restaurant->postal_code ?? null,
                'latitude' => $addressRecord->latitude ?? $restaurant->latitude ?? null,
                'longitude' => $addressRecord->longitude ?? $restaurant->longitude ?? null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Restaurant address fetched successfully.',
                'data' => ['restaurant_id' => $restaurant->id, 'address' => $address],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found or an error occurred.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Return public info (name, phone, description) for the authenticated restaurant
     */
    public function info(Request $request)
    {
        try {
            $admin = $request->user('sanctum');
            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            $restaurantModel = \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->first();
            if (!$restaurantModel) {
                return response()->json(['success' => false, 'message' => 'Restaurant not found.'], 404);
            }

            $restaurant = $this->restaurantService->findRestaurantById($restaurantModel->id);

            return response()->json([
                'success' => true,
                'message' => 'Restaurant info fetched successfully.',
                'data' => [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'phone' => $restaurant->phone,
                    'description' => $restaurant->description,
                    'image' => fn_get_image('restaurant', $restaurant->id)['url'] ?? null,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found or an error occurred.',
            ], 404);
        }
    }

    /**
     * Return restaurant gallery images for the authenticated restaurant
     */
    public function gallery(Request $request)
    {
        try {
            $admin = $request->user('sanctum');
            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            $restaurantModel = \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->first();
            if (!$restaurantModel) {
                return response()->json(['success' => false, 'message' => 'Restaurant not found.'], 404);
            }

            $images = fn_get_images('restaurant_gallery', $restaurantModel->id) ?: [];

            return response()->json([
                'success' => true,
                'message' => 'Restaurant gallery fetched successfully.',
                'data' => ['restaurant_id' => $restaurantModel->id, 'images' => $images],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch gallery images.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get today's opening hours for a restaurant
     */
    private function getTodayHours($restaurant): ?array
    {
        if (!$restaurant?->hours) {
            return null;
        }

        $hoursRecord = $restaurant->hours;
        $hoursData = is_object($hoursRecord) && isset($hoursRecord->hours)
            ? $hoursRecord->hours
            : $hoursRecord;

        if (is_string($hoursData)) {
            $hoursData = json_decode($hoursData, true);
        }

        if (!is_array($hoursData)) {
            return null;
        }

        $today = strtolower(now()->format('l'));
        $dayKey = $today;
        if (!array_key_exists($dayKey, $hoursData)) {
            $dayKey = ucfirst($today);
        }

        $dayHours = $hoursData[$dayKey] ?? [];
        $wholeDayOpen = (bool) (data_get($hoursData, 'is_open_whole_day', false) || data_get($hoursRecord, 'is_open_whole_day', false));

        if ($wholeDayOpen) {
            return [
                'day' => ucfirst($today),
                'open' => true,
                'from' => '00:00',
                'to' => '23:59',
                'intervals' => [[
                    'from' => '00:00',
                    'to' => '23:59',
                ]],
            ];
        }

        if (!is_array($dayHours) || empty($dayHours)) {
            return [
                'day' => ucfirst($today),
                'open' => false,
                'from' => null,
                'to' => null,
                'intervals' => [],
            ];
        }

        $intervals = $dayHours;
        if (array_keys($dayHours) !== range(0, count($dayHours) - 1)) {
            $intervals = [$dayHours];
        }

        $normalizedIntervals = [];
        foreach ($intervals as $interval) {
            if (!is_array($interval)) {
                continue;
            }

            $open = isset($interval['open']) ? (bool) $interval['open'] : true;
            if (!$open) {
                continue;
            }

            $from = $interval['from'] ?? null;
            $to = $interval['to'] ?? null;
            if ($from && $to) {
                $normalizedIntervals[] = ['from' => $from, 'to' => $to];
            }
        }

        $currentTime = now()->format('H:i');
        $matchedInterval = null;

        foreach ($normalizedIntervals as $interval) {
            if ($this->isTimeWithinInterval($currentTime, $interval['from'], $interval['to'])) {
                $matchedInterval = $interval;
                break;
            }
        }

        return [
            'day' => ucfirst($today),
            'open' => !empty($matchedInterval),
            'from' => $matchedInterval['from'] ?? null,
            'to' => $matchedInterval['to'] ?? null,
            'intervals' => $normalizedIntervals,
        ];
    }

    private function isTimeWithinInterval(string $currentTime, string $from, string $to): bool
    {
        $currentMinutes = $this->timeToMinutes($currentTime);
        $startMinutes = $this->timeToMinutes($from);
        $endMinutes = $this->timeToMinutes($to);

        if ($startMinutes <= $endMinutes) {
            return $currentMinutes >= $startMinutes && $currentMinutes <= $endMinutes;
        }

        return $currentMinutes >= $startMinutes || $currentMinutes <= $endMinutes;
    }

    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = array_pad(explode(':', $time), 2, '00');

        return ((int) $hours * 60) + (int) $minutes;
    }

    /**
     * Check if restaurant is currently open based on time
     */
    private function isRestaurantOpen($restaurant): bool
    {
        if (!$restaurant || !$restaurant->is_accepting_orders) {
            return false;
        }

        if ($this->isHolidayToday($restaurant)) {
            return false;
        }

        $todayHours = $this->getTodayHours($restaurant);
        if (!$todayHours) {
            return false;
        }

        return (bool) ($todayHours['open'] ?? false);
    }

    /**
     * Check if today is a holiday for the restaurant
     */
    private function isHolidayToday($restaurant): bool
    {
        $holidays = $restaurant->holidays ?? [];
        if (empty($holidays)) {
            return false;
        }

        $today = now()->format('Y-m-d');

        return collect($holidays)->contains(function ($holiday) use ($today) {
            $holidayDate = is_object($holiday) ? ($holiday->date ?? null) : ($holiday['date'] ?? null);

            if (!$holidayDate) {
                return false;
            }

            return \Carbon\Carbon::parse($holidayDate)->format('Y-m-d') === $today;
        });
    }
}
