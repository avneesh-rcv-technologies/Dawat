<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\DietaryManagment\Services\LabelCertificateEligibility;
use Modules\DietaryManagment\Models\DietaryLabel;
use Modules\FoodManagements\Http\Requests\SaveFoodItemRequest;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodFeature;
use Modules\FoodManagements\Models\FoodItem;
use Modules\FoodManagements\Services\CategoryService;
use Modules\Filemanager\Models\FileLink;
use Modules\FoodManagements\Services\FoodFeatureService;
use Modules\FoodManagements\Services\FoodImageUploadPolicy;
use Modules\FoodManagements\Services\FoodItemService;
use Modules\FoodManagements\Services\FoodItemFeatureData;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Support\MenuContext;
use Modules\Restaurant\Services\RestaurantOnboardingAccessService;

/**
 * Optional "Menu details" section of restaurant onboarding.
 *
 * Lets the restaurant owner build their menu (food items, categories and item features)
 * before onboarding is approved, reusing the FoodManagements services used by the admin panel.
 * Every query is scoped to the logged-in owner's restaurant.
 */
class OnboardingMenuController extends Controller
{
    public const TABS = ['items', 'categories', 'features'];

    /** Feature types offered to restaurants, mapped to the FoodFeatureService purpose/styles. */
    public const FEATURE_TYPES = [
        'S' => ['label' => 'Single choice', 'help' => 'Customers see one option, e.g. Spice level: Mild / Medium / Hot.', 'options' => true, 'purpose' => 'find_products', 'feature_style' => 'text', 'filter_style' => 'checkbox'],
        'M' => ['label' => 'Multiple choice', 'help' => 'Several options can apply, e.g. Contains: Nuts, Dairy, Eggs.', 'options' => true, 'purpose' => 'find_products', 'feature_style' => 'multiple_checkbox', 'filter_style' => 'checkbox'],
        'C' => ['label' => 'Yes / No', 'help' => 'A simple tick, e.g. Chef\'s special.', 'options' => false, 'purpose' => 'find_products', 'feature_style' => 'checkbox', 'filter_style' => 'checkbox'],
        'T' => ['label' => 'Text', 'help' => 'Free text, e.g. Origin: Punjab.', 'options' => false, 'purpose' => 'describe_product', 'feature_style' => 'text', 'filter_style' => ''],
        'O' => ['label' => 'Number', 'help' => 'A number, e.g. Calories: 450.', 'options' => false, 'purpose' => 'find_products', 'feature_style' => 'number', 'filter_style' => 'slider'],
    ];

    protected MenuContext $menu;

    public function __construct(
        private readonly FoodItemService $items,
        private readonly CategoryService $categories,
        private readonly FoodFeatureService $features,
    ) {
    }

    /* ================= LISTING ================= */

    public function index(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'items';
        $search = trim((string) $request->query('q', ''));

        $foodItems = FoodItem::where('restaurant_id', $restaurant->id)
            ->with(['categories:id,name', 'servingOptions'])
            ->withCount('featureValues')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%' . addcslashes($search, '%_\\') . '%'))
            ->latest('id')
            ->paginate(10, ['*'], 'items_page')
            ->withQueryString();

        $categoryList = FoodCategory::where('restaurant_id', $restaurant->id)
            ->withCount('foodItems')
            ->orderBy('name')
            ->get();

        return response()->view('restaurant::onboarding.menu.index', [
            'restaurant' => $restaurant,
            'tab' => $tab,
            'search' => $search,
            'foodItems' => $foodItems,
            'categoryList' => $categoryList,
            'featureList' => $tab === 'features' ? $this->featureList($restaurant) : collect(),
        ])->header('Cache-Control', 'no-store, private');
    }

    /* ================= FOOD ITEMS ================= */

    public function createItem(Request $request)
    {
        return $this->itemForm($this->restaurant($request), null);
    }

    public function editItem(Request $request, int $id)
    {
        $restaurant = $this->restaurant($request);

        return $this->itemForm($restaurant, $this->ownedItem($restaurant, $id));
    }

    public function showItem(Request $request, int $id)
    {
        $restaurant = $this->restaurant($request);
        $item = $this->ownedItem($restaurant, $id);
        $item->load([
            'categories.parent', 'servingOptions', 'tags', 'optionGroups.options',
            'options', ...FoodItemFeatureData::RELATIONS,
        ]);

        return response()->view('restaurant::onboarding.menu.item-show', [
            'restaurant' => $restaurant,
            'item' => $item,
            'itemFeatures' => FoodItemFeatureData::forItem($item),
            'dietaryLabels' => DietaryLabel::whereIn('id', $item->dietary_label_ids ?? [])
                ->orderBy('display_order')->get(),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function storeItem(SaveFoodItemRequest $request)
    {
        $restaurant = $this->restaurant($request);
        $data = $this->itemData($request, $restaurant);

        $item = $this->items->create($request, $data);
        $this->saveItemFeatures($request, $restaurant, $item->id);

        return redirect($this->menu->route('items.edit', ['id' => $item->id]))
            ->with('success', 'Food item saved successfully.');
    }

    public function updateItem(SaveFoodItemRequest $request, int $id)
    {
        $restaurant = $this->restaurant($request);
        $this->ownedItem($restaurant, $id);
        $data = $this->itemData($request, $restaurant);

        // Remove saved images the user marked, before new uploads are counted.
        $this->removeItemImages($request, $id);
        $this->items->update($id, $request, $data);
        $this->saveItemFeatures($request, $restaurant, $id);
        CacheService::forgetFoodItem($id);

        return redirect($this->menu->route('items.edit', ['id' => $id]))
            ->with('success', 'Food item updated successfully.');
    }

    public function destroyItem(Request $request, int $id)
    {
        $restaurant = $this->restaurant($request);
        $this->ownedItem($restaurant, $id);

        $this->items->delete($id);
        CacheService::forgetFoodItem($id);

        return redirect($this->menu->route('index', ['tab' => 'items']))
            ->with('success', 'Food item deleted successfully.');
    }

    /* ================= CATEGORIES ================= */

    public function createCategory(Request $request)
    {
        return $this->categoryForm($this->restaurant($request), null);
    }

    public function editCategory(Request $request, int $id)
    {
        $restaurant = $this->restaurant($request);

        return $this->categoryForm($restaurant, $this->ownedCategory($restaurant, $id));
    }

    public function storeCategory(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $data = $this->validateCategory($request, $restaurant);

        $category = $this->categories->create($data);
        if ($request->hasFile('category_image')) {
            fn_upload_image($request->file('category_image'), 'category_image', $category->id, true);
        }

        return $this->afterCategorySave($request, 'Category created successfully.');
    }

    public function updateCategory(Request $request, int $id)
    {
        $restaurant = $this->restaurant($request);
        $category = $this->ownedCategory($restaurant, $id);
        $data = $this->validateCategory($request, $restaurant, $category);

        $this->categories->update($id, $data);
        if ($request->hasFile('category_image')) {
            fn_upload_image($request->file('category_image'), 'category_image', $id, true);
        }

        return $this->afterCategorySave($request, 'Category updated successfully.');
    }

    public function destroyCategory(Request $request, int $id)
    {
        $restaurant = $this->restaurant($request);
        $category = $this->ownedCategory($restaurant, $id);

        if ($category->foodItems()->exists()) {
            return redirect($this->menu->route('index', ['tab' => 'categories']))
                ->with('error', 'This category still has food items. Move or delete those items before deleting the category.');
        }

        $this->categories->delete($id);

        return redirect($this->menu->route('index', ['tab' => 'categories']))
            ->with('success', 'Category deleted successfully.');
    }

    /* ================= FEATURES ================= */

    public function createFeature(Request $request)
    {
        return $this->featureForm($this->restaurant($request), null);
    }

    public function editFeature(Request $request, int $id)
    {
        $restaurant = $this->restaurant($request);

        return $this->featureForm($restaurant, $this->ownedFeature($restaurant, $id));
    }

    public function storeFeature(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $this->features->create($this->validateFeature($request, $restaurant));

        return redirect($this->menu->route('index', ['tab' => 'features']))
            ->with('success', 'Feature created. You can now set it on your food items.');
    }

    public function updateFeature(Request $request, int $id)
    {
        $restaurant = $this->restaurant($request);
        $feature = $this->ownedFeature($restaurant, $id);
        $this->features->update($feature, $this->validateFeature($request, $restaurant, $feature));
        $this->forgetRestaurantItems($restaurant);

        return redirect($this->menu->route('index', ['tab' => 'features']))
            ->with('success', 'Feature updated successfully.');
    }

    public function destroyFeature(Request $request, int $id)
    {
        $restaurant = $this->restaurant($request);
        $this->features->delete($this->ownedFeature($restaurant, $id));
        $this->forgetRestaurantItems($restaurant);

        return redirect($this->menu->route('index', ['tab' => 'features']))
            ->with('success', 'Feature deleted.');
    }

    /**
     * Data source for the item form's feature selector (advanced-selector apiRoute):
     * this restaurant's active features, searchable and paged, or the given ?ids[].
     */
    public function lookupFeatures(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'ids' => ['nullable', 'array', 'max:100'],
            'ids.*' => ['integer'],
        ]);
        $page = (int) ($data['page'] ?? 1);
        $perPage = (int) ($data['per_page'] ?? 20);
        $search = trim((string) ($data['search'] ?? ''));

        $query = FoodFeature::forRestaurant($restaurant->id)
            ->where('feature_type', '!=', 'G')
            ->where('status', 'A')
            ->with('description')
            ->when(isset($data['ids']), fn ($q) => $q->whereIn('feature_id', $data['ids']))
            ->when($search !== '', fn ($q) => $q->whereHas('description', fn ($d) => $d->where('name', 'like', '%' . addcslashes($search, '%_\\') . '%')))
            ->orderBy('position')->orderBy('feature_id');

        $total = (clone $query)->count();
        $items = $query->forPage($page, $perPage)->get()->map(fn (FoodFeature $feature) => [
            'feature_id' => $feature->feature_id,
            'name' => $feature->description?->name ?? 'Feature #' . $feature->feature_id,
            'type_label' => self::FEATURE_TYPES[$feature->feature_type]['label'] ?? $feature->feature_type,
        ])->values();

        return response()->json([
            'success' => true,
            'items' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'has_more' => $page * $perPage < $total,
        ]);
    }

    /* ================= PRIVATE ================= */

    /** Delete the item's saved images listed in remove_images[] (only this item's images). */
    private function removeItemImages(Request $request, int $itemId): void
    {
        $ids = $request->validate([
            'remove_images' => ['nullable', 'array', 'max:' . FoodImageUploadPolicy::MAX_IMAGES],
            'remove_images.*' => ['integer'],
        ])['remove_images'] ?? [];
        if (!$ids) {
            return;
        }

        $files = app(\Modules\Filemanager\Services\FileService::class);
        FileLink::where('object_type', 'food_image')->where('object_id', $itemId)->whereKey($ids)
            ->pluck('id')->each(fn ($linkId) => $files->deleteFileWithId($linkId));
    }

    /**
     * Save the item's feature values submitted with the item form. Only the features shown
     * on the form (this restaurant's active ones) are replaced; hidden ones keep their values.
     */
    private function saveItemFeatures(Request $request, Restaurant $restaurant, int $itemId): void
    {
        if (!$request->boolean('features_submitted')) {
            return;
        }
        $shownIds = $this->features->itemFeatureSections($restaurant->id)
            ->flatMap(fn ($section) => $section->children->pluck('feature_id'))
            ->map(fn ($featureId) => (int) $featureId)->all();

        $this->items->syncFeatures($itemId, (array) $request->input('features', []), $shownIds);
    }

    /** Feature changes alter item feature values, so clear cached items. */
    private function forgetRestaurantItems(Restaurant $restaurant): void
    {
        FoodItem::where('restaurant_id', $restaurant->id)->pluck('id')
            ->each(fn ($itemId) => CacheService::forgetFoodItem((int) $itemId));
    }

    /**
     * Resolve the restaurant being managed and set up the menu context (links + layout)
     * for redirects and views. Every action calls this first.
     */
    protected function restaurant(Request $request): Restaurant
    {
        $restaurant = $this->resolveRestaurant($request);
        $this->menu = $this->makeContext($restaurant);
        view()->share('menu', $this->menu);

        return $restaurant;
    }

    /** Onboarding: the logged-in owner's restaurant. */
    protected function resolveRestaurant(Request $request): Restaurant
    {
        $admin = $request->user('admin');
        abort_unless($admin && $admin->status && $admin->email_verified_at, 403);

        $restaurant = app(RestaurantOnboardingAccessService::class)->forAdmin($admin)
            ?? Restaurant::where('admin_id', $admin->id)->first();
        abort_unless($restaurant, 404);

        return $restaurant;
    }

    protected function makeContext(Restaurant $restaurant): MenuContext
    {
        return MenuContext::onboarding($restaurant);
    }

    private function ownedItem(Restaurant $restaurant, int $id): FoodItem
    {
        return FoodItem::where('restaurant_id', $restaurant->id)->findOrFail($id);
    }

    private function ownedCategory(Restaurant $restaurant, int $id): FoodCategory
    {
        return FoodCategory::where('restaurant_id', $restaurant->id)->findOrFail($id);
    }

    private function itemForm(Restaurant $restaurant, ?FoodItem $item)
    {
        $item?->load(['categories', 'featureValues', 'servingOptions']);

        return response()->view('restaurant::onboarding.menu.item-form', [
            'restaurant' => $restaurant,
            'item' => $item,
            // Active categories, plus the item's current one even if it was deactivated.
            'categories' => FoodCategory::where('restaurant_id', $restaurant->id)
                ->where(fn ($query) => $query->where('status', true)->orWhereIn('id', $item?->categories->pluck('id') ?? []))
                ->orderBy('name')->get(),
            'labelEligibility' => app(LabelCertificateEligibility::class)->forRestaurant($restaurant->id),
            'featureGroups' => $this->features->itemFeatureSections($restaurant->id),
        ])->header('Cache-Control', 'no-store, private');
    }

    private function categoryForm(Restaurant $restaurant, ?FoodCategory $category)
    {
        return response()->view('restaurant::onboarding.menu.category-form', [
            'restaurant' => $restaurant,
            'category' => $category,
            'categoryImage' => $category ? fn_get_image('category_image', $category->id) : null,
        ])->header('Cache-Control', 'no-store, private');
    }

    /** The restaurant is always taken from the session, never from the request. */
    private function itemData(SaveFoodItemRequest $request, Restaurant $restaurant): array
    {
        $request->validate([
            'features' => ['nullable', 'array'],
            'feature_ids' => ['nullable', 'array', 'list'],
            'feature_ids.*' => ['required', 'integer', 'min:1', 'distinct'],
        ]);
        if ($request->boolean('features_submitted')) {
            // Include selected features whose unchecked controls were omitted by the browser.
            $mapping = array_replace(
                array_fill_keys($request->input('feature_ids', []) ?? [], null),
                $request->input('features', []) ?? [],
            );
            app(\Modules\Restaurant\Services\RestaurantFoodItemFeatureService::class)
                ->normalize((int) $restaurant->id, $mapping);
        }
        $data = $request->validated();
        $data['restaurant_id'] = $restaurant->id;

        if (!FoodCategory::where('restaurant_id', $restaurant->id)->whereKey($data['category_id'])->exists()) {
            throw ValidationException::withMessages(['category_id' => 'Choose a category belonging to your restaurant.']);
        }

        return $data;
    }

    private function validateCategory(Request $request, Restaurant $restaurant, ?FoodCategory $category = null): array
    {
        $data = Validator::make($request->all(), [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('food_categories', 'name')
                    ->where(fn ($query) => $query->where('restaurant_id', $restaurant->id))
                    ->ignore($category?->id),
            ],
            'description' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
            'category_image' => 'nullable|file|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'category_image.file' => 'The category image must be a valid file.',
            'category_image.image' => 'The uploaded file must be an image.',
            'category_image.mimes' => 'The category image must be a JPG, JPEG, or PNG file.',
            'category_image.max' => 'The category image may not be larger than 2 MB.',
        ])->validate();

        unset($data['category_image']);
        $data['restaurant_id'] = $restaurant->id;
        $data['status'] = isset($data['status']) ? (bool) $data['status'] : true;
        // Same defaults as the admin category form: keep hierarchy/food type, new ones apply to all.
        $data['parent_id'] = $category?->parent_id;
        $data['food_type'] = $category?->food_type ?? FoodCategory::TYPE_ALL;

        return $data;
    }

    private function afterCategorySave(Request $request, string $message)
    {
        // Allow "add category" from the item form to return there.
        if ($request->input('return_to') === 'item_create') {
            return redirect($this->menu->route('items.create'))->with('success', $message);
        }

        return redirect($this->menu->route('index', ['tab' => 'categories']))->with('success', $message);
    }

    /** All of this restaurant's features (groups are not used here). */
    private function featureList(Restaurant $restaurant)
    {
        return FoodFeature::forRestaurant($restaurant->id)
            ->where('feature_type', '!=', 'G')
            ->with(['description', 'variants' => fn ($query) => $query->orderBy('position'), 'variants.description'])
            ->withCount(['values as items_count' => fn ($query) => $query->select(\Illuminate\Support\Facades\DB::raw('count(distinct food_item_id)'))])
            ->orderBy('position')->orderBy('feature_id')
            ->get();
    }

    private function ownedFeature(Restaurant $restaurant, int $id): FoodFeature
    {
        return FoodFeature::forRestaurant($restaurant->id)
            ->where('feature_type', '!=', 'G')
            ->with(['description', 'variants.description'])
            ->findOrFail($id);
    }

    /** Name must be unique among this restaurant's features. */
    private function featureNameRule(Restaurant $restaurant, ?int $ignoreId = null)
    {
        return Rule::unique('food_feature_descriptions', 'name')
            ->where('lang_code', 'en')
            ->whereIn('feature_id', FoodFeature::forRestaurant($restaurant->id)->pluck('feature_id'))
            ->ignore($ignoreId, 'feature_id');
    }

    private function featureForm(Restaurant $restaurant, ?FoodFeature $feature)
    {
        return response()->view('restaurant::onboarding.menu.feature-form', [
            'restaurant' => $restaurant,
            'feature' => $feature,
            'featureTypes' => self::FEATURE_TYPES,
        ])->header('Cache-Control', 'no-store, private');
    }

    private function validateFeature(Request $request, Restaurant $restaurant, ?FoodFeature $feature = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', $this->featureNameRule($restaurant, $feature?->feature_id)],
            'feature_type' => ['required', Rule::in(array_keys(self::FEATURE_TYPES))],
            'status' => ['required', Rule::in(['A', 'H'])],
            'variants' => ['nullable', 'array', 'max:50'],
            'variants.*.variant_id' => ['nullable', 'integer'],
            'variants.*.description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.unique' => 'You already have a feature with this name.',
        ]);

        $type = self::FEATURE_TYPES[$data['feature_type']];
        $variants = collect($data['variants'] ?? [])
            ->filter(fn ($variant) => trim((string) ($variant['description'] ?? '')) !== '')
            ->values()
            ->map(fn ($variant, $index) => [
                // Only keep IDs of this feature's own options.
                'variant_id' => $feature && !empty($variant['variant_id']) && $feature->variants->contains('variant_id', (int) $variant['variant_id'])
                    ? (int) $variant['variant_id'] : null,
                'description' => trim((string) $variant['description']),
                'position' => $index + 1,
                'status' => 'A',
            ])->all();

        if ($type['options'] && count($variants) < 2) {
            throw ValidationException::withMessages(['variants' => 'Add at least two options for a choice feature.']);
        }

        return [
            'restaurant_id' => $restaurant->id,
            // Standalone feature; an older feature keeps its existing group.
            'parent_id' => (int) ($feature?->parent_id ?? 0),
            'name' => $data['name'],
            'status' => $data['status'],
            'feature_type' => $data['feature_type'],
            'purpose' => $type['purpose'],
            'feature_style' => $type['feature_style'],
            'filter_style' => $type['filter_style'],
            'variants' => $type['options'] ? $variants : [],
            // Keep existing category links (none = all categories).
            'category_ids' => $feature?->categories->pluck('id')->all() ?? [],
        ];
    }
}
