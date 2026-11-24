<?php

namespace Modules\Checkout\Http\Controllers\Api;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Services\PaymentMethodService;
use Modules\Checkout\Services\OrderPlacementService;
use Modules\Checkout\Services\CheckoutCacheService;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\Payments\Models\PaymentConfiguration;
use Modules\Restaurant\Services\RestaurantService;

class CheckoutController extends Controller
{
    public function __construct(
        protected RestaurantService $restaurantService,
        protected PaymentMethodService $paymentMethodService,
        protected OrderPlacementService $orderPlacementService,
        protected CheckoutCacheService $cacheService
    ) {
        // $this->middleware('auth:sanctum,customer');
    }

    // POST /api/checkout/save-delivery-details
    public function saveDeliveryDetails(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'preferred_delivery_date' => 'required|date',
                'preferred_delivery_time' => 'required|date_format:h:i A',
                'number_of_guests'        => 'required|integer|min:1',
                'phone'                   => 'nullable|string|max:20',
                'restaurant_id'           => 'bail|required|integer|min:1|exists:restaurants,id',
                'saved_address_id'        => 'nullable|integer|exists:addresses,id',
                'is_default'              => 'nullable|boolean', 
                'address_line_1'          => 'required_without:saved_address_id|string|max:255',
                'address_line_2'          => 'nullable|string|max:255',
                'city_id'                 => 'nullable|integer|exists:cities,id',
                'state_id'                => 'nullable|integer|exists:country_states,id',
                'country_id'              => 'nullable|integer|exists:countries,id',
                'zip_code'                => 'required_without:saved_address_id|string|max:20',
                'latitude'                => 'nullable|numeric|between:-90,90',
                'longitude'               => 'nullable|numeric|between:-180,180',
                'city_name'               => 'nullable|string|max:50',
                'state_name'               => 'nullable|string|max:100',
                'floor'                   => 'nullable|string|max:50',
            ]);

            $customer         = Auth::guard('customer')->user();
            $deliveryDateTime = $validated['preferred_delivery_date'] . ' ' . $validated['preferred_delivery_time'];


            if (!$customer) {
                return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
            }

            $deliveryCarts = DB::transaction(function () use ($customer, $validated, $deliveryDateTime) {
                \Modules\Acl\Models\Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
                $customer->update([
                    'guest_method'             => 'Delivery',
                    'preferred_delivery_date'  => $deliveryDateTime,
                    'number_of_guests'         => $validated['number_of_guests'],
                    'phone'                    => $validated['phone'] ?? $customer->phone,
                ]);

                // Check if using saved address
                if (isset($validated['saved_address_id'])) {

                    $savedAddress = $customer->addresses()->findOrFail($validated['saved_address_id']);

                    if (!$savedAddress) {
                        throw new Exception('Saved address not found or does not belong to you');
                    }
                    // If is_default is true, unset other defaults and set this as default
                    if ($validated['is_default'] ?? false) {
                        $customer->addresses()->update(['is_default' => false]);
                        $savedAddress->update(['is_default' => true]);
                    }
                } else {
                    $isDefault = ($validated['is_default'] ?? false) || $customer->addresses()->count() === 0;

                    if ($isDefault) {
                        $customer->addresses()->update(['is_default' => false]);
                    }

                    $result = app(\Modules\Acl\Services\CustomerAddressService::class)->save($customer, [
                        'address_type'     => 'delivery',
                        'address_line_1' => $validated['address_line_1'],
                        'address_line_2' => $validated['address_line_2'] ?? null,
                        'city_id'        => $validated['city_id'] ?? null,
                        'state_id'       => $validated['state_id'] ?? null,
                        'country_id'     => $validated['country_id'] ?? null,
                        'zip_code'       => $validated['zip_code'],
                        'latitude'       => $validated['latitude'] ?? null,
                        'longitude'      => $validated['longitude'] ?? null,
                        'is_default'     => $isDefault,
                        'city_name'      => $validated['city_name'] ?? null,
                    ]);
                    $savedAddress = $result['address'];
                }
                return app(\Modules\Cart\Services\CartDeliveryService::class)->refreshCustomer($customer, $savedAddress->id);
            });

            // Invalidate caches
            $this->cacheService->forgetCustomerAddresses($customer->id);
            if (isset($validated['restaurant_id'])) {
                $this->cacheService->forgetReviewOrder($customer->id, $validated['restaurant_id']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Delivery details saved',
                'data'    => [
                    'delivery_method'          => 'Delivery',
                    'preferred_delivery_date'  => $deliveryDateTime,
                    'number_of_guests'         => $validated['number_of_guests'],
                    'address_saved'            => true,
                    'delivery_carts'           => $deliveryCarts,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Saved address not found.'], 404);
        } catch (Exception $e) {
            Log::error('Unable to save delivery details.', ['exception_class' => get_class($e)]);
            return response()->json(['success' => false, 'message' => 'Unable to save delivery details. Please try again.'], 500);
        }
    }

    // POST /api/checkout/save-pickup-details
    public function savePickupDetails(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'preferred_delivery_date' => 'required|date',
                'preferred_delivery_time' => 'required|date_format:h:i A',
                'number_of_guests'        => 'required|integer|min:1',
                'phone'                   => 'nullable|string|max:20',
                'restaurant_id'           => 'bail|required|integer|min:1|exists:restaurants,id',
            ]);

            $customer         = Auth::guard('customer')->user();
            $deliveryDateTime = $validated['preferred_delivery_date'] . ' ' . $validated['preferred_delivery_time'];

            // Verify customer is authenticated via token
            if (!$customer) {
                return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
            }

            // Save pickup details (works for both guest and authenticated users)
            $customer->update([
                'guest_method'            => 'Pickup',
                'preferred_delivery_date' => $deliveryDateTime,
                'number_of_guests'        => $validated['number_of_guests'],
                'phone'                   => $validated['phone'] ?? $customer->phone,
            ]);
            $deliveryCarts = app(\Modules\Cart\Services\CartDeliveryService::class)->refreshCustomer($customer);

            // Invalidate caches
            $this->cacheService->forgetCustomerAddresses($customer->id);
            if (isset($validated['restaurant_id'])) {
                $this->cacheService->forgetReviewOrder($customer->id, $validated['restaurant_id']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pickup details saved',
                'data'    => [
                    'delivery_method'         => 'Pickup',
                    'delivery_carts'          => $deliveryCarts,
                    'preferred_delivery_date' => $deliveryDateTime,
                    'number_of_guests'        => $validated['number_of_guests'],
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // GET /api/checkout/restaurant-address/{restaurantId}
    public function getRestaurantAddress(int $restaurantId): JsonResponse
    {
        try {
            $restaurant = \Modules\Restaurant\Models\Restaurant::with([
                'addresses' => fn($q) =>
                $q->where('address_type', 'primary')
            ])->findOrFail($restaurantId);

            $address = $restaurant->addresses->first();

            if (!$address) {
                return response()->json(['success' => false, 'message' => 'Restaurant address not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'restaurant' => [
                        'id'    => $restaurant->id,
                        'name'  => $restaurant->name,
                        'phone' => $restaurant->phone,
                    ],
                    'address' => [
                        'address_line_1' => $address->address_line_1,
                        'address_line_2' => $address->address_line_2,
                        'city'           => $address->city?->name,
                        'state'          => $address->state?->name,
                        'country'        => $address->country?->name,
                        'zip_code'       => $address->zip_code,
                        'full_address'   => $address->full_address,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    // GET /api/checkout/food-categories?restaurant_id=1

    public function getFoodCategories(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'restaurant_id' => 'required|integer|exists:restaurants,id',
            ]);

            $restaurantId = $request->restaurant_id;
            $restaurant =  $this->restaurantService->findRestaurantById($restaurantId, ['addresses']);

            // Get restaurant gallery images
            $fileService = app(\Modules\Filemanager\Services\FileService::class);
            $restaurantGalleryRaw = $fileService->getFiles('restaurant_gallery', $restaurantId);

            // Format restaurant gallery - only image URLs
            $restaurantGallery = collect($restaurantGalleryRaw)->map(function ($image) {
                return [
                    'id' => $image['id'],
                    'url' => $image['url'],
                    'thumbnail_url' => $image['url'],
                    'position' => $image['position'] ?? 0,
                ];
            })->values()->all();

            $categories = FoodCategory::active()
                ->whereNull('parent_id')
                ->where('restaurant_id', $restaurantId)
                ->orderBy('name')
                ->get()
                ->map(fn($category) => [
                    'id'    => $category->id,
                    'name'  => $category->name,
                    'image' => fn_get_image('category_image', $category->id)['url'] ?? null,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Food categories Found successfully',
                'data'    => [
                    'restaurant' => [
                        'name' => $restaurant->name,
                        'phone' => $restaurant->phone,
                        "address_line_1" => $restaurant->address_line1 ?? null,
                        "address_line_2" => $restaurant->address_line2 ?? null,
                        "city" => $restaurant->city ?? null,
                        "state" => $restaurant->state ?? null,
                        "country" => $restaurant->country ?? null,
                        "zip_code" => $restaurant->postal_code ?? null,
                    ],
                    'restaurant_gallery' => $restaurantGallery,
                    'categories'  => $categories,
                    'total_count' => $categories->count(),
                ],

            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getFoodItems(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'restaurant_id' => 'required|integer|exists:restaurants,id',
                'category_id'   => 'nullable|integer|exists:food_categories,id',
                'variant_id'    => 'nullable|integer|exists:food_feature_variants,variant_id',
                'per_page'      => 'nullable|integer|min:1|max:100',
                'page'          => 'nullable|integer|min:1',
            ]);

            $restaurantId = $request->integer('restaurant_id');
            $variantId    = $request->integer('variant_id') ?: null;
            $perPage      = (int) $request->query('per_page', 20);
            $page         = (int) $request->query('page', 1);

            // Fetch all categories for restaurant that have items
            $allCategories = FoodCategory::active()
                ->whereNull('parent_id')
                ->where('restaurant_id', $restaurantId)
                ->whereHas(
                    'foodItems',
                    fn($q) =>
                    $q->where('is_available', true)
                        ->where('is_active', true)
                        ->where('in_stock', true)
                )
                ->orderBy('name')
                ->get()
                ->map(fn($c) => [
                    'id'    => $c->id,
                    'name'  => $c->name,
                    'image' => fn_get_image('category_image', $c->id)['url'] ?? null,
                ]);

            // Active category — use passed category_id or default to first
            $firstCategory = $allCategories->first();
            $categoryId    = $request->integer('category_id') ?: ($firstCategory ? $firstCategory['id'] : null);

            // Collect category IDs to query items (parent + its sub-categories)
            // scoped to this restaurant to prevent cross-restaurant data leak
            $categoryIds = $categoryId
                ? FoodCategory::where('restaurant_id', $restaurantId)
                ->where(fn($q) => $q->where('id', $categoryId)->orWhere('parent_id', $categoryId))
                ->pluck('id')
                ->toArray()
                : [];

            // Get all available variants for this category (that have items)
            $availableVariantsForCategory = [];
            if (!empty($categoryIds)) {
                $availableVariantsForCategory = \Modules\FoodManagements\Models\FoodItem::query()
                    ->where('is_available', true)
                    ->where('is_active', true)
                    ->where('in_stock', true)
                    ->whereHas('categories', fn($q) => $q->whereIn('food_categories.id', $categoryIds))
                    ->whereHas('featureValues')
                    ->with('featureValues')
                    ->get()
                    ->pluck('featureValues')
                    ->flatten()
                    ->pluck('variant_id')
                    ->unique()
                    ->toArray();
            }

            // Filter tabs - only show variants that have items in this category
            $filterTabs = \Modules\FoodManagements\Models\FoodFeature::with([
                'descriptions',
                'variants'              => fn($q) => $q->where('status', 'A')->orderBy('position'),
                'variants.descriptions' => fn($q) => $q->where('lang_code', 'en'),
            ])
                ->where('status', 'A')
                ->where('feature_type', '!=', 'G')
                // Features are restaurant-owned: only those of the categories' restaurants.
                ->whereIn('restaurant_id', \Modules\FoodManagements\Models\FoodCategory::whereIn('id', $categoryIds)->pluck('restaurant_id')->filter())
                ->where('display_on_catalog', 'Y')
                ->where(function ($q) use ($categoryIds) {
                    $q->whereDoesntHave('categories')
                        ->orWhereHas(
                            'categories',
                            fn($cq) =>
                            $cq->whereIn('food_categories.id', $categoryIds)
                        );
                })
                ->orderBy('position')
                ->get()
                ->map(fn($f) => [
                    'feature_id' => $f->feature_id,
                    'name'       => $f->descriptions->where('lang_code', 'en')->first()?->name ?? '',
                    'variants'   => $f->variants
                        ->where('status', 'A')
                        ->filter(fn($v) => in_array($v->variant_id, $availableVariantsForCategory))
                        ->map(fn($v) => [
                            'variant_id' => $v->variant_id,
                            'name'       => $v->descriptions->first()?->description ?? '',
                        ])
                        ->values(),
                ])
                ->filter(fn($f) => !empty($f['variants']))
                ->values();

            // Default to first variant if not provided
            $variantId = $request->integer('variant_id') ?: null;

            // If variant_id is not provided and we have filter tabs, use the first variant
            if ($variantId === null && $filterTabs->isNotEmpty()) {
                $firstTab = $filterTabs->first();
                if (isset($firstTab['variants']) && count($firstTab['variants']) > 0) {
                    $variantId = $firstTab['variants'][0]['variant_id'];
                }
            }

            // Collect category IDs to query items (parent + its sub-categories)
            $categoryIds = $categoryId
                ? FoodCategory::where('restaurant_id', $restaurantId)
                ->where(fn($q) => $q->where('id', $categoryId)->orWhere('parent_id', $categoryId))
                ->pluck('id')
                ->toArray()
                : [];

            // Fetch items for the active category
            $items      = collect();
            $pagination = null;

            if (!empty($categoryIds)) {
                $itemQuery = \Modules\FoodManagements\Models\FoodItem::query()
                    ->where('is_available', true)
                    ->where('is_active', true)
                    ->where('in_stock', true)
                    ->whereHas('categories', fn($q) => $q->whereIn('food_categories.id', $categoryIds));

                // Apply variant filter 
                if ($variantId) {
                    $itemQuery->whereHas(
                        'featureValues',
                        fn($q) => $q->where('variant_id', $variantId)
                    );
                }

                $paginator = $itemQuery->with(['optionGroups.options', 'options'])
                    ->orderBy('name')
                    ->paginate($perPage, ['*'], 'page', $page);

                $items = collect($paginator->items())->map(fn($item) => [
                    'id'            => $item->id,
                    'name'          => $item->name,
                    'description'   => $item->description,
                    'currency'      => fn_format_default_currency(),
                    'sale_price'    => $item->sale_price,
                    'quantity_type' => $item->quantity_type,
                    'quantity'      => $item->quantity,
                    'food_type'     => $item->food_type,
                    'image'         => fn_get_image('food_image', $item->id)['url'] ?? null,
                    'image_gallery' => collect(fn_get_images('food_image', $item->id) ?? [])
                        ->map(fn($image) => $image['url'] ?? null)
                        ->filter()
                        ->values()
                        ->all(),
                    'groups'        => $item->optionGroups->map(fn($group) => [
                        'id'      => $group->id,
                        'name'    => $group->name,
                        'options' => $group->options->map(fn($option) => [
                            'id'    => $option->id,
                            'name'  => $option->name,
                            'price' => $item->options->firstWhere('id', $option->id)?->pivot->price ?? null,
                        ])->values(),
                    ])->values(),
                ]);

                $pagination = [
                    'total'          => $paginator->total(),
                    'per_page'       => $paginator->perPage(),
                    'current_page'   => $paginator->currentPage(),
                    'total_pages'    => $paginator->lastPage(),
                    'has_more_pages' => $paginator->hasMorePages(),
                ];
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'categories'     => $allCategories->values(),
                    'filter_tabs'    => $filterTabs,
                    'items'          => $items,
                    'pagination'     => $pagination,
                    'active_filters' => [
                        'category_id' => $categoryId ? (int) $categoryId : null,
                        'variant_id'  => $variantId,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }



    // GET /api/checkout/payment-methods
    public function getPaymentMethods(): JsonResponse
    {
        try {
            $paymentMethods = PaymentConfiguration::with('method')
                ->where('is_active', true)
                ->get()
                ->map(function ($config) {
                    return [
                        // 'id' => $config->id,
                        'id' => $config->id,
                        'payment_method_id' => $config->payment_method_id,
                        'name' => $config->merchant_name,
                        'is_active' => (bool) $config->is_active,
                    ];
                });

            return response()->json([
                'success' => true,
                'data'    => ['payment_methods' => $paymentMethods],
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // POST /api/checkout/set-payment-method
    public function setPaymentMethod(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['payment_method' => 'required|string']);

            return response()->json([
                'success' => true,
                'message' => 'Payment method set',
                'data'    => ['payment_method' => $validated['payment_method']],
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    // GET /api/checkout/review-order?restaurant_id=1
    public function reviewOrder(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        try {
            $validated = $request->validate([
                'restaurant_id' => 'bail|required|integer|min:1|exists:restaurants,id',
                'payment_method' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('payment_configurations', 'id')->where('is_active', true)],
            ]);
            $restaurantId = (int) $validated['restaurant_id'];

            if (!in_array($customer->guest_method, ['Delivery', 'Pickup'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please save delivery or pickup details first',
                ], 400);
            }

            $cart = DB::transaction(function () use ($customer, $restaurantId) {
                \Modules\Acl\Models\Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
                // Use the same session-first, latest-cart fallback as order placement.
                $cart = $customer->getCart($restaurantId)
                    ?? $customer->ownedCarts()->forRestaurant($restaurantId)->latest()->first();
                if ($cart && $cart->items()->exists()) {
                    $cart->updateTotals();
                    // updateTotals reloads items; load presentation relations afterwards.
                    $cart->load(['items.item.categories', 'items.servingOption']);
                }

                return $cart;
            });

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Cart is empty'], 400);
            }

            $restaurant = \Modules\Restaurant\Models\Restaurant::with([
                'addresses.city', 'addresses.state', 'addresses.country',
            ])->findOrFail($restaurantId);
            $deliveryAddress = $customer->guest_method === 'Delivery' && $cart->delivery_address_id
                ? $customer->addresses()->with(['city', 'state', 'country'])->find($cart->delivery_address_id)
                : null;
            $pickupAddress = $restaurant->addresses->firstWhere('address_type', 'primary');
            $payment = !empty($validated['payment_method'])
                ? PaymentConfiguration::with('method')->find($validated['payment_method']) : null;
            $image = fn_get_image('restaurant', $restaurantId);
            $gallery = app(\Modules\Filemanager\Services\FileService::class)->getFiles('restaurant_gallery', $restaurantId);

            $data = app(\Modules\Checkout\Services\ReviewOrderSummary::class)->build(
                $cart, $customer, $request, fn_format_default_currency(),
                (float) (fn_get_setting('general.default_service_charge') ?? 0)
            );

            return response()->json([
                'success' => true,
                'message' => 'Order review retrieved successfully',
                'data' => array_merge($data, [
                    'restaurant' => [
                        'id' => $restaurant->id,
                        'name' => $restaurant->name,
                        'phone' => $restaurant->phone,
                        'image' => $image['url'] ?? null,
                    ],
                    'delivery_address' => $this->reviewAddress($deliveryAddress),
                    'pickup_address' => $customer->guest_method === 'Pickup'
                        ? ($this->reviewAddress($pickupAddress) ?? [
                            'address_line_1' => $restaurant->address_line1,
                            'address_line_2' => $restaurant->address_line2,
                            'city' => $restaurant->city,
                            'state' => $restaurant->state,
                            'country' => $restaurant->country,
                            'zip_code' => $restaurant->postal_code,
                            'full_address' => implode(', ', array_filter([
                                $restaurant->address_line1, $restaurant->address_line2, $restaurant->city,
                                $restaurant->state, $restaurant->country, $restaurant->postal_code,
                            ])),
                        ]) : null,
                    'payment_method' => $payment ? [
                        'id' => $payment->id,
                        'payment_method_id' => $payment->payment_method_id,
                        'name' => $payment->merchant_name,
                        'code' => $payment->method?->code,
                    ] : null,
                    'restaurant_gallery' => collect($gallery)->map(fn ($image) => [
                        'id' => $image['id'],
                        'url' => $image['url'] ?? null,
                        'thumbnail_url' => $image['thumbnail_url'] ?? $image['url'] ?? null,
                        'position' => $image['position'] ?? 0,
                    ])->values()->all(),
                ]),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Checkout details no longer available. Please refresh your cart.'], 404);
        } catch (\Throwable $e) {
            Log::error('Unable to review order.', ['exception_class' => get_class($e)]);

            return response()->json(['success' => false, 'message' => 'Unable to review order. Please try again.'], 500);
        }
    }

    private function reviewAddress(?\Modules\Countries\Models\Address $address): ?array
    {
        if (!$address) {
            return null;
        }

        $city = $address->city?->name ?? $address->city_name;

        return [
            'id' => $address->id,
            'address_line_1' => $address->address_line_1,
            'address_line_2' => $address->address_line_2,
            'city' => $city,
            'state' => $address->state?->name,
            'country' => $address->country?->name,
            'zip_code' => $address->zip_code,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
            'full_address' => implode(', ', array_filter([
                $address->address_line_1, $address->address_line_2, $city,
                $address->state?->name, $address->country?->name, $address->zip_code,
            ])),
        ];
    }


    // POST /api/checkout/place-order
    public function placeOrder(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'restaurant_id'  => 'required|integer|exists:restaurants,id',
                // 'payment_method' => 'required|string',
                'payment_method' => 'required|integer|exists:payment_configurations,id',
            ]);

        log::info('Placing order for restaurant_id: ' . $validated['restaurant_id'] . ' with payment_method: ' . $validated['payment_method']);
            $customer  = Auth::guard('customer')->user();

            // Verify customer is authenticated via token
            if (!$customer) {
                return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
            }

            $deliveryMethod = $customer->guest_method;

            if (!$deliveryMethod) {
                return response()->json(['success' => false, 'message' => 'Save delivery or pickup details first'], 400);
            }

            // Place order (works for both authenticated and guest)
            $order = $this->orderPlacementService->placeOrder($customer, [
                'shipping_method' => $deliveryMethod === 'Delivery' ? 'delivery' : 'takeaway',
                'payment_method'  => $validated['payment_method'],
                'restaurant_id'   => $validated['restaurant_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data'    => ['order' => $order],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Unable to place order.', [
                'exception_class' => get_class($e),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'sql_state' => $e instanceof \Illuminate\Database\QueryException ? ($e->errorInfo[0] ?? null) : null,
                'database_error_code' => $e instanceof \Illuminate\Database\QueryException ? ($e->errorInfo[1] ?? null) : null,
            ]);
            return response()->json(['success' => false, 'message' => 'Unable to place order. Please try again.'], 500);
        }
    }

    // GET /api/checkout/my-addresses
    public function getMyAddresses(): JsonResponse
    {
        try {
            $customer = Auth::guard('customer')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required to view saved addresses'
                ], 401);
            }

            $addresses = $customer->addresses()
                ->with(['city', 'state', 'country'])
                ->get()
                ->map(fn($a) => [
                    'id'             => $a->id,
                    'address_type'   => $a->address_type,
                    'address_line_1' => $a->address_line_1,
                    'address_line_2' => $a->address_line_2,
                    'city'           => $a->city?->name,
                    'state'          => $a->state?->name,
                    'country'        => $a->country?->name,
                    'zip_code'       => $a->zip_code,
                    'full_address'   => $a->full_address,
                    'is_default'     => $a->is_default,
                ]);

            return response()->json([
                'success' => true,
                'data'    => ['addresses' => $addresses],
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
