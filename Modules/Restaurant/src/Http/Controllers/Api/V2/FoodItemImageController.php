<?php

namespace Modules\Restaurant\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Modules\FoodManagements\Services\FoodImageUploadPolicy;
use Modules\Restaurant\Services\RestaurantFoodItemImageService;
use Modules\Restaurant\Services\RestaurantFoodItemV2Service;

class FoodItemImageController
{
    public function __construct(private RestaurantFoodItemImageService $images, private RestaurantFoodItemV2Service $items) {}

    public function index(Request $request, int $id)
    {
        return $this->response('Food item images retrieved successfully.',
            $this->images->gallery($this->items->restaurantForUser($request->user()), $id));
    }

    public function store(Request $request, int $id)
    {
        return $this->saveGallery($request, $id, false);
    }

    public function storeSingle(Request $request, int $id)
    {
        $restaurant = $this->items->restaurantForUser($request->user());
        $request->validate([
            'image' => $this->singleImageRules(),
        ] + $this->ownershipRules(), $this->messages());

        return $this->response('Food item single image uploaded successfully.',
            $this->images->saveSingle($restaurant, $id, $request->file('image')), 201);
    }

    public function replace(Request $request, int $id)
    {
        return $this->saveGallery($request, $id, true);
    }

    public function update(Request $request, int $id, int $imageId)
    {
        $restaurant = $this->items->restaurantForUser($request->user());
        $request->validate(['image' => $this->fileRules()] + $this->ownershipRules(), $this->messages());

        return $this->response('Food item image updated successfully.',
            $this->images->save($restaurant, $id, [$request->file('image')], false, $imageId));
    }

    public function destroy(Request $request, int $id, int $imageId)
    {
        $restaurant = $this->items->restaurantForUser($request->user());

        return $this->response('Food item image deleted successfully.', $this->images->delete($restaurant, $id, [$imageId]));
    }

    public function destroyMany(Request $request, int $id)
    {
        $restaurant = $this->items->restaurantForUser($request->user());
        $data = $request->validate([
            'image_ids' => ['required', 'array', 'list', 'min:1', 'max:10'],
            'image_ids.*' => ['required', 'integer', 'distinct', 'min:1'],
        ] + $this->ownershipRules());

        return $this->response('Food item images deleted successfully.', $this->images->delete($restaurant, $id, $data['image_ids']));
    }

    private function saveGallery(Request $request, int $id, bool $replace)
    {
        $restaurant = $this->items->restaurantForUser($request->user());
        $request->validate([
            'images' => ['required', 'array', 'list', 'min:1', 'max:10'],
            'images.*' => $this->fileRules(),
        ] + $this->ownershipRules(), $this->messages());

        return $this->response($replace ? 'Food item images updated successfully.' : 'Food item images uploaded successfully.',
            $this->images->save($restaurant, $id, $request->file('images'), $replace), $replace ? 200 : 201);
    }

    private function fileRules(): array
    {
        return FoodImageUploadPolicy::fileRules();
    }

    private function singleImageRules(): array
    {
        return $this->fileRules();
    }

    private function ownershipRules(): array
    {
        return ['restaurant_id' => ['prohibited'], 'admin_id' => ['prohibited'], 'food_item_id' => ['prohibited']];
    }

    private function messages(): array
    {
        return [
            ...FoodImageUploadPolicy::messages('images'),
            'image.required' => 'Please upload a replacement image.',
            'image.file' => 'Please select a valid food image file.',
            'image.image' => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            'image.max' => 'The food image must be 2 MB or smaller.',
            'image.mimes' => 'The food image must be a JPG, JPEG, PNG, or WEBP file.',
            'image.extensions' => 'The food image must have a .jpg, .jpeg, .png, or .webp extension.',
        ];
    }

    private function response(string $message, array $data, int $status = 200)
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }
}
