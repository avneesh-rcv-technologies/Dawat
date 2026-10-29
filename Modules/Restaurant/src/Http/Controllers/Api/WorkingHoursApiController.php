<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantWorkingHourService;

class WorkingHoursApiController extends Controller
{
    public function __construct(protected RestaurantWorkingHourService $service)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $hours = $this->service->getForRestaurant($restaurant->id);

            return response()->json([
                'success' => true,
                'message' => 'Working hours retrieved successfully.',
                'data' => [
                    'id' => $restaurant->hours?->id ?? null,
                    'is_open_whole_day' => $hours['is_open_whole_day'] ?? false,
                    'hours' => $this->formatResponseHours($hours),
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch working hours.',
            ], 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        $restaurant = $this->getRestaurantForAuthUser($request);

        try {
            $validated = Validator::make($request->all(), [
                'is_open_whole_day' => ['sometimes', 'boolean'],
                'hours' => ['required_without:is_open_whole_day', 'array'],
            ], [
                'hours.required_without' => 'Please provide working hours schedule.',
            ])->validate();

            $updatedHours = $this->service->updateForRestaurant($restaurant->id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Working hours updated successfully.',
                'data' => [
                    'id' => $restaurant->hours?->id ?? null,
                    'is_open_whole_day' => $updatedHours['is_open_whole_day'] ?? false,
                    'hours' => $this->formatResponseHours($updatedHours),
                ],
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update working hours.',
            ], 500);
        }
    }

    protected function formatResponseHours(array $hours): array
    {
        $orderedDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $formatted = [];

        foreach ($orderedDays as $day) {
            $raw = $hours[$day] ?? [];
            $segments = is_array($raw) && !empty($raw) && array_keys($raw) !== range(0, count($raw) - 1)
                ? [$raw]
                : (is_array($raw) ? $raw : []);

            $intervals = [];
            foreach ($segments as $segment) {
                $intervals[] = [
                    'open' => (bool) ($segment['open'] ?? false),
                    'from' => $segment['from'] ?? null,
                    'to' => $segment['to'] ?? null,
                ];
            }

            $formatted[$day] = [
                'open' => collect($intervals)->contains(fn ($segment) => !empty($segment['open'])),
                'intervals' => $intervals,
            ];
        }

        return $formatted;
    }

    protected function getRestaurantForAuthUser(Request $request): Restaurant
    {
        $admin = $request->user('sanctum');

        if (!$admin) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401));
        }

        $restaurant = Restaurant::query()->where('admin_id', $admin->id)->first();

        if (!$restaurant) {
            abort(response()->json([
                'success' => false,
                'message' => 'Restaurant profile not found for this account.',
            ], 404));
        }

        return $restaurant;
    }
}
