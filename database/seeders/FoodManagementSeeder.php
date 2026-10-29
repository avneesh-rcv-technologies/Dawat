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

class FoodManagementSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Restaurant Owner role exists
        $restaurantRole = Role::firstOrCreate(
            ['id' => 2],
            [
                'name' => 'Restaurant Owner',
                'description' => 'Restaurant owner role',
                'guard' => 'web',
                'permission_type' => 'custom',
                'permissions' => [],
            ]
        );

        $admin = Admin::first() ?? Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $restaurants = $this->createRestaurants($admin);
        $optionGroups = $this->createOptionGroups();

        // Create feature groups and features
        $featureGroups = $this->createFeatureGroups();

        // Create categories and items for each restaurant
        foreach ($restaurants as $restaurant) {
            $this->createCategoriesAndItems($restaurant, $featureGroups, $optionGroups);
        }

        $this->command->info('✅ Food Management seeding completed!');
    }

    private function createRestaurants($admin)
    {
        $restaurantData = [
            [
                'name' => 'Spice Palace',
                'slug' => 'spice-palace',
                'email' => 'admin@spicepalace.com',
                'password' => 'password123',
                'phone' => '9876543210',
                'description' => 'Authentic Indian cuisine',
                'address_line1' => '123 Main Street',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'country' => 'India',
                'postal_code' => '400001',
                'food_type' => 'veg',
                'is_takeaway' => true,
                'is_delivery' => true,
                'is_accepting_orders' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Vegan Delight',
                'slug' => 'vegan-delight',
                'email' => 'admin@vegandelight.com',
                'password' => 'password123',
                'phone' => '9876543211',
                'description' => 'Pure vegetarian and vegan',
                'address_line1' => '456 Green Avenue',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'country' => 'India',
                'postal_code' => '560001',
                'food_type' => 'veg',
                'is_takeaway' => true,
                'is_delivery' => true,
                'is_accepting_orders' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Meat Masters',
                'slug' => 'meat-masters',
                'email' => 'admin@meatmasters.com',
                'password' => 'password123',
                'phone' => '9876543212',
                'description' => 'Premium non-vegetarian',
                'address_line1' => '789 Meat Lane',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'country' => 'India',
                'postal_code' => '110001',
                'food_type' => 'non_veg',
                'is_takeaway' => true,
                'is_delivery' => true,
                'is_accepting_orders' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Pizza Paradise',
                'slug' => 'pizza-paradise',
                'email' => 'admin@pizzaparadise.com',
                'password' => 'password123',
                'phone' => '9876543213',
                'description' => 'Italian pizzas and pastas',
                'address_line1' => '321 Pizza Street',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'country' => 'India',
                'postal_code' => '411001',
                'food_type' => 'veg',
                'is_takeaway' => true,
                'is_delivery' => true,
                'is_accepting_orders' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Burger Barn',
                'slug' => 'burger-barn',
                'email' => 'admin@burgerbarn.com',
                'password' => 'password123',
                'phone' => '9876543214',
                'description' => 'Gourmet burgers and fries',
                'address_line1' => '654 Burger Boulevard',
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'country' => 'India',
                'postal_code' => '500001',
                'food_type' => 'non_veg',
                'is_takeaway' => true,
                'is_delivery' => true,
                'is_accepting_orders' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Sushi Sensation',
                'slug' => 'sushi-sensation',
                'email' => 'admin@sushisensation.com',
                'password' => 'password123',
                'phone' => '9876543215',
                'description' => 'Fresh Japanese sushi',
                'address_line1' => '987 Sushi Lane',
                'city' => 'Kolkata',
                'state' => 'West Bengal',
                'country' => 'India',
                'postal_code' => '700001',
                'food_type' => 'veg',
                'is_takeaway' => true,
                'is_delivery' => true,
                'is_accepting_orders' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Taco Fiesta',
                'slug' => 'taco-fiesta',
                'email' => 'admin@tacofiesta.com',
                'password' => 'password123',
                'phone' => '9876543216',
                'description' => 'Mexican street food',
                'address_line1' => '147 Taco Street',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'country' => 'India',
                'postal_code' => '600001',
                'food_type' => 'veg',
                'is_takeaway' => true,
                'is_delivery' => true,
                'is_accepting_orders' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Biryani House',
                'slug' => 'biryani-house',
                'email' => 'admin@biryanihouse.com',
                'password' => 'password123',
                'phone' => '9876543217',
                'description' => 'Authentic biryani varieties',
                'address_line1' => '258 Biryani Road',
                'city' => 'Lucknow',
                'state' => 'Uttar Pradesh',
                'country' => 'India',
                'postal_code' => '226001',
                'food_type' => 'veg',
                'is_takeaway' => true,
                'is_delivery' => true,
                'is_accepting_orders' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Cafe Mocha',
                'slug' => 'cafe-mocha',
                'email' => 'admin@cafemocha.com',
                'password' => 'password123',
                'phone' => '9876543218',
                'description' => 'Coffee and pastries',
                'address_line1' => '369 Coffee Lane',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'country' => 'India',
                'postal_code' => '302001',
                'food_type' => 'veg',
                'is_takeaway' => true,
                'is_delivery' => true,
                'is_accepting_orders' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Thai Spice',
                'slug' => 'thai-spice',
                'email' => 'admin@thaispice.com',
                'password' => ' ',
                'phone' => '9876543219',
                'description' => 'Authentic Thai cuisine',
                'address_line1' => '741 Thai Street',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'country' => 'India',
                'postal_code' => '380001',
                'food_type' => 'veg',
                'is_takeaway' => true,
                'is_delivery' => true,
                'is_accepting_orders' => true,
                'status' => 'active',
            ],
        ];

        $created = [];
        foreach ($restaurantData as $data) {
            $restaurant = Restaurant::where('slug', $data['slug'])->first();

            if (!$restaurant) {
                // Extract email and password for admin creation
                $email = $data['email'];
                $password = $data['password'];
                unset($data['email'], $data['password']);

                // Create admin account for this restaurant
                $restaurantAdmin = \Modules\Acl\Models\Admin::create([
                    'name' => $data['name'],
                    'email' => $email,
                    'password' => bcrypt($password),
                    'role_id' => 2, // Restaurant owner role
                    'status' => true,
                ]);

                // Create restaurant linked to admin
                $data['admin_id'] = $restaurantAdmin->id;
                $restaurant = Restaurant::create($data);

                $this->command->info("✅ Created restaurant: {$data['name']}");
                $this->command->info("   Email: {$email}");
                $this->command->info("   Password: {$password}");
            } else {
                $this->command->info("✅ Found existing restaurant: {$data['name']}");
            }

            $created[] = $restaurant;
        }

        $this->command->info('✅ Created/Found ' . count($created) . ' restaurants');
        return $created;
    }

    private function createOptionGroups()
    {
        $optionGroups = [];

        $sizesGroup = FoodOptionGroup::where('name', 'Sizes')->first();
        if (!$sizesGroup) {
            $sizesGroup = FoodOptionGroup::create([
                'name' => 'Sizes',
                'is_required' => false,
                'min' => 0,
                'max' => 1,
            ]);
            foreach (['Small', 'Medium', 'Large'] as $size) {
                FoodOption::create([
                    'food_option_group_id' => $sizesGroup->id,
                    'name' => $size,
                    'is_active' => true,
                ]);
            }
        }
        $optionGroups['Sizes'] = $sizesGroup;

        $addOnsGroup = FoodOptionGroup::where('name', 'Add-ons')->first();
        if (!$addOnsGroup) {
            $addOnsGroup = FoodOptionGroup::create([
                'name' => 'Add-ons',
                'is_required' => false,
                'min' => 0,
                'max' => 5,
            ]);
            foreach (['Extra Cheese', 'Extra Sauce', 'Extra Vegetables'] as $addon) {
                FoodOption::create([
                    'food_option_group_id' => $addOnsGroup->id,
                    'name' => $addon,
                    'is_active' => true,
                ]);
            }
        }
        $optionGroups['Add-ons'] = $addOnsGroup;

        $this->command->info('✅ Created/Found ' . count($optionGroups) . ' option groups');
        return $optionGroups;
    }

    private function createFeatureGroups()
    {
        $groups = [];

        // Feature Group 1: Food Attributes
        $foodAttrGroup = FoodFeature::where('feature_code', 'food_attributes')->first();
        if (!$foodAttrGroup) {
            $foodAttrGroup = FoodFeature::create([
                'feature_code' => 'food_attributes',
                'purpose' => 'filter',
                'feature_style' => 'select',
                'filter_style' => 'checkbox',
                'feature_type' => 'G',
                'parent_id' => 0,
                'display_on_product' => 'Y',
                'display_on_catalog' => 'Y',
                'display_on_header' => 'N',
                'status' => 'A',
                'position' => 1,
            ]);

            FoodFeatureDescription::create([
                'feature_id' => $foodAttrGroup->feature_id,
                'lang_code' => 'en',
                'name' => 'Food Attributes',
            ]);
        }
        $groups['food_attributes'] = $foodAttrGroup;

        // Feature Group 2: Carb Attributes
        $carbAttrGroup = FoodFeature::where('feature_code', 'carb_attributes')->first();
        if (!$carbAttrGroup) {
            $carbAttrGroup = FoodFeature::create([
                'feature_code' => 'carb_attributes',
                'purpose' => 'filter',
                'feature_style' => 'select',
                'filter_style' => 'checkbox',
                'feature_type' => 'G',
                'parent_id' => 0,
                'display_on_product' => 'Y',
                'display_on_catalog' => 'Y',
                'display_on_header' => 'N',
                'status' => 'A',
                'position' => 2,
            ]);

            FoodFeatureDescription::create([
                'feature_id' => $carbAttrGroup->feature_id,
                'lang_code' => 'en',
                'name' => 'Carb Attributes',
            ]);
        }
        $groups['carb_attributes'] = $carbAttrGroup;

        // Feature Group 3: Dessert Attributes
        $dessertAttrGroup = FoodFeature::where('feature_code', 'dessert_attributes')->first();
        if (!$dessertAttrGroup) {
            $dessertAttrGroup = FoodFeature::create([
                'feature_code' => 'dessert_attributes',
                'purpose' => 'filter',
                'feature_style' => 'select',
                'filter_style' => 'checkbox',
                'feature_type' => 'G',
                'parent_id' => 0,
                'display_on_product' => 'Y',
                'display_on_catalog' => 'Y',
                'display_on_header' => 'N',
                'status' => 'A',
                'position' => 3,
            ]);

            FoodFeatureDescription::create([
                'feature_id' => $dessertAttrGroup->feature_id,
                'lang_code' => 'en',
                'name' => 'Dessert Attributes',
            ]);
        }
        $groups['dessert_attributes'] = $dessertAttrGroup;

        // Feature Group 4: Beverage Attributes
        $beverageAttrGroup = FoodFeature::where('feature_code', 'beverage_attributes')->first();
        if (!$beverageAttrGroup) {
            $beverageAttrGroup = FoodFeature::create([
                'feature_code' => 'beverage_attributes',
                'purpose' => 'filter',
                'feature_style' => 'select',
                'filter_style' => 'checkbox',
                'feature_type' => 'G',
                'parent_id' => 0,
                'display_on_product' => 'Y',
                'display_on_catalog' => 'Y',
                'display_on_header' => 'N',
                'status' => 'A',
                'position' => 4,
            ]);

            FoodFeatureDescription::create([
                'feature_id' => $beverageAttrGroup->feature_id,
                'lang_code' => 'en',
                'name' => 'Beverage Attributes',
            ]);
        }
        $groups['beverage_attributes'] = $beverageAttrGroup;

        // Now create features under groups
        $this->createFeaturesUnderGroups($groups);

        $this->command->info('✅ Created/Found ' . count($groups) . ' feature groups');
        return $groups;
    }

    private function createFeaturesUnderGroups(&$groups)
    {
        // Feature 1: Food Type (under Food Attributes group)
        $foodTypeFeature = FoodFeature::where('feature_code', 'food_type')->first();
        if (!$foodTypeFeature) {
            $foodTypeFeature = FoodFeature::create([
                'feature_code' => 'food_type',
                'purpose' => 'filter',
                'feature_style' => 'select',
                'filter_style' => 'checkbox',
                'feature_type' => 'F',
                'parent_id' => $groups['food_attributes']->feature_id,
                'display_on_product' => 'Y',
                'display_on_catalog' => 'Y',
                'display_on_header' => 'N',
                'status' => 'A',
                'position' => 1,
            ]);

            FoodFeatureDescription::create([
                'feature_id' => $foodTypeFeature->feature_id,
                'lang_code' => 'en',
                'name' => 'Food Type',
            ]);

            $variants = [['name' => 'Veg'], ['name' => 'Non-Veg']];
            foreach ($variants as $index => $variant) {
                $variantModel = FoodFeatureVariant::create([
                    'feature_id' => $foodTypeFeature->feature_id,
                    'status' => 'A',
                    'position' => $index + 1,
                ]);
                FoodFeatureVariantDescription::create([
                    'variant_id' => $variantModel->variant_id,
                    'lang_code' => 'en',
                    'description' => $variant['name'],
                ]);
            }
        }
        $groups['food_type'] = $foodTypeFeature;

        // Feature 2: Carb Type (under Carb Attributes group)
        $carbTypeFeature = FoodFeature::where('feature_code', 'carb_type')->first();
        if (!$carbTypeFeature) {
            $carbTypeFeature = FoodFeature::create([
                'feature_code' => 'carb_type',
                'purpose' => 'filter',
                'feature_style' => 'select',
                'filter_style' => 'checkbox',
                'feature_type' => 'F',
                'parent_id' => $groups['carb_attributes']->feature_id,
                'display_on_product' => 'Y',
                'display_on_catalog' => 'Y',
                'display_on_header' => 'N',
                'status' => 'A',
                'position' => 1,
            ]);

            FoodFeatureDescription::create([
                'feature_id' => $carbTypeFeature->feature_id,
                'lang_code' => 'en',
                'name' => 'Carb Type',
            ]);

            $variants = [['name' => 'Rice'], ['name' => 'Bread']];
            foreach ($variants as $index => $variant) {
                $variantModel = FoodFeatureVariant::create([
                    'feature_id' => $carbTypeFeature->feature_id,
                    'status' => 'A',
                    'position' => $index + 1,
                ]);
                FoodFeatureVariantDescription::create([
                    'variant_id' => $variantModel->variant_id,
                    'lang_code' => 'en',
                    'description' => $variant['name'],
                ]);
            }
        }
        $groups['carb_type'] = $carbTypeFeature;

        // Feature 3: Dessert Type (under Dessert Attributes group)
        $dessertTypeFeature = FoodFeature::where('feature_code', 'dessert_type')->first();
        if (!$dessertTypeFeature) {
            $dessertTypeFeature = FoodFeature::create([
                'feature_code' => 'dessert_type',
                'purpose' => 'filter',
                'feature_style' => 'select',
                'filter_style' => 'checkbox',
                'feature_type' => 'F',
                'parent_id' => $groups['dessert_attributes']->feature_id,
                'display_on_product' => 'Y',
                'display_on_catalog' => 'Y',
                'display_on_header' => 'N',
                'status' => 'A',
                'position' => 1,
            ]);

            FoodFeatureDescription::create([
                'feature_id' => $dessertTypeFeature->feature_id,
                'lang_code' => 'en',
                'name' => 'Dessert Type',
            ]);

            $variants = [['name' => 'Grain'], ['name' => 'Sweets']];
            foreach ($variants as $index => $variant) {
                $variantModel = FoodFeatureVariant::create([
                    'feature_id' => $dessertTypeFeature->feature_id,
                    'status' => 'A',
                    'position' => $index + 1,
                ]);
                FoodFeatureVariantDescription::create([
                    'variant_id' => $variantModel->variant_id,
                    'lang_code' => 'en',
                    'description' => $variant['name'],
                ]);
            }
        }
        $groups['dessert_type'] = $dessertTypeFeature;

        // Feature 4: Beverage Type (under Beverage Attributes group)
        $beverageTypeFeature = FoodFeature::where('feature_code', 'beverage_type')->first();
        if (!$beverageTypeFeature) {
            $beverageTypeFeature = FoodFeature::create([
                'feature_code' => 'beverage_type',
                'purpose' => 'filter',
                'feature_style' => 'select',
                'filter_style' => 'checkbox',
                'feature_type' => 'F',
                'parent_id' => $groups['beverage_attributes']->feature_id,
                'display_on_product' => 'Y',
                'display_on_catalog' => 'Y',
                'display_on_header' => 'N',
                'status' => 'A',
                'position' => 1,
            ]);

            FoodFeatureDescription::create([
                'feature_id' => $beverageTypeFeature->feature_id,
                'lang_code' => 'en',
                'name' => 'Beverage Type',
            ]);

            $variants = [['name' => 'Regular'], ['name' => 'Diet']];
            foreach ($variants as $index => $variant) {
                $variantModel = FoodFeatureVariant::create([
                    'feature_id' => $beverageTypeFeature->feature_id,
                    'status' => 'A',
                    'position' => $index + 1,
                ]);
                FoodFeatureVariantDescription::create([
                    'variant_id' => $variantModel->variant_id,
                    'lang_code' => 'en',
                    'description' => $variant['name'],
                ]);
            }
        }
        $groups['beverage_type'] = $beverageTypeFeature;
    }

    private function createCategoriesAndItems($restaurant, $featureGroups, $optionGroups)
    {
        $categoriesData = match ($restaurant->slug) {
            'spice-palace' => $this->getSpicePalaceCategories(),
            'vegan-delight' => $this->getVeganDelightCategories(),
            'meat-masters' => $this->getMeatMastersCategories(),
            'pizza-paradise' => $this->getPizzaParadiseCategories(),
            'burger-barn' => $this->getBurgerBarnCategories(),
            'sushi-sensation' => $this->getSushiSensationCategories(),
            'taco-fiesta' => $this->getTacoFiestaCategories(),
            'biryani-house' => $this->getBiryaniHouseCategories(),
            'cafe-mocha' => $this->getCafeMochaCategories(),
            'thai-spice' => $this->getThaiSpiceCategories(),
            default => [],
        };

        foreach ($categoriesData as $categoryData) {
            $itemsData = $categoryData['items'];
            $linkedFeatures = $categoryData['linked_features'] ?? [];
            unset($categoryData['items'], $categoryData['linked_features']);

            $category = FoodCategory::where('restaurant_id', $restaurant->id)
                ->where('slug', $categoryData['slug'])
                ->first();

            if (!$category) {
                $category = FoodCategory::create([
                    'restaurant_id' => $restaurant->id,
                    'name' => $categoryData['name'],
                    'slug' => $categoryData['slug'],
                    'description' => $categoryData['description'],
                    'food_type' => $categoryData['food_type'],
                    'status' => true,
                ]);

                // Link features to this category via food_feature_categories table
                // linkedFeatures contains feature codes like 'food_type', 'carb_type'
                foreach ($linkedFeatures as $featureCode) {
                    $feature = FoodFeature::where('feature_code', $featureCode)->first();
                    if ($feature) {
                        // Use the categories relationship from FoodFeature to link
                        $feature->categories()->syncWithoutDetaching([$category->id]);
                    }
                }
            }

            foreach ($itemsData as $itemData) {
                $itemFeatures = $itemData['features'];
                $itemOptionGroups = $itemData['option_groups'];
                unset($itemData['features'], $itemData['option_groups']);

                $foodItem = FoodItem::where('slug', $itemData['slug'])->first();

                if (!$foodItem) {
                    $foodItem = FoodItem::create($itemData);
                    $foodItem->categories()->attach($category->id);

                    foreach ($itemOptionGroups as $groupName) {
                        if (isset($optionGroups[$groupName])) {
                            $foodItem->optionGroups()->attach($optionGroups[$groupName]->id);
                        }
                    }

                    foreach ($itemFeatures as $featureData) {
                        $featureName = $featureData['feature'];
                        $variantName = $featureData['variant'];

                        if (isset($featureGroups[$featureName])) {
                            $feature = $featureGroups[$featureName];
                            $variant = $feature->variants()
                                ->whereHas('descriptions', function ($q) use ($variantName) {
                                    $q->where('description', $variantName);
                                })
                                ->first();

                            if ($variant) {
                                FoodFeatureValue::create([
                                    'food_item_id' => $foodItem->id,
                                    'feature_id' => $feature->feature_id,
                                    'variant_id' => $variant->variant_id,
                                    'value' => $variantName,
                                    'lang_code' => 'en',
                                ]);
                            }
                        }
                    }
                }
            }

            $this->command->info("✅ Category '{$category->name}' for {$restaurant->name}");
        }
    }

    private function getSpicePalaceCategories()
    {
        return [
            [
                'name' => 'Entrees',
                'slug' => 'entrees-sp',
                'description' => 'Main entree dishes',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateEntreeItems('sp', 75),
            ],
            [
                'name' => 'Meat Section',
                'slug' => 'meat-section-sp',
                'description' => 'Premium meat dishes',
                'food_type' => 'non_veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateMeatItems('sp', 80),
            ],
            [
                'name' => 'Carbs',
                'slug' => 'carbs-sp',
                'description' => 'Rice and bread',
                'food_type' => 'veg',
                'linked_features' => ['carb_type'],
                'items' => $this->generateCarbItems('sp', 60),
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts-sp',
                'description' => 'Sweet desserts',
                'food_type' => 'veg',
                'linked_features' => ['dessert_type'],
                'items' => $this->generateDessertItems('sp', 70),
            ],
            [
                'name' => 'Beverage',
                'slug' => 'beverage-sp',
                'description' => 'Drinks and beverages',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateBeverageItems('sp', 50),
            ],
            [
                'name' => 'Appetizers',
                'slug' => 'appetizers-sp',
                'description' => 'Starters and appetizers',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateAppetizers('sp', 65),
            ],
            [
                'name' => 'Soups',
                'slug' => 'soups-sp',
                'description' => 'Traditional soups',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateSoups('sp', 55),
            ],
            [
                'name' => 'Breads',
                'slug' => 'breads-sp',
                'description' => 'Specialty breads',
                'food_type' => 'veg',
                'linked_features' => ['carb_type'],
                'items' => $this->generateBreads('sp', 50),
            ],
        ];
    }

    private function getVeganDelightCategories()
    {
        return [
            [
                'name' => 'Entrees',
                'slug' => 'entrees-vd',
                'description' => 'Vegan main dishes',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateVeganEntreeItems('vd', 80),
            ],
            [
                'name' => 'Carbs',
                'slug' => 'carbs-vd',
                'description' => 'Rice and bread',
                'food_type' => 'veg',
                'linked_features' => ['carb_type'],
                'items' => $this->generateCarbItems('vd', 65),
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts-vd',
                'description' => 'Vegan desserts',
                'food_type' => 'veg',
                'linked_features' => ['dessert_type'],
                'items' => $this->generateVeganDessertItems('vd', 75),
            ],
            [
                'name' => 'Beverage',
                'slug' => 'beverage-vd',
                'description' => 'Vegan drinks',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateVeganBeverageItems('vd', 55),
            ],
            [
                'name' => 'Salads',
                'slug' => 'salads-vd',
                'description' => 'Fresh vegan salads',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateSalads('vd', 60),
            ],
            [
                'name' => 'Smoothies',
                'slug' => 'smoothies-vd',
                'description' => 'Healthy smoothies',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateSmoothies('vd', 50),
            ],
        ];
    }

    private function getMeatMastersCategories()
    {
        return [
            [
                'name' => 'Entrees',
                'slug' => 'entrees-mm',
                'description' => 'Premium meat entrees',
                'food_type' => 'non_veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateMeatEntreeItems('mm', 90),
            ],
            [
                'name' => 'Meat Section',
                'slug' => 'meat-section-mm',
                'description' => 'Premium meat curries',
                'food_type' => 'non_veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateMeatCurriesItems('mm', 85),
            ],
            [
                'name' => 'Carbs',
                'slug' => 'carbs-mm',
                'description' => 'Rice and bread',
                'food_type' => 'veg',
                'linked_features' => ['carb_type'],
                'items' => $this->generateCarbItems('mm', 60),
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts-mm',
                'description' => 'Sweet desserts',
                'food_type' => 'veg',
                'linked_features' => ['dessert_type'],
                'items' => $this->generateDessertItems('mm', 70),
            ],
            [
                'name' => 'Beverage',
                'slug' => 'beverage-mm',
                'description' => 'Drinks and beverages',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateBeverageItems('mm', 50),
            ],
            [
                'name' => 'Grilled Specials',
                'slug' => 'grilled-specials-mm',
                'description' => 'Grilled meat specialties',
                'food_type' => 'non_veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateGrilledSpecials('mm', 70),
            ],
        ];
    }

    private function getPizzaParadiseCategories()
    {
        return [
            [
                'name' => 'Pizzas',
                'slug' => 'pizzas-pp',
                'description' => 'Italian pizzas',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generatePizzas('pp', 100),
            ],
            [
                'name' => 'Pastas',
                'slug' => 'pastas-pp',
                'description' => 'Italian pastas',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generatePastas('pp', 80),
            ],
            [
                'name' => 'Appetizers',
                'slug' => 'appetizers-pp',
                'description' => 'Italian appetizers',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateAppetizers('pp', 70),
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts-pp',
                'description' => 'Italian desserts',
                'food_type' => 'veg',
                'linked_features' => ['dessert_type'],
                'items' => $this->generateDessertItems('pp', 60),
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages-pp',
                'description' => 'Drinks and wines',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateBeverageItems('pp', 50),
            ],
        ];
    }

    private function getBurgerBarnCategories()
    {
        return [
            [
                'name' => 'Burgers',
                'slug' => 'burgers-bb',
                'description' => 'Gourmet burgers',
                'food_type' => 'non_veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateBurgers('bb', 90),
            ],
            [
                'name' => 'Fries & Sides',
                'slug' => 'fries-sides-bb',
                'description' => 'Fries and side dishes',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateFriesAndSides('bb', 50),
            ],
            [
                'name' => 'Sandwiches',
                'slug' => 'sandwiches-bb',
                'description' => 'Specialty sandwiches',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateSandwiches('bb', 70),
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts-bb',
                'description' => 'Sweet treats',
                'food_type' => 'veg',
                'linked_features' => ['dessert_type'],
                'items' => $this->generateDessertItems('bb', 50),
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages-bb',
                'description' => 'Drinks and shakes',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateBeverageItems('bb', 60),
            ],
        ];
    }

    private function getSushiSensationCategories()
    {
        return [
            [
                'name' => 'Sushi Rolls',
                'slug' => 'sushi-rolls-ss',
                'description' => 'Fresh sushi rolls',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateSushiRolls('ss', 100),
            ],
            [
                'name' => 'Nigiri',
                'slug' => 'nigiri-ss',
                'description' => 'Nigiri sushi',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateNigiri('ss', 80),
            ],
            [
                'name' => 'Appetizers',
                'slug' => 'appetizers-ss',
                'description' => 'Japanese appetizers',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateAppetizers('ss', 70),
            ],
            [
                'name' => 'Soups',
                'slug' => 'soups-ss',
                'description' => 'Japanese soups',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateSoups('ss', 50),
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages-ss',
                'description' => 'Drinks and sake',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateBeverageItems('ss', 50),
            ],
        ];
    }

    private function getTacoFiestaCategories()
    {
        return [
            [
                'name' => 'Tacos',
                'slug' => 'tacos-tf',
                'description' => 'Mexican tacos',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateTacos('tf', 100),
            ],
            [
                'name' => 'Burritos',
                'slug' => 'burritos-tf',
                'description' => 'Stuffed burritos',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateBurritos('tf', 80),
            ],
            [
                'name' => 'Enchiladas',
                'slug' => 'enchiladas-tf',
                'description' => 'Mexican enchiladas',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateEnchiladas('tf', 70),
            ],
            [
                'name' => 'Sides',
                'slug' => 'sides-tf',
                'description' => 'Mexican sides',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateSides('tf', 60),
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages-tf',
                'description' => 'Drinks',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateBeverageItems('tf', 50),
            ],
        ];
    }

    private function getBiryaniHouseCategories()
    {
        return [
            [
                'name' => 'Biryani',
                'slug' => 'biryani-bh',
                'description' => 'Authentic biryani',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateBiryani('bh', 100),
            ],
            [
                'name' => 'Curries',
                'slug' => 'curries-bh',
                'description' => 'Indian curries',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateCurries('bh', 80),
            ],
            [
                'name' => 'Breads',
                'slug' => 'breads-bh',
                'description' => 'Indian breads',
                'food_type' => 'veg',
                'linked_features' => ['carb_type'],
                'items' => $this->generateBreads('bh', 60),
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts-bh',
                'description' => 'Indian desserts',
                'food_type' => 'veg',
                'linked_features' => ['dessert_type'],
                'items' => $this->generateDessertItems('bh', 70),
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages-bh',
                'description' => 'Drinks',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateBeverageItems('bh', 50),
            ],
        ];
    }

    private function getCafeMochaCategories()
    {
        return [
            [
                'name' => 'Coffee',
                'slug' => 'coffee-cm',
                'description' => 'Premium coffee',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateCoffee('cm', 80),
            ],
            [
                'name' => 'Pastries',
                'slug' => 'pastries-cm',
                'description' => 'Fresh pastries',
                'food_type' => 'veg',
                'linked_features' => ['dessert_type'],
                'items' => $this->generatePastries('cm', 70),
            ],
            [
                'name' => 'Sandwiches',
                'slug' => 'sandwiches-cm',
                'description' => 'Breakfast sandwiches',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateSandwiches('cm', 60),
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages-cm',
                'description' => 'Hot and cold drinks',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateBeverageItems('cm', 70),
            ],
        ];
    }

    private function getThaiSpiceCategories()
    {
        return [
            [
                'name' => 'Curries',
                'slug' => 'curries-ts',
                'description' => 'Thai curries',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateCurries('ts', 90),
            ],
            [
                'name' => 'Stir Fries',
                'slug' => 'stir-fries-ts',
                'description' => 'Thai stir fries',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateStirFries('ts', 80),
            ],
            [
                'name' => 'Soups',
                'slug' => 'soups-ts',
                'description' => 'Thai soups',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateSoups('ts', 70),
            ],
            [
                'name' => 'Salads',
                'slug' => 'salads-ts',
                'description' => 'Thai salads',
                'food_type' => 'veg',
                'linked_features' => ['food_type'],
                'items' => $this->generateSalads('ts', 60),
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts-ts',
                'description' => 'Thai desserts',
                'food_type' => 'veg',
                'linked_features' => ['dessert_type'],
                'items' => $this->generateDessertItems('ts', 50),
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages-ts',
                'description' => 'Thai drinks',
                'food_type' => 'veg',
                'linked_features' => ['beverage_type'],
                'items' => $this->generateBeverageItems('ts', 50),
            ],
        ];
    }

    // ============ BULK ITEM GENERATORS ============

    private function generateEntreeItems($prefix, $count)
    {
        $items = [];
        $entreeNames = ['Tandoori Chicken', 'Paneer Tikka', 'Chicken Tikka', 'Seekh Kebab', 'Shami Kebab', 'Galauti Kebab', 'Malai Tikka', 'Hariyali Tikka', 'Achari Tikka', 'Lemon Coriander Chicken'];
        
        for ($i = 1; $i <= $count; $i++) {
            $baseName = $entreeNames[$i % count($entreeNames)];
            $items[] = [
                'name' => "$baseName #$i",
                'slug' => strtolower(str_replace(' ', '-', "$baseName-$i-$prefix")),
                'description' => "Delicious $baseName variant $i",
                'sale_price' => 250.00 + ($i * 5),
                'cost_price' => 100.00 + ($i * 2),
                'quantity_type' => 'grouping',
                'quantity' => 1,
                'food_type' => $i % 2 == 0 ? 'veg' : 'non_veg',
                'is_available' => true,
                'is_active' => true,
                'in_stock' => true,
                'features' => [
                    ['feature' => 'food_type', 'variant' => $i % 2 == 0 ? 'Veg' : 'Non-Veg'],
                ],
                'option_groups' => ['Sizes', 'Add-ons'],
            ];
        }
        return $items;
    }

    private function generateMeatItems($prefix, $count)
    {
        $items = [];
        $meatNames = ['Butter Chicken', 'Lamb Kebab', 'Chicken Curry', 'Mutton Curry', 'Fish Curry', 'Prawn Curry', 'Chicken Biryani', 'Lamb Biryani', 'Chicken Dopiaza', 'Lamb Dopiaza'];
        
        for ($i = 1; $i <= $count; $i++) {
            $baseName = $meatNames[$i % count($meatNames)];
            $items[] = [
                'name' => "$baseName #$i",
                'slug' => strtolower(str_replace(' ', '-', "$baseName-$i-$prefix")),
                'description' => "Premium $baseName variant $i",
                'sale_price' => 350.00 + ($i * 8),
                'cost_price' => 140.00 + ($i * 3),
                'quantity_type' => 'grouping',
                'quantity' => 1,
                'food_type' => 'non_veg',
                'is_available' => true,
                'is_active' => true,
                'in_stock' => true,
                'features' => [
                    ['feature' => 'food_type', 'variant' => 'Non-Veg'],
                ],
                'option_groups' => ['Sizes', 'Add-ons'],
            ];
        }
        return $items;
    }

    private function generateCarbItems($prefix, $count)
    {
        $items = [];
        $carbNames = ['Basmati Rice', 'Naan', 'Roti', 'Paratha', 'Biryani', 'Pulao', 'Fried Rice', 'Brown Rice', 'Jeera Rice', 'Lemon Rice'];
        
        for ($i = 1; $i <= $count; $i++) {
            $baseName = $carbNames[$i % count($carbNames)];
            $isRice = in_array($baseName, ['Basmati Rice', 'Biryani', 'Pulao', 'Fried Rice', 'Brown Rice', 'Jeera Rice', 'Lemon Rice']);
            $items[] = [
                'name' => "$baseName #$i",
                'slug' => strtolower(str_replace(' ', '-', "$baseName-$i-$prefix")),
                'description' => "Delicious $baseName variant $i",
                'sale_price' => 60.00 + ($i * 2),
                'cost_price' => 20.00 + ($i * 0.5),
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

    private function generateDessertItems($prefix, $count)
    {
        $items = [];
        $dessertNames = ['Kheer', 'Gulab Jamun', 'Rasgulla', 'Jalebi', 'Payasam', 'Halwa', 'Barfi', 'Laddu', 'Kulfi', 'Falooda'];
        
        for ($i = 1; $i <= $count; $i++) {
            $baseName = $dessertNames[$i % count($dessertNames)];
            $isGrain = in_array($baseName, ['Kheer', 'Payasam', 'Halwa', 'Falooda']);
            $items[] = [
                'name' => "$baseName #$i",
                'slug' => strtolower(str_replace(' ', '-', "$baseName-$i-$prefix")),
                'description' => "Sweet $baseName variant $i",
                'sale_price' => 100.00 + ($i * 3),
                'cost_price' => 35.00 + ($i * 1),
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

    private function generateBeverageItems($prefix, $count)
    {
        $items = [];
        $beverageNames = ['Mango Lassi', 'Diet Lassi', 'Masala Chai', 'Green Tea', 'Iced Tea', 'Lemonade', 'Fruit Juice', 'Smoothie', 'Milkshake', 'Soft Drink'];
        
        for ($i = 1; $i <= $count; $i++) {
            $baseName = $beverageNames[$i % count($beverageNames)];
            $isDiet = in_array($baseName, ['Diet Lassi', 'Green Tea', 'Iced Tea', 'Lemonade']);
            $items[] = [
                'name' => "$baseName #$i",
                'slug' => strtolower(str_replace(' ', '-', "$baseName-$i-$prefix")),
                'description' => "Refreshing $baseName variant $i",
                'sale_price' => 50.00 + ($i * 1),
                'cost_price' => 15.00 + ($i * 0.3),
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

    private function generateVeganEntreeItems($prefix, $count)
    {
        $items = [];
        $veganNames = ['Tofu Stir Fry', 'Quinoa Bowl', 'Chickpea Curry', 'Lentil Tikka', 'Vegetable Kebab', 'Mushroom Tikka', 'Cauliflower Steak', 'Jackfruit Curry', 'Tempeh Stir Fry', 'Seitan Kebab'];
        
        for ($i = 1; $i <= $count; $i++) {
            $baseName = $veganNames[$i % count($veganNames)];
            $items[] = [
                'name' => "$baseName #$i",
                'slug' => strtolower(str_replace(' ', '-', "$baseName-$i-$prefix")),
                'description' => "Vegan $baseName variant $i",
                'sale_price' => 280.00 + ($i * 4),
                'cost_price' => 100.00 + ($i * 1.5),
                'quantity_type' => 'grouping',
                'quantity' => 1,
                'food_type' => 'veg',
                'is_available' => true,
                'is_active' => true,
                'in_stock' => true,
                'features' => [
                    ['feature' => 'food_type', 'variant' => 'Veg'],
                ],
                'option_groups' => ['Sizes'],
            ];
        }
        return $items;
    }

    private function generateVeganDessertItems($prefix, $count)
    {
        $items = [];
        $veganDesserts = ['Coconut Pudding', 'Vegan Chocolate Cake', 'Avocado Mousse', 'Chia Pudding', 'Vegan Brownie', 'Fruit Sorbet', 'Coconut Ice Cream', 'Vegan Cheesecake', 'Almond Cake', 'Date Balls'];
        
        for ($i = 1; $i <= $count; $i++) {
            $baseName = $veganDesserts[$i % count($veganDesserts)];
            $isGrain = in_array($baseName, ['Coconut Pudding', 'Chia Pudding', 'Coconut Ice Cream']);
            $items[] = [
                'name' => "$baseName #$i",
                'slug' => strtolower(str_replace(' ', '-', "$baseName-$i-$prefix")),
                'description' => "Vegan $baseName variant $i",
                'sale_price' => 120.00 + ($i * 2),
                'cost_price' => 40.00 + ($i * 0.7),
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

    private function generateVeganBeverageItems($prefix, $count)
    {
        $items = [];
        $veganBeverages = ['Almond Milk', 'Low Fat Almond Milk', 'Oat Milk', 'Soy Milk', 'Coconut Milk', 'Cashew Milk', 'Vegan Smoothie', 'Herbal Tea', 'Matcha Latte', 'Turmeric Latte'];
        
        for ($i = 1; $i <= $count; $i++) {
            $baseName = $veganBeverages[$i % count($veganBeverages)];
            $isDiet = in_array($baseName, ['Low Fat Almond Milk', 'Herbal Tea', 'Matcha Latte', 'Turmeric Latte']);
            $items[] = [
                'name' => "$baseName #$i",
                'slug' => strtolower(str_replace(' ', '-', "$baseName-$i-$prefix")),
                'description' => "Vegan $baseName variant $i",
                'sale_price' => 70.00 + ($i * 1),
                'cost_price' => 25.00 + ($i * 0.3),
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

    private function generateMeatEntreeItems($prefix, $count)
    {
        $items = [];
        $meatEntrees = ['Tandoori Chicken', 'Lamb Kebab', 'Seekh Kebab', 'Shami Kebab', 'Galauti Kebab', 'Fish Tikka', 'Prawn Tikka', 'Chicken Malai Tikka', 'Lamb Malai Tikka', 'Mixed Grill'];
        
        for ($i = 1; $i <= $count; $i++) {
            $baseName = $meatEntrees[$i % count($meatEntrees)];
            $items[] = [
                'name' => "$baseName #$i",
                'slug' => strtolower(str_replace(' ', '-', "$baseName-$i-$prefix")),
                'description' => "Premium $baseName variant $i",
                'sale_price' => 400.00 + ($i * 6),
                'cost_price' => 160.00 + ($i * 2.5),
                'quantity_type' => 'grouping',
                'quantity' => 1,
                'food_type' => 'non_veg',
                'is_available' => true,
                'is_active' => true,
                'in_stock' => true,
                'features' => [
                    ['feature' => 'food_type', 'variant' => 'Non-Veg'],
                ],
                'option_groups' => ['Sizes'],
            ];
        }
        return $items;
    }

    private function generateMeatCurriesItems($prefix, $count)
    {
        $items = [];
        $meatCurries = ['Rogan Josh', 'Butter Chicken', 'Chicken Dopiaza', 'Lamb Dopiaza', 'Chicken Vindaloo', 'Lamb Vindaloo', 'Fish Curry', 'Prawn Curry', 'Mutton Curry', 'Chicken Korma'];
        
        for ($i = 1; $i <= $count; $i++) {
            $baseName = $meatCurries[$i % count($meatCurries)];
            $items[] = [
                'name' => "$baseName #$i",
                'slug' => strtolower(str_replace(' ', '-', "$baseName-$i-$prefix")),
                'description' => "Aromatic $baseName variant $i",
                'sale_price' => 380.00 + ($i * 7),
                'cost_price' => 150.00 + ($i * 2.8),
                'quantity_type' => 'grouping',
                'quantity' => 1,
                'food_type' => 'non_veg',
                'is_available' => true,
                'is_active' => true,
                'in_stock' => true,
                'features' => [
                    ['feature' => 'food_type', 'variant' => 'Non-Veg'],
                ],
                'option_groups' => ['Sizes', 'Add-ons'],
            ];
        }
        return $items;
    }

    private function generateAppetizers($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Spring Rolls', 'Samosa', 'Pakora', 'Chicken Wings', 'Mozzarella Sticks', 'Calamari', 'Bruschetta', 'Nachos', 'Hummus', 'Garlic Bread'], 'veg', 'food_type'); }
    private function generateSoups($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Tomato Soup', 'Chicken Soup', 'Lentil Soup', 'Mushroom Soup', 'Corn Soup', 'Minestrone', 'Pho', 'Miso Soup', 'Vegetable Soup', 'Cream of Broccoli'], 'veg', 'food_type'); }
    private function generateBreads($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Naan', 'Roti', 'Paratha', 'Kulcha', 'Puri', 'Bhatura', 'Dosa', 'Idli', 'Uttapam', 'Chapati'], 'veg', 'carb_type'); }
    private function generateSalads($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Caesar Salad', 'Greek Salad', 'Garden Salad', 'Caprese Salad', 'Coleslaw', 'Beetroot Salad', 'Cucumber Salad', 'Tomato Salad', 'Mixed Greens', 'Spinach Salad'], 'veg', 'food_type'); }
    private function generateSmoothies($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Mango Smoothie', 'Strawberry Smoothie', 'Banana Smoothie', 'Mixed Berry', 'Papaya Smoothie', 'Pineapple Smoothie', 'Avocado Smoothie', 'Protein Smoothie', 'Green Smoothie', 'Chocolate Smoothie'], 'veg', 'beverage_type'); }
    private function generatePizzas($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Margherita', 'Pepperoni', 'Vegetarian', 'Meat Lovers', 'BBQ Chicken', 'Hawaiian', 'Quattro Formaggi', 'Prosciutto', 'Seafood', 'Truffle'], 'veg', 'food_type'); }
    private function generatePastas($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Spaghetti Carbonara', 'Fettuccine Alfredo', 'Penne Arrabbiata', 'Lasagna', 'Ravioli', 'Tortellini', 'Linguine', 'Rigatoni', 'Tagliatelle', 'Gnocchi'], 'veg', 'food_type'); }
    private function generateBurgers($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Classic Burger', 'Cheese Burger', 'Bacon Burger', 'Double Burger', 'Mushroom Burger', 'Spicy Burger', 'Gourmet Burger', 'Angus Burger', 'Truffle Burger', 'Crispy Burger'], 'non_veg', 'food_type'); }
    private function generateFriesAndSides($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['French Fries', 'Sweet Potato Fries', 'Onion Rings', 'Coleslaw', 'Corn', 'Beans', 'Mashed Potatoes', 'Grilled Vegetables', 'Mac and Cheese', 'Garlic Bread'], 'veg', 'food_type'); }
    private function generateSandwiches($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Club Sandwich', 'BLT', 'Grilled Cheese', 'Chicken Sandwich', 'Tuna Sandwich', 'Veggie Sandwich', 'Panini', 'Wrap', 'Submarine', 'Open Faced'], 'veg', 'food_type'); }
    private function generateSushiRolls($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['California Roll', 'Dragon Roll', 'Philadelphia Roll', 'Spicy Tuna', 'Rainbow Roll', 'Tempura Roll', 'Volcano Roll', 'Boston Roll', 'Shrimp Tempura', 'Cucumber Roll'], 'veg', 'food_type'); }
    private function generateNigiri($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Salmon Nigiri', 'Tuna Nigiri', 'Shrimp Nigiri', 'Egg Nigiri', 'Squid Nigiri', 'Scallop Nigiri', 'Mackerel Nigiri', 'Yellowtail Nigiri', 'Eel Nigiri', 'Crab Nigiri'], 'veg', 'food_type'); }
    private function generateTacos($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Beef Taco', 'Chicken Taco', 'Fish Taco', 'Carnitas Taco', 'Al Pastor', 'Barbacoa', 'Veggie Taco', 'Shrimp Taco', 'Chorizo Taco', 'Carne Asada'], 'veg', 'food_type'); }
    private function generateBurritos($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Beef Burrito', 'Chicken Burrito', 'Veggie Burrito', 'Bean Burrito', 'Carnitas Burrito', 'Barbacoa Burrito', 'Shrimp Burrito', 'Chorizo Burrito', 'Carne Asada Burrito', 'Super Burrito'], 'veg', 'food_type'); }
    private function generateEnchiladas($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Beef Enchilada', 'Chicken Enchilada', 'Cheese Enchilada', 'Veggie Enchilada', 'Seafood Enchilada', 'Mole Enchilada', 'Verde Enchilada', 'Roja Enchilada', 'Spinach Enchilada', 'Poblano Enchilada'], 'veg', 'food_type'); }
    private function generateSides($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Refried Beans', 'Spanish Rice', 'Cilantro Lime Rice', 'Black Beans', 'Corn Salsa', 'Pico de Gallo', 'Guacamole', 'Sour Cream', 'Cheese Dip', 'Tortilla Chips'], 'veg', 'food_type'); }
    private function generateBiryani($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Chicken Biryani', 'Mutton Biryani', 'Fish Biryani', 'Prawn Biryani', 'Vegetable Biryani', 'Egg Biryani', 'Mixed Biryani', 'Hyderabadi Biryani', 'Lucknowi Biryani', 'Kolkata Biryani'], 'veg', 'food_type'); }
    private function generateCurries($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Butter Chicken', 'Paneer Tikka Masala', 'Chicken Curry', 'Lamb Curry', 'Fish Curry', 'Vegetable Curry', 'Chickpea Curry', 'Lentil Curry', 'Korma', 'Vindaloo'], 'veg', 'food_type'); }
    private function generateStirFries($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Chicken Stir Fry', 'Beef Stir Fry', 'Vegetable Stir Fry', 'Shrimp Stir Fry', 'Tofu Stir Fry', 'Mushroom Stir Fry', 'Broccoli Stir Fry', 'Mixed Stir Fry', 'Cashew Stir Fry', 'Garlic Stir Fry'], 'veg', 'food_type'); }
    private function generateGrilledSpecials($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Grilled Chicken', 'Grilled Lamb', 'Grilled Fish', 'Grilled Shrimp', 'Grilled Steak', 'Grilled Ribs', 'Grilled Kebab', 'Grilled Vegetables', 'Mixed Grill', 'BBQ Platter'], 'non_veg', 'food_type'); }
    private function generatePastries($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Croissant', 'Danish', 'Donut', 'Muffin', 'Scone', 'Eclair', 'Tart', 'Brownie', 'Cookie', 'Biscotti'], 'veg', 'dessert_type'); }
    private function generateCoffee($prefix, $count) { return $this->generateGenericItems($prefix, $count, ['Espresso', 'Americano', 'Cappuccino', 'Latte', 'Macchiato', 'Mocha', 'Flat White', 'Cortado', 'Affogato', 'Cold Brew'], 'veg', 'beverage_type'); }

    private function generateGenericItems($prefix, $count, $names, $foodType, $featureType)
    {
        $items = [];
        for ($i = 1; $i <= $count; $i++) {
            $baseName = $names[$i % count($names)];
            $price = 150.00 + ($i * 5);
            $items[] = [
                'name' => "$baseName #$i",
                'slug' => strtolower(str_replace(' ', '-', "$baseName-$i-$prefix")),
                'description' => "Delicious $baseName variant $i",
                'sale_price' => $price,
                'cost_price' => $price * 0.4,
                'quantity_type' => 'grouping',
                'quantity' => 1,
                'food_type' => $foodType,
                'is_available' => true,
                'is_active' => true,
                'in_stock' => true,
                'features' => [
                    ['feature' => $featureType, 'variant' => $foodType === 'veg' ? 'Veg' : ($foodType === 'non_veg' ? 'Non-Veg' : ($i % 2 == 0 ? 'Veg' : 'Non-Veg'))],
                ],
                'option_groups' => ['Sizes'],
            ];
        }
        return $items;
    }
}
