<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Restaurant\Models\Restaurant;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodItem;
use Modules\FoodManagements\Models\FoodFeature;
use Modules\FoodManagements\Models\FoodFeatureDescription;
use Modules\FoodManagements\Models\FoodFeatureVariant;
use Modules\FoodManagements\Models\FoodFeatureVariantDescription;
use Modules\FoodManagements\Models\FoodFeatureValue;
use Modules\FoodManagements\Models\FoodOptionGroup;
use Modules\FoodManagements\Models\FoodOption;
use Modules\Acl\Models\Admin;
use Modules\Acl\Models\Role;
use Modules\Filemanager\Services\FileService;
use Illuminate\Http\UploadedFile;

class ComprehensiveFoodSeeder extends Seeder
{
    public const RESTAURANT_SLUG = 'spice-palace-comp';
    public const OWNER_EMAIL = 'admin@spicepalace-comp.com';
    public const OWNER_PASSWORD = 'DemoRestaurant@123';

    protected ?FileService $fileService = null;

    public function run(): void
    {
        $this->command?->info('Seeding one complete demo restaurant...');

        // Demo approvals must not send real onboarding emails or run review observers.
        \Illuminate\Database\Eloquent\Model::withoutEvents(function () {
            \Illuminate\Support\Facades\DB::transaction(function () {
                $reviewer = $this->reviewer();
                $ownerRole = Role::firstOrCreate(['name' => 'Demo Restaurant Owner', 'guard' => 'admin'], [
                    'description' => 'Owner of the comprehensive demo restaurant',
                    'permission_type' => 'custom',
                    'permissions' => ['dashboard.index', 'profile.edit', 'profile.update'],
                ]);
                $owner = Admin::firstOrNew(['email' => self::OWNER_EMAIL]);
                $owner->fill(['name' => 'Spice Palace Demo Owner', 'role_id' => $ownerRole->id,
                    'status' => true, 'email_verified_at' => $owner->email_verified_at ?? now()]);
                if (!$owner->exists) {
                    $owner->password = self::OWNER_PASSWORD;
                }
                $owner->save();

                $location = $this->location();
                $restaurant = $this->createRestaurant($owner, $reviewer, $location);
                $this->createRestaurantDetails($restaurant, $reviewer, $location);
                $this->createKycDocuments($restaurant, $owner, $reviewer);
                $this->createCategoriesAndItems($restaurant, $this->createFeatureGroups(), $this->createOptionGroups());
            });
        });

        // Events are intentionally muted above, so invalidate these caches explicitly.
        $restaurant = Restaurant::where('slug', self::RESTAURANT_SLUG)->firstOrFail();
        $cache = app(\Modules\Restaurant\Services\RestaurantCacheService::class);
        $cache->forgetList();
        $cache->forgetDetail($restaurant->id);
        $this->command?->info('Demo restaurant ready: '.self::OWNER_EMAIL.' (password for a new owner: '.self::OWNER_PASSWORD.'). Existing passwords are preserved.');
    }

    private function reviewer(): Admin
    {
        $reviewer = Admin::whereHas('role', fn ($query) => $query->where('permission_type', 'all'))->orderBy('id')->first();
        if ($reviewer) return $reviewer;

        $role = Role::firstOrCreate(['name' => 'Admin', 'guard' => 'admin'], [
            'description' => 'Administrator role', 'permission_type' => 'all', 'permissions' => [],
        ]);
        return Admin::firstOrCreate(['email' => 'demo.reviewer@example.test'], [
            'name' => 'Demo Reviewer', 'password' => self::OWNER_PASSWORD,
            'role_id' => $role->id, 'status' => true, 'email_verified_at' => now(),
        ]);
    }

    private function location(): array
    {
        $country = \Modules\Countries\Models\Country::firstOrCreate(['code' => 'US'], [
            'name' => 'United States', 'iso3' => 'USA', 'numeric_code' => '840', 'phonecode' => '1',
            'capital' => 'Washington', 'currency' => 'USD', 'region' => 'Americas',
            'subregion' => 'Northern America', 'latitude' => '40.7128', 'longitude' => '-74.0060',
        ]);
        $state = \Modules\Countries\Models\CountryState::firstOrCreate(['country_id' => $country->id, 'code' => 'NY'], [
            'name' => 'New York', 'country_code' => 'US', 'latitude' => '40.7128', 'longitude' => '-74.0060',
        ]);
        $city = \Modules\Countries\Models\City::firstOrCreate(['country_id' => $country->id, 'state_id' => $state->id, 'name' => 'New York'], [
            'latitude' => '40.7128', 'longitude' => '-74.0060',
        ]);
        return compact('country', 'state', 'city');
    }

    private function createRestaurant(Admin $owner, Admin $reviewer, array $location): Restaurant
    {
        $restaurant = Restaurant::updateOrCreate(['slug' => self::RESTAURANT_SLUG], [
            'admin_id' => $owner->id, 'name' => 'Spice Palace Demo', 'phone' => '+12125550123',
            'business_license' => 'DEMO-NY-RESTAURANT-001',
            'description' => 'Demo Indian restaurant with a complete menu, catering, pickup and delivery.',
            'address_line1' => '123 Demo Main Street', 'address_line2' => 'Suite 101',
            'city' => $location['city']->name, 'state' => $location['state']->name,
            'country' => $location['country']->name, 'postal_code' => '10001',
            'latitude' => 40.7128, 'longitude' => -74.0060, 'food_type' => 'all',
            'cuisine_type' => 'Indian, North Indian, South Indian, Indo-Chinese',
            'opening_time' => '09:00:00', 'closing_time' => '22:00:00',
            'is_takeaway' => true, 'is_delivery' => true, 'is_accepting_orders' => true,
            'order_time_limit' => 30, 'allow_order_cancellation' => true,
            'cancellation_time_limit' => 15, 'cancellable_statuses' => ['W', 'O', 'P'],
            'status' => 'active', 'onboarding_status' => 'approved', 'approval_status' => 'approved',
            'rejection_reason' => null, 'approved_at' => now(), 'approved_by' => $reviewer->id,
            'onboarding_step' => 5, 'submitted_at' => now(), 'tax_type' => 'business',
        ]);
        $this->attachImageToModel($restaurant, 'restaurant', 'restaurants', self::RESTAURANT_SLUG);
        foreach (['spice-palace-comp', 'taj-flavors-comp', 'curry-house-comp'] as $position => $image) {
            $this->attachImageToModel($restaurant, 'restaurant_gallery', 'restaurants', $image, 'A', $position);
        }
        return $restaurant;
    }

    private function createRestaurantDetails(Restaurant $restaurant, Admin $reviewer, array $location): void
    {
        $ids = ['country_id' => $location['country']->id, 'state_id' => $location['state']->id, 'city_id' => $location['city']->id];
        $hours = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $hours[$day] = [
                ['open' => true, 'from' => '09:00', 'to' => '14:30'],
                ['open' => true, 'from' => '17:00', 'to' => in_array($day, ['friday', 'saturday'], true) ? '23:00' : '22:00'],
            ];
        }
        $restaurant->hours()->updateOrCreate([], ['hours' => $hours, 'is_open_whole_day' => false]);
        $restaurant->holidays()->updateOrCreate(['date' => '2030-01-01'], ['reason' => 'Demo annual maintenance closure', 'is_closed' => true]);
        $restaurant->holidays()->updateOrCreate(['date' => '2030-12-25'], ['reason' => 'Demo holiday service available', 'is_closed' => false]);
        foreach (['primary', 'billing'] as $type) {
            $restaurant->addresses()->updateOrCreate(['address_type' => $type], $ids + [
                'address_line_1' => '123 Demo Main Street', 'address_line_2' => 'Suite 101',
                'city_name' => $location['city']->name, 'zip_code' => '10001',
                'latitude' => 40.7128, 'longitude' => -74.0060, 'is_default' => $type === 'primary',
            ]);
        }
        $banking = $ids + [
            'account_holder_name' => 'Spice Palace Demo LLC', 'routing_number' => '000000000',
            'account_number' => '000012345678', 'business_legal_name' => 'Spice Palace Demo LLC',
            'doing_business_as' => 'Spice Palace Demo', 'address' => '123 Demo Main Street, Suite 101', 'postal_code' => '10001',
        ];
        foreach (['city' => 'city', 'state' => 'state'] as $column => $key) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('restaurant_banking_details', $column)) {
                $banking[$column] = $location[$key]->name;
            }
        }
        $record = $restaurant->bankingDetail()->firstOrNew();
        $record->fill($banking);
        foreach (['city', 'state'] as $legacy) {
            if (array_key_exists($legacy, $banking)) $record->setAttribute($legacy, $banking[$legacy]);
        }
        $record->save();
        foreach (['individual', 'business'] as $type) {
            $individual = $type === 'individual';
            $restaurant->taxInformation()->updateOrCreate(['tax_type' => $type], $ids + [
                'full_name' => $individual ? 'Demo Restaurant Owner' : null,
                'ssn' => $individual ? '000000000' : null,
                'company_legal_name' => $individual ? null : 'Spice Palace Demo LLC',
                'doing_business_as' => $individual ? null : 'Spice Palace Demo',
                'ein' => $individual ? null : '000000000',
                'address' => '123 Demo Main Street, Suite 101', 'zip_code' => '10001',
                'same_mailing_address' => $individual,
                'mailing_address' => $individual ? null : '456 Demo Mailing Avenue',
                'mailing_city' => $individual ? null : 'New York',
                'mailing_state' => $individual ? null : 'New York',
                'mailing_zip_code' => $individual ? null : '10002',
                'receive_1099_electronically' => true, 'backup_tax_withholding' => false,
                'tax_certification' => true, 'tax_certified_at' => now(),
            ]);
        }
        foreach (\Modules\Restaurant\Enums\OnboardingStep::ordered() as $step) {
            $restaurant->onboardingSteps()->updateOrCreate(['step_key' => $step->value], [
                'status' => 'approved', 'submitted_at' => now(), 'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(), 'rejection_reason' => null,
            ]);
        }
    }

    private function createKycDocuments(Restaurant $restaurant, Admin $owner, Admin $reviewer): void
    {
        $documents = ['Business Registration' => 'registration_document', 'Food License' => 'permit_document',
            'Tax Certificate' => 'tax_certificate', 'Proof of Address' => 'proof_of_address_document'];
        foreach ($documents as $name => $key) {
            $type = \Modules\KYC\Models\KycType::firstOrCreate(['name' => $name, 'type' => 'kyc'], [
                'description' => 'Demo '.$name.' document.', 'is_active' => true, 'created_by' => $reviewer->id,
            ]);
            $field = $type->fields()->firstOrCreate(['field_key' => $key], [
                'field_name' => $name.' Document', 'field_type' => 'file', 'is_required' => true,
                'validation_rules' => ['allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_size_kb' => 10240], 'order_index' => 1,
            ]);
            $submission = \Modules\KYC\Models\KycSubmission::updateOrCreate(['user_id' => $owner->id, 'kyc_type_id' => $type->id], [
                'status' => 'approved', 'reviewed_by' => $reviewer->id, 'reviewed_at' => now(), 'rejection_reason' => null,
            ]);
            $filePath = 'demo/restaurant-'.$restaurant->id.'/kyc-'.$type->id.'.pdf';
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($filePath)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML('<h1>DEMO DOCUMENT</h1><p>'.e($name).'</p><p>Spice Palace Demo. Sample data only; not a legal document.</p>')->output();
                \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $pdf);
            }
            $submission->files()->updateOrCreate(['field_id' => $field->id], ['file_path' => $filePath, 'file_type' => 'application/pdf', 'uploaded_at' => now()]);
            $submission->data()->updateOrCreate(['field_id' => $field->id], [
                'field_key' => $key, 'field_value' => 'file_uploaded', 'verified' => true,
                'verified_at' => now(), 'notes' => 'Demo document for sample onboarding data.',
            ]);
        }
    }

    private function createOptionGroups(): array
    {
        $definitions = [
            'Sizes' => [true, 1, 1, ['Small' => 0, 'Medium' => 2, 'Large' => 4]],
            'Add-ons' => [false, 0, 3, ['Extra Cheese' => 1.5, 'Extra Sauce' => 0.75, 'Extra Vegetables' => 2]],
            'Spice Level' => [true, 1, 1, ['Mild' => 0, 'Medium Hot' => 0, 'Extra Hot' => 0.5]],
        ];
        $groups = [];
        foreach ($definitions as $label => [$required, $min, $max, $options]) {
            $group = FoodOptionGroup::updateOrCreate(['name' => 'Demo '.$label], ['is_required' => $required, 'min' => $min, 'max' => $max]);
            foreach ($options as $name => $price) {
                FoodOption::updateOrCreate(['food_option_group_id' => $group->id, 'name' => $name], ['is_active' => true]);
            }
            $groups[$label] = $group;
        }
        return $groups;
    }

    private function featureDefinitions(): array
    {
        return [
            'food_type' => ['Food Type', 'S', 'find_products', 'text', 'checkbox', ['Veg', 'Non-Veg']],
            'carb_type' => ['Carb Type', 'S', 'find_products', 'text', 'checkbox', ['Rice', 'Bread']],
            'dessert_type' => ['Dessert Type', 'S', 'find_products', 'text', 'checkbox', ['Grain', 'Sweets']],
            'beverage_type' => ['Beverage Type', 'S', 'find_products', 'text', 'checkbox', ['Regular', 'Diet']],
            'allergens' => ['Allergens', 'M', 'find_products', 'multiple_checkbox', 'checkbox', ['Milk', 'Wheat', 'Nuts']],
            'organic' => ['Organic Ingredients', 'C', 'find_products', 'checkbox', 'checkbox', []],
            'calories' => ['Calories', 'O', 'find_products', 'number', 'slider', []],
            'portion_weight' => ['Portion Weight', 'N', 'group_variation_catalog_item', 'dropdown', 'slider', ['150', '300', '600']],
            'available_on' => ['Available From', 'D', 'find_products', 'text', 'date', []],
            'chef_note' => ['Chef Note', 'T', 'describe_product', 'text', '', []],
            'brand' => ['Kitchen Brand', 'E', 'organize_catalog', 'brand', 'checkbox', ['Spice Palace Demo']],
            'label' => ['Serving Style', 'S', 'group_variation_catalog_item', 'dropdown_labels', 'checkbox', ['Classic', 'Signature']],
        ];
    }

    private function createFeatureGroups(): array
    {
        $base = ['parent_id' => 0, 'display_on_product' => 'Y', 'display_on_catalog' => 'Y', 'display_on_header' => 'N', 'status' => 'A', 'comparison' => 'N'];
        $group = FoodFeature::updateOrCreate(['feature_code' => 'demo_menu_attributes'], $base + [
            'purpose' => 'find_products', 'feature_style' => 'text', 'filter_style' => '', 'feature_type' => 'G', 'position' => 1,
        ]);
        FoodFeatureDescription::updateOrCreate(['feature_id' => $group->feature_id, 'lang_code' => 'en'], ['name' => 'Demo Menu Attributes', 'description' => 'Sample menu features covering supported field types.']);
        $features = [];
        foreach ($this->featureDefinitions() as $key => [$name, $type, $purpose, $style, $filter, $variants]) {
            $data = array_replace($base, ['parent_id' => $group->feature_id, 'purpose' => $purpose, 'feature_style' => $style,
                'filter_style' => $filter, 'feature_type' => $type, 'position' => count($features) + 1]);
            $feature = FoodFeature::updateOrCreate(['feature_code' => 'demo_'.$key], $data);
            FoodFeatureDescription::updateOrCreate(['feature_id' => $feature->feature_id, 'lang_code' => 'en'], ['name' => $name, 'description' => 'Demo '.$name.' attribute.']);
            foreach ($variants as $index => $variant) {
                $record = FoodFeatureVariant::updateOrCreate(['feature_id' => $feature->feature_id, 'position' => $index + 1], ['status' => 'A']);
                FoodFeatureVariantDescription::updateOrCreate(['variant_id' => $record->variant_id, 'lang_code' => 'en'], ['description' => $variant]);
            }
            $features[$key] = $feature;
        }
        return $features;
    }

    private function createCategoriesAndItems(Restaurant $restaurant, array $features, array $optionGroups): void
    {
        $tagIds = [];
        foreach (['Popular', 'Chef Recommended', 'Freshly Prepared'] as $name) {
            $tag = \Modules\FoodManagements\Models\FoodTag::firstOrCreate(['slug' => 'demo-'.\Illuminate\Support\Str::slug($name)], ['name' => $name]);
            $tagIds[] = $tag->id;
        }
        $index = 0;
        foreach ($this->getCategoriesData() as $categoryData) {
            $slug = $categoryData['slug'].'-'.$restaurant->id;
            $category = FoodCategory::updateOrCreate(['slug' => $slug], [
                'restaurant_id' => $restaurant->id, 'name' => $categoryData['name'], 'description' => $categoryData['description'],
                'food_type' => 'all', 'status' => true, 'parent_id' => null,
            ]);
            $this->attachImageToModel($category, 'category_image', 'categories', strtolower($categoryData['name']));
            foreach ($features as $feature) $feature->categories()->syncWithoutDetaching([$category->id]);
            foreach ($categoryData['items'] as $itemData) {
                $itemFeatureData = $itemData['features'];
                $groupNames = $itemData['option_groups'];
                unset($itemData['features'], $itemData['option_groups']);
                $foodType = $this->foodTypeForName($itemData['name']);
                $child = FoodCategory::updateOrCreate(['slug' => $slug.'-'.$foodType], [
                    'restaurant_id' => $restaurant->id, 'parent_id' => $category->id, 'food_type' => $foodType,
                    'name' => ($foodType === 'veg' ? 'Vegetarian ' : 'Non-Vegetarian ').$category->name,
                    'description' => 'Demo '.$foodType.' selections.', 'status' => true,
                ]);
                $itemData = array_replace($itemData, [
                    'restaurant_id' => $restaurant->id, 'slug' => self::RESTAURANT_SLUG.'-'.$itemData['slug'],
                    'food_type' => $foodType, 'long_description' => $itemData['description'].'. Prepared fresh in our demo kitchen. Serving suggestions and ingredients are sample data.',
                    'list_price' => $itemData['sale_price'] + 25,
                    'quantity_type' => ['manual', 'grouping', 'piece'][$index % 3],
                    'quantity' => [50, 10, 6][$index % 3],
                ]);
                $item = FoodItem::updateOrCreate(['slug' => $itemData['slug']], $itemData);
                $item->categories()->syncWithoutDetaching([$category->id, $child->id]);
                $item->tags()->syncWithoutDetaching($tagIds);
                if (in_array($categoryData['name'], ['Appetizers', 'Entrees'], true)) $groupNames[] = 'Spice Level';
                $selectedGroupIds = [];
                foreach (array_unique($groupNames) as $groupName) {
                    $group = $optionGroups[$groupName];
                    $selectedGroupIds[] = $group->id;
                    foreach ($group->options()->orderBy('id')->get() as $position => $option) {
                        $price = $groupName === 'Sizes' ? $position * 2 : ($groupName === 'Add-ons' ? [1.5, 0.75, 2][$position] : ($position === 2 ? 0.5 : 0));
                        $item->options()->syncWithoutDetaching([$option->id => ['price' => $price]]);
                    }
                }
                $item->optionGroups()->syncWithoutDetaching($selectedGroupIds);
                $this->seedFeatureValue($item, $features['food_type'], $foodType === 'veg' ? 'Veg' : 'Non-Veg');
                foreach ($itemFeatureData as $selection) {
                    if ($selection['feature'] !== 'food_type') $this->seedFeatureValue($item, $features[$selection['feature']], $selection['variant']);
                }
                foreach (['Milk', 'Wheat'] as $allergen) $this->seedFeatureValue($item, $features['allergens'], $allergen);
                $this->seedFeatureValue($item, $features['organic'], $index % 2 ? 'N' : 'Y');
                $this->seedFeatureValue($item, $features['calories'], (string) (200 + $index), 200 + $index);
                $this->seedFeatureValue($item, $features['portion_weight'], '300', 300);
                $this->seedFeatureValue($item, $features['available_on'], '2026-01-01');
                $this->seedFeatureValue($item, $features['chef_note'], 'Made to order. Ask about dietary preferences.');
                $this->seedFeatureValue($item, $features['brand'], 'Spice Palace Demo');
                $this->seedFeatureValue($item, $features['label'], $index % 2 ? 'Classic' : 'Signature');
                $this->attachImageToModel($item, 'food_image', 'food-items', \Illuminate\Support\Str::slug($item->name));
                $index++;
            }
            $this->command?->info('Seeded category: '.$category->name);
        }
    }

    private function foodTypeForName(string $name): string
    {
        $meatWords = ['chicken', 'lamb', 'prawn', 'fish', 'calamari', 'seekh kebab', 'shami kebab', 'galauti kebab', 'nihari', 'rogan josh', 'mixed grill', 'egg '];
        foreach ($meatWords as $word) {
            if (str_contains(strtolower($name), $word)) return 'non_veg';
        }
        return 'veg';
    }

    private function seedFeatureValue(FoodItem $item, FoodFeature $feature, string $value, ?float $number = null): void
    {
        $variant = $feature->variants()->whereHas('descriptions', fn ($query) => $query->where('lang_code', 'en')->where('description', $value))->first();
        FoodFeatureValue::updateOrCreate([
            'food_item_id' => $item->id, 'feature_id' => $feature->feature_id,
            'variant_id' => $variant?->variant_id, 'lang_code' => 'en',
        ], ['value' => $value, 'value_int' => $number]);
    }

    private function getCategoriesData()
    {
        return [
            // APPETIZERS CATEGORY
            [
                'name' => 'Appetizers',
                'slug' => 'appetizers-comp',
                'description' => 'Delicious starters and appetizers',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateAppetizers(),
            ],
            // ENTREES CATEGORY
            [
                'name' => 'Entrees',
                'slug' => 'entrees-comp',
                'description' => 'Main course entrees',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateEntrees(),
            ],
            // CARBS CATEGORY
            [
                'name' => 'Carbs',
                'slug' => 'carbs-comp',
                'description' => 'Rice and bread varieties',
                'food_type' => 'veg',
                'linked_features' => ['carb_type'],
                'items' => $this->generateCarbs(),
            ],
            // DESSERTS CATEGORY
            [
                'name' => 'Desserts',
                'slug' => 'desserts-comp',
                'description' => 'Sweet desserts and treats',
                'food_type' => 'veg',
                'linked_features' => ['dessert_type'],
                'items' => $this->generateDesserts(),
            ],
            // BEVERAGES CATEGORY
            [
                'name' => 'Beverages',
                'slug' => 'beverages-comp',
                'description' => 'Drinks and beverages',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateBeverages(),
            ],
        ];
    }

    private function generateAppetizers()
    {
        $appetizers = [
            'Spring Rolls',
            'Samosa',
            'Pakora',
            'Chicken Wings',
            'Mozzarella Sticks',
            'Calamari',
            'Bruschetta',
            'Nachos',
            'Hummus',
            'Garlic Bread',
            'Paneer Tikka',
            'Vegetable Cutlet',
            'Onion Bhaji',
            'Chikhalwali',
            'Seekh Kebab',
            'Aloo Tikki',
            'Chaat Papdi',
            'Dahi Bhalle',
            'Vegetable Pakora',
            'Cheese Pakora',
            'Mushroom Pakora',
            'Corn Pakora',
            'Spinach Pakora',
            'Paneer Fries',
            'Vegetable Spring Roll',
            'Prawn Pakora',
            'Fish Pakora',
            'Chicken Pakora',
            'Lamb Seekh Kebab',
            'Tandoori Mushroom',
            'Tandoori Paneer',
        ];

        $items = [];
        foreach ($appetizers as $index => $name) {
            $items[] = [
                'name' => $name,
                'slug' => strtolower(str_replace(' ', '-', "$name-app-" . ($index + 1))),
                'description' => "Delicious $name appetizer",
                'sale_price' => 150.00 + ($index * 10),
                'cost_price' => 50.00 + ($index * 3),
                'quantity_type' => 'grouping',
                'quantity' => 1,
                'food_type' => $index % 3 == 0 ? 'non_veg' : 'veg',
                'is_available' => true,
                'is_active' => true,
                'in_stock' => true,
                'features' => [
                    ['feature' => 'food_type', 'variant' => $index % 3 == 0 ? 'Non-Veg' : 'Veg'],
                ],
                'option_groups' => ['Sizes'],
            ];
        }
        return $items;
    }

    private function generateEntrees()
    {
        $entrees = [
            'Tandoori Chicken',
            'Paneer Tikka',
            'Chicken Tikka',
            'Seekh Kebab',
            'Shami Kebab',
            'Galauti Kebab',
            'Malai Tikka',
            'Hariyali Tikka',
            'Achari Tikka',
            'Lemon Coriander Chicken',
            'Butter Chicken',
            'Lamb Kebab',
            'Fish Tikka',
            'Prawn Tikka',
            'Mixed Grill',
            'Tandoori Fish',
            'Tandoori Prawns',
            'Tandoori Lamb',
            'Chicken Tikka Masala',
            'Paneer Tikka Masala',
            'Lamb Tikka Masala',
            'Rogan Josh',
            'Chettinad Chicken',
            'Hyderabadi Biryani Chicken',
            'Lucknowi Biryani',
            'Nihari',
            'Korma',
            'Dopiaza',
            'Vindaloo',
            'Jalfrezi',
            'Saag Chicken',
            'Methi Chicken',
        ];

        $items = [];
        foreach ($entrees as $index => $name) {
            $items[] = [
                'name' => $name,
                'slug' => strtolower(str_replace(' ', '-', "$name-ent-" . ($index + 1))),
                'description' => "Premium $name entree",
                'sale_price' => 300.00 + ($index * 15),
                'cost_price' => 120.00 + ($index * 5),
                'quantity_type' => 'grouping',
                'quantity' => 1,
                'food_type' => $index % 2 == 0 ? 'non_veg' : 'veg',
                'is_available' => true,
                'is_active' => true,
                'in_stock' => true,
                'features' => [
                    ['feature' => 'food_type', 'variant' => $index % 2 == 0 ? 'Non-Veg' : 'Veg'],
                ],
                'option_groups' => ['Sizes', 'Add-ons'],
            ];
        }
        return $items;
    }

    private function generateCarbs()
    {
        $carbs = [
            'Basmati Rice',
            'Naan',
            'Roti',
            'Paratha',
            'Biryani',
            'Pulao',
            'Fried Rice',
            'Brown Rice',
            'Jeera Rice',
            'Lemon Rice',
            'Kulcha',
            'Puri',
            'Bhatura',
            'Dosa',
            'Idli',
            'Garlic Naan',
            'Butter Naan',
            'Cheese Naan',
            'Paneer Naan',
            'Peshwari Naan',
            'Tandoori Roti',
            'Methi Paratha',
            'Aloo Paratha',
            'Gobi Paratha',
            'Paneer Paratha',
            'Vegetable Biryani',
            'Chicken Biryani',
            'Lamb Biryani',
            'Prawn Biryani',
            'Vegetable Pulao',
            'Mushroom Pulao',
            'Egg Fried Rice',
        ];

        $items = [];
        foreach ($carbs as $index => $name) {
            $isRice = in_array($name, ['Basmati Rice', 'Biryani', 'Pulao', 'Fried Rice', 'Brown Rice', 'Jeera Rice', 'Lemon Rice']);
            $items[] = [
                'name' => $name,
                'slug' => strtolower(str_replace(' ', '-', "$name-carb-" . ($index + 1))),
                'description' => "Delicious $name",
                'sale_price' => 80.00 + ($index * 5),
                'cost_price' => 30.00 + ($index * 1.5),
                'quantity_type' => 'grouping',
                'quantity' => 1,
                'food_type' => 'veg',
                'is_available' => true,
                'is_active' => true,
                'in_stock' => true,
                'features' => [
                    ['feature' => 'carb_type', 'variant' => $isRice ? 'Rice' : 'Bread'],
                ],
                'option_groups' => [],
            ];
        }
        return $items;
    }

    private function generateDesserts()
    {
        $desserts = [
            'Kheer',
            'Gulab Jamun',
            'Rasgulla',
            'Jalebi',
            'Payasam',
            'Halwa',
            'Barfi',
            'Laddu',
            'Kulfi',
            'Falooda',
            'Chocolate Cake',
            'Cheesecake',
            'Brownie',
            'Tiramisu',
            'Mousse',
            'Carrot Halwa',
            'Moong Dal Halwa',
            'Sooji Halwa',
            'Peda',
            'Kaju Katli',
            'Mysore Pak',
            'Coconut Barfi',
            'Milk Barfi',
            'Chocolate Barfi',
            'Besan Laddu',
            'Motichur Laddu',
            'Coconut Laddu',
            'Pista Kulfi',
            'Mango Kulfi',
            'Strawberry Falooda',
            'Rose Falooda',
            'Mango Cheesecake',
        ];

        $items = [];
        foreach ($desserts as $index => $name) {
            $isGrain = in_array($name, ['Kheer', 'Payasam', 'Halwa', 'Falooda']);
            $items[] = [
                'name' => $name,
                'slug' => strtolower(str_replace(' ', '-', "$name-des-" . ($index + 1))),
                'description' => "Sweet $name dessert",
                'sale_price' => 120.00 + ($index * 8),
                'cost_price' => 40.00 + ($index * 2),
                'quantity_type' => 'grouping',
                'quantity' => 1,
                'food_type' => 'veg',
                'is_available' => true,
                'is_active' => true,
                'in_stock' => true,
                'features' => [
                    ['feature' => 'dessert_type', 'variant' => $isGrain ? 'Grain' : 'Sweets'],
                ],
                'option_groups' => [],
            ];
        }
        return $items;
    }

    private function generateBeverages()
    {
        $beverages = [
            'Mango Lassi',
            'Diet Lassi',
            'Masala Chai',
            'Green Tea',
            'Iced Tea',
            'Lemonade',
            'Fruit Juice',
            'Smoothie',
            'Milkshake',
            'Soft Drink',
            'Coffee',
            'Espresso',
            'Cappuccino',
            'Latte',
            'Iced Coffee',
            'Strawberry Lassi',
            'Banana Lassi',
            'Pistachio Lassi',
            'Rose Lassi',
            'Cardamom Tea',
            'Ginger Tea',
            'Tulsi Tea',
            'Black Tea',
            'Herbal Tea',
            'Iced Green Tea',
            'Mint Lemonade',
            'Orange Juice',
            'Apple Juice',
            'Pomegranate Juice',
            'Mango Smoothie',
            'Strawberry Smoothie',
            'Chocolate Milkshake',
        ];

        $items = [];
        foreach ($beverages as $index => $name) {
            $isDiet = in_array($name, ['Diet Lassi', 'Green Tea', 'Iced Tea', 'Lemonade', 'Iced Coffee']);
            $items[] = [
                'name' => $name,
                'slug' => strtolower(str_replace(' ', '-', "$name-bev-" . ($index + 1))),
                'description' => "Refreshing $name beverage",
                'sale_price' => 60.00 + ($index * 3),
                'cost_price' => 20.00 + ($index * 1),
                'quantity_type' => 'grouping',
                'quantity' => 1,
                'food_type' => 'veg',
                'is_available' => true,
                'is_active' => true,
                'in_stock' => true,
                'features' => [
                    ['feature' => 'beverage_type', 'variant' => $isDiet ? 'Diet' : 'Regular'],
                ],
                'option_groups' => [],
            ];
        }
        return $items;
    }

    /** Attach existing local images using the object types consumed by the app. */
    private function attachImageToModel($model, string $objectType, string $folder, string $filename, string $type = 'M', int $position = 0): void
    {
        if (\Modules\Filemanager\Models\FileLink::where('object_type', $objectType)->where('object_id', $model->id)
            ->where('type', $type)->where('position', $position)->exists()) return;

        $imagePath = base_path("resources/images/{$folder}/{$filename}.jpg");
        if (!is_file($imagePath)) {
            $fallback = glob(base_path("resources/images/{$folder}/*.jpg"));
            $imagePath = $fallback[0] ?? '';
        }
        if (!$imagePath || !is_file($imagePath)) {
            $this->command?->warn("No local image found for {$model->name}.");
            return;
        }
        $uploadedFile = new UploadedFile($imagePath, basename($imagePath), 'image/jpeg', null, true);
        $this->fileService ??= app(FileService::class);
        $link = $this->fileService->uploadFile($uploadedFile, $objectType, $model->id, $type, $position);
        if ($model instanceof FoodCategory) {
            $model->update(['image' => $link->file->path.'/'.$link->file->file_name]);
        }
    }
}
