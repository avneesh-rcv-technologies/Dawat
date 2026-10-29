<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Arr;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Repositories\RestaurantHourRepository;

class RestaurantWorkingHourService
{
    public function __construct(
        protected RestaurantHourRepository $hourRepository,
        protected RestaurantService $restaurantService
    ) {
    }

    public function getForRestaurant(int $restaurantId): array
    {
        $restaurant = Restaurant::query()->with('hours')->findOrFail($restaurantId);

        return $this->restaurantService->getFormattedHours($restaurant);
    }

    public function updateForRestaurant(int $restaurantId, array $payload): array
    {
        $hours = data_get($payload, 'hours', []);
        $isOpenWholeDay = filter_var(data_get($payload, 'is_open_whole_day', false), FILTER_VALIDATE_BOOLEAN);

        $normalized = $this->normalizeAndValidateHours($hours, $isOpenWholeDay);

        $this->hourRepository->updateHours($restaurantId, $normalized, $isOpenWholeDay);

        return $this->getForRestaurant($restaurantId);
    }

    protected function normalizeAndValidateHours(array $hours, bool $isOpenWholeDay): array
    {
        if ($isOpenWholeDay) {
            $days = $this->restaurantService->getOrderedDays();
            $normalized = [];
            foreach ($days as $day) {
                $normalized[$day] = [[
                    'open' => true,
                    'from' => '00:00',
                    'to' => '23:59',
                ]];
            }

            return $normalized;
        }

        $orderedDays = $this->restaurantService->getOrderedDays();
        $normalized = [];

        foreach ($orderedDays as $day) {
            $raw = $hours[$day] ?? [];
            $segments = $this->normalizeDaySegments($raw);

            $validatedSegments = [];
            $openSegments = [];

            foreach ($segments as $idx => $segment) {
                if (!is_array($segment)) {
                    throw new \InvalidArgumentException("Invalid interval for {$day}.");
                }

                $open = (bool) ($segment['open'] ?? false);
                $from = $segment['from'] ?? null;
                $to = $segment['to'] ?? null;

                if (!$open) {
                    $validatedSegments[] = ['open' => false, 'from' => null, 'to' => null];
                    continue;
                }

                if (empty($from) || empty($to)) {
                    throw new \InvalidArgumentException("Opening and closing times are required for {$day} when open is enabled.");
                }

                if (!$this->isValidTimeFormat($from) || !$this->isValidTimeFormat($to)) {
                    throw new \InvalidArgumentException("Opening and closing times for {$day} must be in HH:MM format.");
                }

                $fromMinutes = $this->minutesFromTime($from);
                $toMinutes = $this->minutesFromTime($to);

                if ($fromMinutes >= $toMinutes) {
                    throw new \InvalidArgumentException("Opening time must be earlier than closing time for {$day}.");
                }

                $validatedSegments[] = ['open' => true, 'from' => $from, 'to' => $to];
                $openSegments[] = ['from' => $fromMinutes, 'to' => $toMinutes];
            }

            foreach ($openSegments as $index => $segment) {
                foreach (array_slice($openSegments, $index + 1) as $nextSegment) {
                    if ($segment['to'] > $nextSegment['from']) {
                        throw new \InvalidArgumentException("Time intervals for {$day} overlap. Please adjust the schedule.");
                    }
                }
            }

            $normalized[$day] = $validatedSegments;
        }

        return $normalized;
    }

    protected function normalizeDaySegments($value): array
    {
        if (is_null($value)) {
            return [];
        }

        if (is_array($value) && !Arr::isAssoc($value)) {
            return $value;
        }

        if (is_array($value) && Arr::isAssoc($value)) {
            return [$value];
        }

        return [];
    }

    protected function isValidTimeFormat(string $value): bool
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value) === 1;
    }

    protected function minutesFromTime(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);

        return ((int) $hours * 60) + (int) $minutes;
    }
}
