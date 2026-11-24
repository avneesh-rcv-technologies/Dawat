<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;
use Modules\Restaurant\Services\RestaurantService;

class RestaurantHolidayController extends Controller
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
            ->with('holidays')->orderBy('id')->firstOrFail();
    }

    public function index(Request $request)
    {
        $restaurant = $this->ownedRestaurant($request);
        $holidays = $this->service->getFormattedHolidays($restaurant);
        $canUpdateHolidays = $request->user('admin')->hasPermission('settings.restaurant.holidays.update');

        return view('restaurant::admin.holidays', compact('restaurant', 'holidays', 'canUpdateHolidays'));
    }

    public function update(Request $request)
    {
        $restaurant = $this->ownedRestaurant($request);
        abort_unless($request->user('admin')->hasPermission('settings.restaurant.holidays.index')
            && $request->user('admin')->hasPermission('settings.restaurant.holidays.update'), 403);
        $data = $request->validate([
            'holidays' => ['nullable', 'array'],
            'holidays.*' => ['array'],
            'holidays.*.date' => ['required', 'date'],
            'holidays.*.reason' => ['required', 'string', 'max:255'],
            'holidays.*.is_closed' => ['nullable', 'boolean'],
        ], [
            'holidays.*.reason.required' => 'Please enter a reason for every holiday.',
        ]);

        $this->service->updateHolidays($restaurant->id, $data['holidays'] ?? []);

        return back()->with('success', 'Holidays saved successfully');
    }
}
