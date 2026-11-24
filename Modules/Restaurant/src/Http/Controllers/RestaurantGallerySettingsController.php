<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Filemanager\Models\FileLink;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;
use Modules\Restaurant\Services\RestaurantGalleryService;

class RestaurantGallerySettingsController extends Controller
{
    public function __construct(
        private RestaurantGalleryService $gallery,
        private Restaurant $restaurants,
        private FileLink $links,
        private RestaurantApprovalAccess $access,
    ) {}

    private function ownedRestaurant(Request $request): Restaurant
    {
        $owner = $request->user('admin');
        abort_unless($this->access->isOwner($owner), 403);
        abort_unless($owner->hasPermission('settings.restaurant.gallery.index'), 403);
        $restaurant = $this->restaurants->newQuery()->where('admin_id', $owner->id)
            ->orderBy('id')->firstOrFail();
        abort_unless($restaurant->canManageFeatures(), 403, 'Both onboarding and application approval are required.');

        return $restaurant;
    }

    public function index(Request $request)
    {
        $restaurant = $this->ownedRestaurant($request);
        $restaurantId = $restaurant->id;
        $canUploadGallery = $request->user('admin')->hasPermission('settings.restaurant.gallery.store');
        $canDeleteGallery = $request->user('admin')->hasPermission('settings.restaurant.gallery.destroy');

        return view('restaurant::admin.gallery-settings', compact('restaurant', 'restaurantId', 'canUploadGallery', 'canDeleteGallery'));
    }

    public function store(Request $request)
    {
        $restaurant = $this->ownedRestaurant($request);
        abort_unless($request->user('admin')->hasPermission('settings.restaurant.gallery.store'), 403);
        $request->validate(RestaurantGalleryService::rules(true), RestaurantGalleryService::messages());
        $this->gallery->update($restaurant, $request->file('images', []));

        return back()->with('success', 'Gallery images added successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $restaurant = $this->ownedRestaurant($request);
        abort_unless($request->user('admin')->hasPermission('settings.restaurant.gallery.destroy'), 403);
        $link = $this->links->newQuery()->where('object_type', 'restaurant_gallery')
            ->where('object_id', $restaurant->id)->findOrFail($id);
        $this->gallery->update($restaurant, [], [$link->id]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Gallery image deleted successfully.',
                'redirect_url' => route('settings.restaurant.gallery.index'),
            ]);
        }

        return back()->with('success', 'Gallery image deleted successfully.');
    }
}
