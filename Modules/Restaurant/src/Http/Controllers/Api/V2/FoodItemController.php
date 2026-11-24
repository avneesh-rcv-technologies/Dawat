<?php

namespace Modules\Restaurant\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\DietaryManagment\Models\DietaryLabel;
use Modules\DietaryManagment\Services\LabelCertificateEligibility;
use Modules\FoodManagements\Models\FoodItem;
use Modules\FoodManagements\Services\FoodImageUploadPolicy;
use Modules\FoodManagements\Services\ServingOptionConfiguration;
use Modules\Restaurant\Http\Resources\V2\FoodItemResource;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantFoodItemV2Service;

class FoodItemController
{
    public function __construct(
        private RestaurantFoodItemV2Service $items,
        private ServingOptionConfiguration $configuration,
        private LabelCertificateEligibility $eligibility,
    ) {
    }

    public function formOptions(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $eligibility = $this->eligibility->forRestaurant($restaurant->id);
        $labels = DietaryLabel::active()->orderBy('display_order')->orderBy('name')->get();

        return $this->response('Food item form options retrieved successfully.', [
            'restaurant_id' => $restaurant->id,
            'categories' => $this->items->categories($restaurant),
            'features' => collect(app(\Modules\Restaurant\Services\RestaurantFoodFeatureService::class)->activeListsForRestaurant($restaurant->id))
                ->whereIn('feature_type', array_keys(\Modules\Restaurant\Services\RestaurantFoodFeatureService::TYPES))->values(),
            'dietary_labels' => $labels->map(fn($label) => [
                'id' => (string) $label->id,
                'name' => $label->name,
                'code' => $label->code,
                'description' => $label->description,
                'certification_type_id' => $label->certificationType?->id,
                ...($eligibility[(string) $label->id] ?? []),
            ])->values(),
            'serving_options' => $this->configuration->all(),
            'image_upload' => [
                'field' => 'food_image[]',
                'max_files' => FoodImageUploadPolicy::MAX_IMAGES,
                'alternative_fields' => ['images[]'],
                'replace_gallery_field' => 'replace_images',
                'max_size_mb' => FoodImageUploadPolicy::MAX_SIZE_KB / 1024,
                'allowed_extensions' => FoodImageUploadPolicy::EXTENSIONS,
            ],
        ]);
    }

    public function servingOptions(Request $request)
    {
        $this->restaurant($request);

        return $this->response('Serving options retrieved successfully.', $this->configuration->all());
    }

    public function index(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', Rule::exists('food_categories', 'id')->where('restaurant_id', $restaurant->id)],
            'is_active' => ['sometimes', 'boolean'],
            'is_available' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $page = $this->items->listing($restaurant, $filters);
        $eligibility = $this->eligibility->forRestaurant($restaurant->id);
        $config = $this->configuration->all();

        return response()->json([
            'success' => true,
            'message' => 'Food items retrieved successfully.',
            'data' => $page->getCollection()->map(fn($item) => (new FoodItemResource($item, $eligibility, $config))->resolve($request)),
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
                'has_more_pages' => $page->hasMorePages(),
            ],
        ]);
    }

    public function show(Request $request, int $id)
    {
        $restaurant = $this->restaurant($request);

        return $this->itemResponse($request, $restaurant, $this->items->find($restaurant, $id), 'Food item retrieved successfully.');
    }

    public function store(Request $request)
    {
        $restaurant = $this->restaurant($request);

        return $this->itemResponse($request, $restaurant, $this->items->create($restaurant, $request), 'Food item created successfully.', 201);
    }

    public function update(Request $request, int $id)
    {
        $restaurant = $this->restaurant($request);

        return $this->itemResponse($request, $restaurant, $this->items->update($restaurant, $id, $request), 'Food item updated successfully.');
    }

    public function status(Request $request, int $id)
    {
        $restaurant = $this->restaurant($request);
        $data = $request->validate([
            'is_active' => ['required_without:is_available', 'boolean'],
            'is_available' => ['required_without:is_active', 'boolean'],
            'restaurant_id' => ['prohibited'],
            'admin_id' => ['prohibited'],
        ]);
        unset($data['restaurant_id'], $data['admin_id']);

        return $this->itemResponse($request, $restaurant, $this->items->updateStatus($restaurant, $id, $data), 'Food item status updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->items->delete($this->restaurant($request), $id);

        return $this->response('Food item deleted successfully.', null);
    }

    public function addServingOption(Request $request, int $id)
    {
        return $this->changeServingOption($request, $id, 'add');
    }

    public function updateServingOption(Request $request, int $id, int $optionId)
    {
        return $this->changeServingOption($request, $id, 'update', $optionId);
    }

    public function deleteServingOption(Request $request, int $id, int $optionId)
    {
        return $this->changeServingOption($request, $id, 'delete', $optionId);
    }

    private function changeServingOption(Request $request, int $id, string $action, ?int $optionId = null)
    {
        $restaurant = $this->restaurant($request);
        $request->validate(['id' => ['prohibited'], 'food_item_id' => ['prohibited'], 'restaurant_id' => ['prohibited'], 'admin_id' => ['prohibited']]);
        $item = $this->items->changeServingOption($restaurant, $id, $request, $action, $optionId);
        $messages = ['add' => 'Serving option added successfully.', 'update' => 'Serving option updated successfully.', 'delete' => 'Serving option deleted successfully.'];

        return $this->itemResponse($request, $restaurant, $item, $messages[$action], $action === 'add' ? 201 : 200);
    }

    private function restaurant(Request $request): Restaurant
    {
        return $this->items->restaurantForUser($request->user());
    }

    private function itemResponse(Request $request, Restaurant $restaurant, FoodItem $item, string $message, int $status = 200)
    {
        return $this->response($message, (new FoodItemResource(
            $item,
            $this->eligibility->forRestaurant($restaurant->id),
            $this->configuration->all()
        ))->resolve($request), $status);
    }

    private function response(string $message, $data, int $status = 200)
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status)
            ->header('Cache-Control', 'private, no-store');
    }
}
