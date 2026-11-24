<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Countries\Models\City;
use Modules\Countries\Models\Country;
use Modules\Countries\Models\CountryState;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;
use Modules\Restaurant\Services\RestaurantService;

class RestaurantGeneralInformationController extends Controller
{
    public function __construct(
        private RestaurantService $service,
        private Restaurant $restaurants,
        private RestaurantApprovalAccess $access,
    ) {}

    private function ownedRestaurant(Request $request): Restaurant
    {
        $owner = $request->user('admin');
        abort_unless($this->access->isOwner($owner)
            && $owner->hasPermission('settings.restaurant.general-information.index'), 403);

        return $this->restaurants->newQuery()->where('admin_id', $owner->id)
            ->with('admin')->orderBy('id')->firstOrFail();
    }

    public function index(Request $request)
    {
        $restaurant = $this->ownedRestaurant($request);
        $restaurantId = $restaurant->id;
        $canUpdateGeneral = $request->user('admin')->hasPermission('settings.restaurant.general-information.update');
        $canUpdateAddress = $request->user('admin')->hasPermission('settings.restaurant.general-information.address');
        $canDeleteGeneralImage = $canUpdateGeneral && $request->user('admin')->hasPermission('filemanager.delete');

        return view('restaurant::admin.general-information', compact('restaurant', 'restaurantId', 'canUpdateGeneral', 'canUpdateAddress', 'canDeleteGeneralImage'));
    }

    public function update(Request $request)
    {
        $restaurant = $this->ownedRestaurant($request);
        abort_unless($request->user('admin')->hasPermission('settings.restaurant.general-information.update'), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image'],
        ]);
        $this->service->updateGeneral($restaurant->id, $data);
        if ($request->hasFile('image')) {
            fn_upload_image($request->file('image'), 'restaurant', $restaurant->id, true);
        }

        return back()->with('success', 'Restaurant information updated successfully');
    }

    public function updateAddress(Request $request)
    {
        $restaurant = $this->ownedRestaurant($request);
        abort_unless($request->user('admin')->hasPermission('settings.restaurant.general-information.address'), 403);
        $data = $request->validate([
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['required', 'integer', Rule::exists('country_states', 'id')->where('country_id', $request->input('country_id'))],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')->where('country_id', $request->input('country_id'))->where('state_id', $request->input('state_id'))],
            'postal_code' => ['required', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        $data['country_name'] = Country::findOrFail($data['country_id'])->name;
        $data['state_name'] = CountryState::findOrFail($data['state_id'])->name;
        $data['city_name'] = City::findOrFail($data['city_id'])->name;
        $this->service->updateAddress($restaurant->id, $data);

        return back()->with('success', 'Address saved successfully');
    }
}
