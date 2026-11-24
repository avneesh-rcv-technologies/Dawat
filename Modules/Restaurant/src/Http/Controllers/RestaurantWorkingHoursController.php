<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;
use Modules\Restaurant\Services\RestaurantService;

class RestaurantWorkingHoursController extends Controller
{
    public function __construct(
        private RestaurantService $service,
        private Restaurant $restaurants,
        private RestaurantApprovalAccess $access,
    ) {}

    private function ownedRestaurant(Request $request): Restaurant
    {
        $owner = $request->user('admin');
        abort_unless($this->access->isOwner($owner), 403);

        return $this->restaurants->newQuery()->where('admin_id', $owner->id)
            ->with('hours')->orderBy('id')->firstOrFail();
    }

    public function index(Request $request)
    {
        $restaurant = $this->ownedRestaurant($request);
        $hours = $this->service->getFormattedHours($restaurant);
        $canUpdateHours = $request->user('admin')->hasPermission('settings.restaurant.working-hours.update');

        return view('restaurant::admin.working-hours', compact('restaurant', 'hours', 'canUpdateHours'));
    }

    public function update(Request $request)
    {
        $restaurant = $this->ownedRestaurant($request);
        abort_unless($request->user('admin')->hasPermission('settings.restaurant.working-hours.index')
            && $request->user('admin')->hasPermission('settings.restaurant.working-hours.update'), 403);
        $data = $request->validate([
            'is_open_whole_day' => ['sometimes', 'boolean'],
            'hours' => ['nullable', 'array'],
            'hours.*' => ['array'],
            'hours.*.*' => ['array'],
            'hours.*.*.open' => ['required', 'boolean'],
            'hours.*.*.from' => ['required', 'date_format:H:i'],
            'hours.*.*.to' => ['required', 'date_format:H:i'],
        ]);

        try {
            $this->service->updateHours($restaurant->id, $data['hours'] ?? [], $request->boolean('is_open_whole_day'));
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Working hours saved');
    }
}
