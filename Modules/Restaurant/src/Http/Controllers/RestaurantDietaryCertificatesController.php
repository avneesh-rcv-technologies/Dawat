<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\DietaryManagment\Services\OnboardingCertificateService;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;

class RestaurantDietaryCertificatesController extends Controller
{
    public function __construct(
        private OnboardingCertificateService $certificates,
        private Restaurant $restaurants,
        private RestaurantApprovalAccess $access,
    ) {}

    private function owner(Request $request)
    {
        $owner = $request->user('admin');
        abort_unless($this->access->isOwner($owner)
            && $owner->hasPermission('settings.restaurant.dietary-certificates.index'), 403);

        return $owner;
    }

    public function index(Request $request)
    {
        $owner = $this->owner($request);
        $data = $request->validate(['restaurant_id' => ['sometimes', 'required', 'integer', 'min:1']]);
        $certificateRestaurants = $this->restaurants->newQuery()->where('admin_id', $owner->id)->orderBy('name')->get();
        $certificateRestaurant = isset($data['restaurant_id'])
            ? $certificateRestaurants->firstWhere('id', (int) $data['restaurant_id']) : $certificateRestaurants->first();
        abort_if(isset($data['restaurant_id']) && !$certificateRestaurant, 404);
        $certificateData = $certificateRestaurant ? $this->certificates->screen($certificateRestaurant) : [];
        $canUploadCertificates = $owner->hasPermission('settings.restaurant.dietary-certificates.store');

        return view('restaurant::admin.dietary-certificates', compact('certificateRestaurants', 'certificateRestaurant', 'certificateData', 'canUploadCertificates'));
    }

    public function store(Request $request)
    {
        $owner = $this->owner($request);
        abort_unless($owner->hasPermission('settings.restaurant.dietary-certificates.store'), 403);
        $data = $request->validate([
            'restaurant_id' => ['required', 'integer', 'min:1'],
            'dietary_label_id' => ['required', 'string', 'max:36'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
        $restaurant = $this->restaurants->newQuery()->where('admin_id', $owner->id)
            ->whereKey($data['restaurant_id'])->firstOrFail();
        $this->certificates->upload($owner, $restaurant->id, $data['dietary_label_id'], $request->file('document'));

        return redirect()->route('settings.restaurant.dietary-certificates.index', ['restaurant_id' => $restaurant->id])
            ->with('success', 'Certificate submitted. You can use this dietary label after admin approval.');
    }
}
