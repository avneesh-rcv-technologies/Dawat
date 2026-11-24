<?php

namespace Modules\Restaurant\Services;

use App\Services\CacheService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\DietaryManagment\Services\LabelCertificateEligibility;
use Modules\Filemanager\Services\FileService;
use Modules\Filemanager\Models\FileLink;
use Modules\FoodManagements\Http\Requests\SaveFoodItemRequest;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodItem;
use Modules\FoodManagements\Services\FoodItemService;
use Modules\FoodManagements\Services\FoodItemFeatureData;
use Modules\FoodManagements\Services\ServingOptionConfiguration;
use Modules\Restaurant\Models\Restaurant;

class RestaurantFoodItemV2Service
{
    public function __construct(private FoodItemService $items, private LabelCertificateEligibility $eligibility) {}

    public function restaurantForUser($user): Restaurant
    {
        abort_unless($user instanceof Admin && $user->status && $user->email_verified_at, 403, 'An active, verified restaurant owner account is required.');

        return Restaurant::where('admin_id', $user->id)->whereIn('status', ['draft', 'active'])->orderBy('id')->firstOrFail();
    }

    public function listing(Restaurant $restaurant, array $filters): LengthAwarePaginator
    {
        $query = FoodItem::where('restaurant_id', $restaurant->id)->with(['categories', 'servingOptions', ...FoodItemFeatureData::RELATIONS]);
        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }
        if (! empty($filters['category_id'])) {
            $query->whereHas('categories', fn ($query) => $query->where('food_categories.id', $filters['category_id']));
        }
        foreach (['is_active', 'is_available'] as $field) {
            if (array_key_exists($field, $filters)) {
                $query->where($field, $filters[$field]);
            }
        }

        return $query->orderByDesc('id')->paginate($filters['per_page'] ?? 15);
    }

    public function find(Restaurant $restaurant, int $id, bool $lock = false): FoodItem
    {
        return FoodItem::where('restaurant_id', $restaurant->id)->with(['categories', 'servingOptions', ...FoodItemFeatureData::RELATIONS])
            ->when($lock, fn ($query) => $query->lockForUpdate())->findOrFail($id);
    }

    public function categories(Restaurant $restaurant)
    {
        return FoodCategory::where('restaurant_id', $restaurant->id)->orderBy('name')->get(['id', 'name', 'status']);
    }

    public function create(Restaurant $restaurant, Request $request): FoodItem
    {
        return DB::transaction(function () use ($restaurant, $request) {
            $this->lockRestaurant($restaurant);
            $data = $this->validate($restaurant, $request, $request->all());
            $data['restaurant_id'] = $restaurant->id;
            $data['is_active'] ??= true;
            $data['is_available'] ??= true;
            $item = $this->items->create($this->saveRequest($request, $data), $data);
            if (array_key_exists('features', $data)) {
                app(RestaurantFoodItemFeatureService::class)->save($item, $data['features']);
            }
            $this->forgetAfterCommit($item->id);

            return $this->find($restaurant, $item->id);
        });
    }

    public function update(Restaurant $restaurant, int $id, Request $request): FoodItem
    {
        return DB::transaction(function () use ($restaurant, $id, $request) {
            $this->lockRestaurant($restaurant);
            $item = $this->find($restaurant, $id, true);
            if (! array_intersect(array_keys($request->all()), [
                'name', 'description', 'category_id', 'tax_percentage', 'dietary_label_ids',
                'is_active', 'is_available', 'serving_options', 'food_image', 'images', 'replace_images', 'remove_image_ids', 'features',
            ])) {
                throw ValidationException::withMessages(['request' => 'Provide at least one food item field to update.']);
            }

            return $this->saveUpdate($restaurant, $item, $request, $request->all());
        });
    }

    public function changeServingOption(Restaurant $restaurant, int $id, Request $request, string $action, ?int $optionId = null): FoodItem
    {
        return DB::transaction(function () use ($restaurant, $id, $request, $action, $optionId) {
            $this->lockRestaurant($restaurant);
            $item = $this->find($restaurant, $id, true);
            $rows = $this->optionRows($item);
            if ($action === 'add') {
                $rows[] = $request->all();
            } else {
                $position = array_search($optionId, array_column($rows, 'id'), true);
                abort_if($position === false, 404, 'Serving option not found for this food item.');
                if ($action === 'delete') {
                    array_splice($rows, $position, 1);
                } else {
                    $rows[$position] = array_replace($rows[$position], $request->all());
                }
            }

            return $this->saveUpdate($restaurant, $item, $request, ['serving_options' => array_values($rows)]);
        });
    }

    public function updateStatus(Restaurant $restaurant, int $id, array $data): FoodItem
    {
        return DB::transaction(function () use ($restaurant, $id, $data) {
            $this->lockRestaurant($restaurant);
            $item = $this->find($restaurant, $id, true);
            // Owners can always stop offering an item, including after certificate rejection.
            if (in_array(true, array_map(fn ($value) => (bool) $value, $data), true)) {
                $this->eligibility->assertAllowed($restaurant->id, $item->dietary_label_ids ?? []);
            }
            $item->update($data);
            $this->forgetAfterCommit($id);

            return $this->find($restaurant, $id);
        });
    }

    public function delete(Restaurant $restaurant, int $id): void
    {
        DB::transaction(function () use ($restaurant, $id) {
            $this->lockRestaurant($restaurant);
            $item = $this->find($restaurant, $id, true);
            $item->delete();
            $this->forgetAfterCommit($id);
            DB::afterCommit(fn () => app(FileService::class)->deleteFile('food_image', $id));
        });
    }

    private function validate(Restaurant $restaurant, Request $request, array $data, ?FoodItem $item = null): array
    {
        if (array_key_exists('images', $data)) {
            if (array_key_exists('food_image', $data)) {
                throw ValidationException::withMessages(['images' => 'Send either images[] or food_image[], not both.']);
            }
            $data['food_image'] = $data['images'];
            unset($data['images']);
        }
        if (is_array($data['dietary_label_ids'] ?? null)) {
            $data['dietary_label_ids'] = array_map(fn ($id) => is_int($id) ? (string) $id : $id, $data['dietary_label_ids']);
        }
        $validationRequest = SaveFoodItemRequest::createFrom($request);
        $validationRequest->replace($data);
        $rules = $validationRequest->rules();
        $rules['restaurant_id'] = ['prohibited'];
        $rules['admin_id'] = ['prohibited'];
        $rules['features'] = ['sometimes', 'array', 'max:50'];
        $rules['replace_images'] = ['sometimes', 'boolean'];
        $rules['remove_image_ids'] = $item ? ['sometimes', 'array', 'list', 'min:1', 'max:10',
            Rule::prohibitedIf(in_array($data['replace_images'] ?? false, [true, 1, '1'], true))] : ['prohibited'];
        $rules['remove_image_ids.*'] = ['required', 'integer', 'distinct', 'min:1',
            Rule::exists('file_links', 'id')->where('object_type', 'food_image')->where('object_id', $item?->id ?? 0)];
        if (in_array($data['replace_images'] ?? false, [true, 1, '1'], true)) {
            $rules['food_image'][] = 'required';
            $rules['food_image'][] = 'min:1';
        }
        $rules['category_id'] = ['required', 'integer', Rule::exists('food_categories', 'id')->where('restaurant_id', $restaurant->id)];
        $rules['serving_options.*.id'] = [
            'nullable', 'integer', 'distinct', Rule::exists('food_item_serving_options', 'id')->where('food_item_id', $item?->id ?? 0),
        ];

        $messages = [
            ...$validationRequest->messages(),
            'category_id.exists' => 'Choose a category belonging to your restaurant.',
            'remove_image_ids.*.exists' => 'Choose an image belonging to this food item.',
            'remove_image_ids.prohibited' => 'Remove selected images or replace the gallery; do not combine remove_image_ids with replace_images.',
            'serving_options.required' => 'At least one serving option is required.',
            'serving_options.min' => 'At least one serving option is required.',
            'serving_options.max' => 'You can add up to :max serving options.',
            'serving_options.*.id.exists' => 'Choose a serving option belonging to this food item.',
            'serving_options.*.unit.in' => 'Choose an allowed unit for the selected serve type.',
            'serving_options.*.serve_type.in' => 'Choose a serve type from the serving options configuration.',
        ];
        $attributes = $validationRequest->attributes();
        $config = app(ServingOptionConfiguration::class)->all();
        foreach (is_array($data['serving_options'] ?? null) ? $data['serving_options'] : [] as $index => $row) {
            $key = is_array($row) ? ($row['serve_type'] ?? null) : null;
            $type = is_string($key) ? ($config['serve_types'][$key] ?? null) : null;
            if (! $type || ! ctype_digit((string) $index)) {
                continue;
            }
            foreach ($config['fields'] as $field => $definition) {
                $attributes['serving_options.'.$index.'.'.$field] = $type['label'].' option '.($index + 1).' '.($type['field_labels'][$field] ?? $definition['label']);
            }
            if ($type['allowed_units']) {
                $messages['serving_options.'.$index.'.unit.in'] = $type['label'].' requires one of these units: '.implode(', ', $type['allowed_units']).'.';
            }
        }

        $validated = Validator::make($data, $rules, $messages, $attributes)->validate();
        // Check the final label selection before image removals, uploads or item writes.
        // Updates include the item's existing labels when the request omits them.
        $this->eligibility->assertAllowed($restaurant->id, $validated['dietary_label_ids']);
        if (array_key_exists('features', $validated)) {
            $validated['features'] = app(RestaurantFoodItemFeatureService::class)->normalize($restaurant->id, $validated['features']);
        }

        return $validated;
    }

    private function saveUpdate(Restaurant $restaurant, FoodItem $item, Request $request, array $changes): FoodItem
    {
        $current = $item->only(['name', 'description', 'tax_percentage', 'dietary_label_ids', 'is_active', 'is_available']);
        $current['category_id'] = $item->categories->first()?->id;
        $current['serving_options'] = $this->optionRows($item);
        $data = $this->validate($restaurant, $request, array_replace($current, $changes), $item);
        if (! empty($data['remove_image_ids'])) {
            $links = FileLink::with('file')->where('object_type', 'food_image')->where('object_id', $item->id)
                ->whereIn('id', $data['remove_image_ids'])->lockForUpdate()->get();
            if ($links->count() !== count($data['remove_image_ids'])) {
                throw ValidationException::withMessages(['remove_image_ids' => 'One or more images are no longer available for this food item.']);
            }
            foreach ($links as $link) {
                app(FileService::class)->deleteFileLinkAfterCommit($link);
            }
        }
        $data['restaurant_id'] = $restaurant->id;
        $this->items->update($item->id, $this->saveRequest($request, $data), $data);
        if (array_key_exists('features', $data)) {
            app(RestaurantFoodItemFeatureService::class)->save($item, $data['features']);
        }
        $this->forgetAfterCommit($item->id);

        return $this->find($restaurant, $item->id);
    }

    private function optionRows(FoodItem $item): array
    {
        return $item->servingOptions->map->only([
            'id', 'serve_type', 'serving_name', 'quantity', 'unit', 'serves', 'min_guests',
            'max_guests', 'price', 'discount_price', 'is_available',
        ])->values()->all();
    }

    private function lockRestaurant(Restaurant $restaurant): void
    {
        Restaurant::whereKey($restaurant->id)->where('admin_id', $restaurant->admin_id)
            ->whereIn('status', ['draft', 'active'])->lockForUpdate()->firstOrFail();
    }

    private function saveRequest(Request $request, array $data): Request
    {
        // The shared web service also supports legacy fields; expose only validated v2 fields.
        $files = array_intersect_key($request->allFiles(), $data);
        if (isset($data['food_image'])) {
            // images[] is normalized to food_image[] during validation.
            $files['food_image'] = $data['food_image'];
        }
        $clean = Request::create($request->url(), $request->method(), $data, [], $files);
        $clean->setUserResolver($request->getUserResolver());

        return $clean;
    }

    private function forgetAfterCommit(int $id): void
    {
        DB::afterCommit(fn () => CacheService::forgetFoodItem($id));
    }
}
