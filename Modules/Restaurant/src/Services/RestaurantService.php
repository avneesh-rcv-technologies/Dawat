<?php

namespace Modules\Restaurant\Services;

use Modules\Restaurant\Repositories\RestaurantRepository;
use Modules\Restaurant\Repositories\RestaurantHourRepository;
use Modules\Restaurant\Repositories\RestaurantHolidayRepository;
use Modules\Restaurant\Repositories\AdminRepository;
use Illuminate\Support\Facades\DB;
use Modules\Notifications\Services\NotificationService;
use App\Services\CacheService;
use Modules\Restaurant\Models\Restaurant;

class RestaurantService
{
    protected RestaurantRepository $restaurantRepository;
    protected RestaurantHourRepository $hourRepository;
    protected RestaurantHolidayRepository $holidayRepository;
    protected AdminRepository $adminRepository;
    protected NotificationService $notificationService;

    public function __construct(
            RestaurantRepository $restaurantRepository,
            RestaurantHourRepository $hourRepository,
            RestaurantHolidayRepository $holidayRepository,
            AdminRepository $adminRepository,
            NotificationService $notificationService,
            protected GeocodingService $geocodingService
        ) {
            $this->restaurantRepository = $restaurantRepository;
            $this->hourRepository = $hourRepository;
            $this->holidayRepository = $holidayRepository;
            $this->adminRepository = $adminRepository;
            $this->notificationService = $notificationService;
        }

    public function getAllRestaurants(array $filters = [])
    {
        return $this->restaurantRepository->getAll($filters);
    }

    public function find(int $id)
    {
        return $this->restaurantRepository->findById($id);
    }
    public function findRestaurantById(int $id , $relations = [])
    {
        return Restaurant::with($relations)->findOrFail($id);
    }

    public function findWithRelations(int $id)
    {
        return $this->restaurantRepository->findByIdWithRelations($id);
    }

    public function getRestaurantForEdit(int $id, ?object $currentAdmin = null)
    {
        $restaurant = $this->restaurantRepository->findByIdWithRelations($id);

        // If user is restaurant owner (role_id = 2), ensure they can only edit their own restaurant
        if ($currentAdmin && $currentAdmin->role_id == 2) {
            if ($restaurant->admin_id !== $currentAdmin->id) {
                throw new \Exception('You can only edit your own restaurant.');
            }
        }

        return $restaurant;
    }

    public function createGeneral(array $data): int
    {   
       
        return DB::transaction(function () use ($data) {
            // Store plain password before hashing
            $plainPassword = $data['password'] ?? null;
            
            // Create admin
            $admin = $this->adminRepository->create($data);
            // Create restaurant
            $restaurant = $this->restaurantRepository->create([
                'admin_id'    => $admin->id,
                'name'        => $data['name'],
                'slug'        => $data['slug'],
                'phone'       => $data['phone'],
                'description' => $data['description'] ?? null,
                'status'      => 'draft',
            ]);
            // Create default hours
            $this->hourRepository->createHours($restaurant->id, $this->getDefaultHours());

            // Trigger restaurant created notification with password
            $this->notificationService->trigger(
                'Restaurant',
                'RestaurantCreated',
                $admin->email,
                ['restaurant' => $restaurant, 'password' => $plainPassword]
            );

            return $restaurant->id;
        });
    }

    public function updateGeneral(int $restaurantId, array $data): bool
    {
        return \DB::transaction(function () use ($restaurantId, $data) {
            // Update restaurant basic info only for provided keys
            $updateData = [];
            if (array_key_exists('name', $data)) {
                $updateData['name'] = $data['name'];
            }
            if (array_key_exists('phone', $data)) {
                $updateData['phone'] = $data['phone'];
            }
            if (array_key_exists('description', $data)) {
                $updateData['description'] = $data['description'];
            }

            if (!empty($updateData)) {
                $this->restaurantRepository->update($restaurantId, $updateData);
            }

            // Update admin password if provided
            $restaurant = $this->restaurantRepository->findById($restaurantId);
            if (!empty($data['password']) && $restaurant->admin_id) {
                $this->adminRepository->update($restaurant->admin_id, [
                    'password' => $data['password']
                ]);
            }

            return true;
        });
    }

    public function updateAddress(int $restaurantId, array $data)
        {
            return DB::transaction(function () use ($restaurantId, $data) {
                $restaurant = $this->restaurantRepository->findById($restaurantId);

                // Auto-geocode if latitude/longitude not provided
                if (empty($data['latitude']) || empty($data['longitude'])) {
                    $coordinates = $this->geocodingService->geocodeAddress($data);

                    if ($coordinates) {
                        $data['latitude'] = $coordinates['latitude'];
                        $data['longitude'] = $coordinates['longitude'];
                    }
                }

                // Update restaurant table (for backward compatibility)
                $restaurantData = [
                    'address_line1' => $data['address_line1'] ?? null,
                    'address_line2' => $data['address_line2'] ?? null,
                    'city' => $data['city_name'] ?? null,
                    'state' => $data['state_name'] ?? null,
                    'country' => $data['country_name'] ?? null,
                    'postal_code' => $data['postal_code'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                ];

                $this->restaurantRepository->update($restaurantId, $restaurantData);

                // Update or create address in addresses table
                $addressData = [
                    'address_line_1' => $data['address_line1'] ?? null,
                    'address_line_2' => $data['address_line2'] ?? null,
                    'city_id' => $data['city_id'] ?? null,
                    'state_id' => $data['state_id'] ?? null,
                    'country_id' => $data['country_id'] ?? null,
                    'zip_code' => $data['postal_code'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'is_default' => true,
                ];

                // Use the addresses relationship
                $restaurant->addresses()->updateOrCreate(
                    [
                        'addressable_id' => $restaurantId,
                        'addressable_type' => 'Modules\\Restaurant\\Models\\Restaurant',
                        'address_type' => 'primary',
                    ],
                    $addressData
                );

                return true;
            });
        }

    public function updateHours(int $restaurantId, array $hours, bool $isOpenWholeDay = false)
    {
        $defaultHours = $this->getDefaultHours();

        foreach ($this->getOrderedDays() as $day) {
            if ($isOpenWholeDay) {
                $hours[$day] = [
                    ['open' => true, 'from' => '00:00', 'to' => '23:59'],
                ];
                continue;
            }

            $input = $hours[$day] ?? null;

            if (is_null($input)) {
                $hours[$day] = $defaultHours[$day];
                continue;
            }

            if (is_array($input) && (array_keys($input) !== range(0, count($input) - 1))) {
                $input = [$input];
            }

            $hours[$day] = $this->normalizeDayHours($day, $input, $defaultHours[$day]);
        }

        $result = $this->hourRepository->updateHours($restaurantId, $hours, $isOpenWholeDay);
        CacheService::forgetRestaurant($restaurantId);
        CacheService::forgetRestaurantList();
        return $result;
    }

    protected function normalizeDayHours(string $day, array $intervals, array $fallback): array
    {
        $valid = [];

        foreach ($intervals as $segment) {
            if (!is_array($segment)) {
                continue;
            }

            $open = isset($segment['open']) ? (bool) $segment['open'] : true;
            $from = $segment['from'] ?? null;
            $to = $segment['to'] ?? null;

            if (!$open) {
                continue;
            }

            if (!is_string($from) || !is_string($to)) {
                throw new \InvalidArgumentException('Invalid working hour range for ' . ucfirst($day) . '.');
            }

            if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $from) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $to)) {
                throw new \InvalidArgumentException('Working hours must be in HH:MM format for ' . ucfirst($day) . '.');
            }

            $fromMinutes = $this->minutesFromTime($from);
            $toMinutes = $this->minutesFromTime($to);

            if ($fromMinutes >= $toMinutes) {
                throw new \InvalidArgumentException('The start time must be earlier than the end time for ' . ucfirst($day) . '.');
            }

            $valid[] = ['open' => true, 'from' => $from, 'to' => $to];
        }

        if (empty($valid)) {
            return $fallback;
        }

        usort($valid, fn ($a, $b) => strcmp($a['from'], $b['from']));

        $previous = $valid[0];
        for ($i = 1; $i < count($valid); $i++) {
            $current = $valid[$i];
            $previousEnd = $this->minutesFromTime($previous['to']);
            $currentStart = $this->minutesFromTime($current['from']);

            if ($previousEnd > $currentStart) {
                throw new \InvalidArgumentException('Overlapping working hours found for ' . ucfirst($day) . '.');
            }

            $previous = $current;
        }

        return $valid;
    }

    protected function minutesFromTime(string $time): int
    {
        [$hours, $minutes] = array_pad(explode(':', $time), 2, '00');
        return ((int) $hours * 60) + (int) $minutes;
    }

    public function updateHolidays(int $restaurantId, array $holidays)
    {
        // dd($restaurantId , $holidays);
        // Delete existing holidays
        $this->holidayRepository->deleteByRestaurantId($restaurantId);

        // Insert new holidays
        $this->holidayRepository->createHolidays($restaurantId, $holidays);

        CacheService::forgetRestaurant($restaurantId);
        CacheService::forgetRestaurantList();
        return true;
    }

    public function updateSettings(int $restaurantId, array $data)
    {
        $updateData = [
            'food_type' => $data['food_type'] ?? 'all',
            'is_takeaway' => $data['is_takeaway'] ?? false,
            'is_delivery' => $data['is_delivery'] ?? false,
            'is_accepting_orders' => $data['is_accepting_orders'] ?? false,
            'order_time_limit' => $data['order_time_limit'] ?? 48,
            'status' => $data['status'] ?? 'draft',
            'allow_order_cancellation' => $data['allow_order_cancellation'] ?? false,
            'cancellation_time_limit' => $data['cancellation_time_limit'] ?? 15,
            'cancellable_statuses' => $data['cancellable_statuses'] ?? ['P', 'O', 'H'],
        ];

        $result = $this->restaurantRepository->update($restaurantId, $updateData);

        // Trigger restaurant status changed notification if status is being updated
        if (isset($data['status']) && $data['status'] !== 'draft') {
            $restaurant = $this->restaurantRepository->findById($restaurantId);

            if ($data['status'] === 'active') {
                // Trigger restaurant approved notification
                $this->notificationService->trigger(
                    'Restaurant',
                    'RestaurantApproved',
                    $restaurant->admin->email,
                    $restaurant
                );
            } else {
                // Trigger restaurant status changed notification
                $this->notificationService->trigger(
                    'Restaurant',
                    'RestaurantStatusChanged',
                    $restaurant->admin->email,
                    $restaurant
                );
            }
        }

        return $result;
    }

    public function getRestaurantView(int $id)
    {
        $restaurant = $this->restaurantRepository->getRestaurantView($id);

        if (!$restaurant) {
            throw new \Exception('Restaurant not found');
        }

        // Decode hours if available
        $hours = $restaurant->hours ? json_decode($restaurant->hours, true) : [];

        // Get holidays
        $holidays = $this->restaurantRepository->getHolidays($id);

        // Get analytics data
        $analyticsService = app(\Modules\Admin\Services\AnalyticsService::class);
        $dateFrom = request()->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = request()->input('date_to', now()->format('Y-m-d'));
        $analytics = $analyticsService->getRestaurantAnalytics($id, $dateFrom, $dateTo);

        return compact('restaurant', 'hours', 'holidays', 'analytics', 'dateFrom', 'dateTo');
    }

    public function getRestaurantHistory(int $id)
    {
        $restaurant = Restaurant::whereKey($id)->first();

        if (!$restaurant) {
            throw new \Exception('Restaurant not found');
        }

        return $restaurant->onboardingStatusHistories()
            ->requiredOnboardingSteps()
            ->with('changer')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function recordOnboardingStatusHistory(Restaurant $restaurant, string $oldStatus, string $newStatus, ?int $changedBy = null, ?string $reason = null): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        app(OnboardingHistoryService::class)->record(
            $restaurant->id, null, 'status_changed', $oldStatus, $newStatus, $changedBy, $reason
        );
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $restaurant = $this->restaurantRepository->findById($id);
            $adminId = $restaurant->admin_id;

            // Delete restaurant (will cascade to hours and holidays via repository)
            $deleted = $this->restaurantRepository->delete($id);

            // Delete admin if not used elsewhere
            if ($adminId && !$this->adminRepository->isAdminUsedElsewhere($adminId)) {
                $this->adminRepository->delete($adminId);
            }

            return $deleted;
        });
    }

    public function bulkDelete(array $ids, ?object $currentAdmin = null): int
    {
        // Only super admins (role_id = 1) can perform bulk delete
        if (!$currentAdmin || $currentAdmin->role_id !== 1) {
            throw new \Exception('You do not have permission to delete restaurants');
        }

        return DB::transaction(function () use ($ids) {
            // Get restaurants with admin_id before deletion
            $restaurants = $this->restaurantRepository->bulkDelete($ids);

            // Delete unused admins
            $adminIds = array_column($restaurants, 'admin_id');
            $this->adminRepository->deleteUnusedAdmins($adminIds);

            return count($restaurants);
        });
    }

    public function bulkUpdateStatus(array $ids, string $status, ?object $currentAdmin = null): int
    {
        // Validate status
        if (!in_array($status, ['active', 'inactive', 'draft'])) {
            throw new \Exception('Invalid status selected: ' . $status);
        }

        // If user is restaurant owner (role_id = 2), ensure they can only update their own restaurants
        if ($currentAdmin && $currentAdmin->role_id == 2) {
            $ownedIds = $this->restaurantRepository->getOwnedRestaurantIds($currentAdmin->id, $ids);

            if (count($ownedIds) !== count($ids)) {
                throw new \Exception('You can only update your own restaurants');
            }

            $ids = $ownedIds;
        }

        return $this->restaurantRepository->bulkUpdateStatus($ids, $status);
    }

    public function getFormattedHours($restaurant): array
    {
        $stored = $restaurant?->hours
            ? (is_array($restaurant->hours->hours)
                ? $restaurant->hours->hours
                : json_decode($restaurant->hours->hours, true))
            : null;

        $default = $this->getDefaultHours();

        $orderedHours = [];
        foreach ($this->getOrderedDays() as $day) {
            $value = $stored[$day] ?? null;

            // Missing day data should use default hours, but an explicit empty array means the user intentionally closed the day.
            if (is_null($value)) {
                $orderedHours[$day] = $default[$day];
                continue;
            }

            if (is_array($value) && count($value) === 0) {
                $orderedHours[$day] = [];
                continue;
            }

            // If associative single interval, wrap into array
            if (is_array($value) && (array_keys($value) !== range(0, count($value) - 1))) {
                $orderedHours[$day] = [[
                    'open' => isset($value['open']) ? (bool)$value['open'] : true,
                    'from' => $value['from'] ?? $default[$day][0]['from'],
                    'to' => $value['to'] ?? $default[$day][0]['to'],
                ]];
                continue;
            }

            // Numeric array — ensure each segment has required keys
            $segments = [];
            foreach ($value as $seg) {
                if (!is_array($seg)) continue;
                $segments[] = [
                    'open' => isset($seg['open']) ? (bool)$seg['open'] : true,
                    'from' => $seg['from'] ?? $default[$day][0]['from'],
                    'to' => $seg['to'] ?? $default[$day][0]['to'],
                ];
            }

            $orderedHours[$day] = count($segments) ? $segments : [];
        }

        $orderedHours['is_open_whole_day'] = $restaurant?->hours?->is_open_whole_day ?? false;

        return $orderedHours;
    }

    public function getFormattedHolidays($restaurant): array
    {
        return $restaurant?->holidays
            ->sortBy('date')
            ->map(fn($h) => [
                'date' => $h->date,
                'reason' => $h->reason,
            ])
            ->values()
            ->toArray() ?? [];
    }

    public function getOrderedDays(): array
    {
        return [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];
    }

    public function getDefaultHours(): array
    {
        // Default returns array of single interval per day to support multi-interval storage
        return [
            'monday'    => [['open' => false, 'from' => '09:00', 'to' => '22:00']],
            'tuesday'   => [['open' => false, 'from' => '09:00', 'to' => '22:00']],
            'wednesday' => [['open' => false, 'from' => '09:00', 'to' => '22:00']],
            'thursday'  => [['open' => false, 'from' => '09:00', 'to' => '22:00']],
            'friday'    => [['open' => false, 'from' => '09:00', 'to' => '22:00']],
            'saturday'  => [['open' => false, 'from' => '09:00', 'to' => '22:00']],
            'sunday'    => [['open' => false, 'from' => '09:00', 'to' => '22:00']],
        ];
    }
}
