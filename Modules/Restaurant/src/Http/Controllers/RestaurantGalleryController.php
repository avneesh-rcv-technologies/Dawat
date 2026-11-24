<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Filemanager\Models\FileLink;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantGalleryService;

class RestaurantGalleryController extends Controller
{
    public function __construct(protected RestaurantGalleryService $gallery) {}

    public function store(Request $request)
    {
        $request->validate(['restaurant_id' => 'required|integer|exists:restaurants,id']);
        $restaurant = $this->restaurant($request, (int) $request->input('restaurant_id'));
        $redirect = route('admin.restaurant.edit', ['id' => $restaurant->id, 'tab' => 'gallery']);

        try {
            $request->validate(RestaurantGalleryService::rules(true), RestaurantGalleryService::messages());
            $this->gallery->update($restaurant, $request->file('images', []));

            return redirect($redirect)->with('success', 'Gallery images added successfully.')->with('tab', 'gallery');
        } catch (ValidationException $e) {
            return redirect($redirect)->withErrors($e->errors())->with('tab', 'gallery');
        } catch (\Throwable $e) {
            Log::error('Restaurant gallery upload failed.', ['restaurant_id' => $restaurant->id, 'exception' => $e]);

            return redirect($redirect)->with('error', 'Unable to upload gallery images. Please try again.')->with('tab', 'gallery');
        }
    }

    public function destroy(Request $request, int $id)
    {
        $link = FileLink::where('object_type', 'restaurant_gallery')->findOrFail($id);
        $restaurant = $this->restaurant($request, (int) $link->object_id);
        $redirect = route('admin.restaurant.edit', ['id' => $restaurant->id, 'tab' => 'gallery']);

        try {
            $this->gallery->update($restaurant, [], [$link->id]);
        } catch (\Throwable $e) {
            Log::error('Restaurant gallery deletion failed.', ['restaurant_id' => $restaurant->id, 'exception' => $e]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unable to delete this image. Please try again.'], 500);
            }

            return redirect($redirect)->with('error', 'Unable to delete this image. Please try again.')->with('tab', 'gallery');
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Gallery image deleted successfully.', 'redirect_url' => $redirect]);
        }

        return redirect($redirect)->with('success', 'Gallery image deleted successfully.')->with('tab', 'gallery');
    }

    private function restaurant(Request $request, int $id): Restaurant
    {
        $restaurant = Restaurant::findOrFail($id);
        $admin = $request->user('admin');
        abort_if(app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($admin), 403);

        return $restaurant;
    }
}
