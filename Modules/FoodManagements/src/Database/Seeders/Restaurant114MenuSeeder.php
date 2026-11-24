<?php

namespace Modules\FoodManagements\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodItem;
use Modules\FoodManagements\Models\FoodFeature;
use Modules\FoodManagements\Models\FoodFeatureValue;
use Modules\FoodManagements\Models\FoodFeatureVariant;
use Modules\FoodManagements\Services\ServingOptionConfiguration;

class Restaurant114MenuSeeder extends Seeder
{
    private const RESTAURANT_ID = 4;

    public function run(): void
    {
        if (! DB::table('restaurants')->where('id', self::RESTAURANT_ID)->exists()) {
            throw new \RuntimeException('Restaurant '.self::RESTAURANT_ID.' does not exist. Create the restaurant before running this seeder.');
        }

        // Read IDs directly to support both UUID and legacy numeric dietary labels.
        $dietaryLabels = DB::table('dietary_labels')->where('backend_value', 'VEGETARIAN')->pluck('id', 'backend_value');
        if (! $dietaryLabels->has('VEGETARIAN')) {
            throw new \RuntimeException('Seed dietary labels before running the restaurant menu seeder.');
        }

        $configuration = app(ServingOptionConfiguration::class);
        $menu = $this->menu();
        // Validate every row before deleting any existing menu data.
        foreach ($menu as &$entry) {
            $validated = Validator::make(
                ['serving_options' => $entry['options']],
                $configuration->rules($entry['options'])
            )->validate();
            $entry['options'] = array_map($configuration->normalize(...), $validated['serving_options']);
        }
        unset($entry);

        $createdOptions = 0;
        DB::transaction(function () use ($dietaryLabels, $menu, &$createdOptions) {
            foreach ($this->restaurantMenuResetQueries() as $query) {
                $query->delete();
            }

            $categories = [];
            foreach ($this->categoryDefinitions() as $name => $description) {
                $categories[$name] = $this->seedRecord(new FoodCategory, $name, [
                    'name' => $name, 'description' => $description,
                    'status' => true, 'food_type' => 'all', 'parent_id' => null,
                ]);
            }

            $features = $this->seedFeatures(array_values($categories));

            foreach ($menu as $entry) {
                $options = $entry['options'];
                $primary = collect($options)->firstWhere('is_available', true) ?? $options[0];
                $category = $categories[$entry['category']];
                $item = $this->seedRecord(new FoodItem, $entry['name'], [
                    'name' => $entry['name'],
                    'description' => $entry['description'],
                    'sale_price' => $primary['discount_price'] ?? $primary['price'],
                    'list_price' => $primary['price'],
                    'tax_percentage' => '5.00',
                    'dietary_label_ids' => $entry['food_type'] === 'veg' ? [(string) $dietaryLabels['VEGETARIAN']] : [],
                    'food_type' => $entry['food_type'],
                    'quantity_type' => 'manual',
                    'in_stock' => true, 'is_available' => true, 'is_active' => true,
                ], $category->getKey(), $entry['category']);

                $item->categories()->syncWithoutDetaching([$category->getKey()]);
                foreach ($options as $order => $option) {
                    $item->servingOptions()->create([...$option, 'sort_order' => $order]);
                    $createdOptions++;
                }

                $this->seedFeatureValues($item, $entry, $features);
            }
        });

        $this->command?->info('Restaurant '.self::RESTAURANT_ID.' menu replaced: '.count($menu).' food items and '.$createdOptions.' serving options across '.count($this->categoryDefinitions()).' categories.');
        $this->command?->info('All serving types and allowed units are represented. Feature group and five current UI types (Yes/No, Single choice, Multiple choice, Text, Number) are seeded and assigned.');
        $this->command?->warn('Prices and 5% tax are demo values. Review them before accepting real orders.');
    }

    protected function restaurantMenuResetQueries(): array
    {
        $itemIds = DB::table('food_items')->select('id')->where('restaurant_id', self::RESTAURANT_ID);
        $categoryIds = DB::table('food_categories')->select('id')->where('restaurant_id', self::RESTAURANT_ID);

        // Unlink menu images, keeping physical files available for transaction rollback.
        // Item/category deletion cascades to serving options and relation rows.
        // Existing feature definitions and groups are reused and updated; item feature values are removed by item deletion.
        return [
            'images' => DB::table('file_links')->where(function ($links) use ($itemIds, $categoryIds) {
                $links->where(function ($items) use ($itemIds) {
                    $items->where('object_type', 'food_image')->whereIn('object_id', $itemIds);
                })->orWhere(function ($categories) use ($categoryIds) {
                    $categories->where('object_type', 'category_image')->whereIn('object_id', $categoryIds);
                });
            }),
            'items' => DB::table('food_items')->where('restaurant_id', self::RESTAURANT_ID),
            'categories' => DB::table('food_categories')->where('restaurant_id', self::RESTAURANT_ID),
        ];
    }

    protected function categoryDefinitions(): array
    {
        return [
            'Appetizers' => 'Small bites and piece-based catering orders.',
            'Sandwiches & Wraps' => 'Boxed lunches and per-person sandwich catering.',
            'Chicken Entrees' => 'Chicken dishes sold by tray or plate.',
            'Beef Entrees' => 'Meat dishes sold by portion or pieces.',
            'Seafood Entrees' => 'Seafood dishes sold in fixed-weight sizes.',
            'Vegetarian Entrees' => 'Vegetarian packages for group dining.',
            'Pasta' => 'Pasta packages for catered meals.',
            'Salads' => 'Salads sold by bowl.',
            'Sides' => 'Side dishes and accompaniments.',
            'Beverages' => 'Drinks sold in fixed-volume sizes.',
        ];
    }

    protected function seedRecord(Model $model, string $name, array $attributes, ?int $categoryId = null, ?string $categoryName = null): Model
    {
        // Reuse earlier seed data even if its slug used a different restaurant prefix.
        $query = $model->newQuery()->where('restaurant_id', self::RESTAURANT_ID)->where('name', $name);
        if ($categoryId !== null) {
            $query->whereHas('categories', fn ($categories) => $categories->where('food_categories.id', $categoryId));
        }
        $existing = $query->first();
        if ($existing) {
            return $existing;
        }

        $base = 'restaurant-'.self::RESTAURANT_ID.'-demo-'.($categoryName ? Str::slug($categoryName).'-' : '').Str::slug($name);
        for ($suffix = 0; ; $suffix++) {
            $slug = $base.($suffix ? '-'.$suffix : '');
            $existing = $model->newQuery()->where('slug', $slug)->first();
            if ($existing) {
                // Reuse our own slug after an administrator renames the record.
                if ((int) $existing->restaurant_id === self::RESTAURANT_ID) {
                    return $existing;
                }

                continue;
            }

            return $model->newQuery()->firstOrCreate([
                'restaurant_id' => self::RESTAURANT_ID, 'slug' => $slug,
            ], $attributes + ['name' => $name]);
        }
    }

    /**
     * Fictional demo offers covering every configured serve-type/unit combination.
     * Quantity is the amount in one option, not the number added to the cart.
     * Per-person offers deliberately have no piece counts or fixed-size labels.
     */
    protected function menu(): array
    {
        $fixed = static fn (string $type, string $name, string $quantity, string $unit, int $serves, string $price, ?string $discount = null): array => [
            'serve_type' => $type, 'serving_name' => $name,
            'quantity' => $quantity, 'unit' => $unit, 'serves' => $serves,
            'price' => $price, 'discount_price' => $discount, 'is_available' => true,
        ];
        $package = static fn (string $name, int $serves, string $price, ?string $discount = null): array => [
            'serve_type' => 'package', 'serving_name' => $name, 'serves' => $serves,
            'price' => $price, 'discount_price' => $discount, 'is_available' => true,
        ];
        $person = static fn (string $name, int $minimum, ?int $maximum, string $price, ?string $discount = null): array => [
            'serve_type' => 'per_person', 'serving_name' => $name,
            'min_guests' => $minimum, 'max_guests' => $maximum,
            'price' => $price, 'discount_price' => $discount, 'is_available' => true,
        ];

        return [
            [
                'category' => 'Appetizers', 'name' => 'Mini Chicken Tacos', 'food_type' => 'non_veg',
                'description' => 'Chicken-filled mini tacos. Each fixed order contains the stated number of pieces.',
                'options' => [
                    $fixed('piece', '25-piece order', '25.00', 'Pieces', 12, '40.00', '35.00'),
                ],
            ],
            [
                'category' => 'Sandwiches & Wraps', 'name' => 'Hero Sandwich Buffet', 'food_type' => 'non_veg',
                'description' => 'Assorted hero sandwiches and condiments, priced per guest. Select the guest count when ordering.',
                'options' => [
                    $person('Standard buffet per guest', 10, 100, '8.95'),
                    $person('Premium buffet per guest', 20, null, '12.95', '10.95'),
                ],
            ],
            [
                'category' => 'Chicken Entrees', 'name' => 'Chicken Francaise', 'food_type' => 'non_veg',
                'description' => 'Chicken in a lemon and white-wine sauce, available as a half tray or a fixed piece order.',
                'options' => [
                    $fixed('tray', 'Half tray', '0.50', 'Trays', 8, '45.00'),
                    $fixed('tray', '12-piece catering tray', '12.00', 'Pieces', 12, '65.00', '60.00'),
                ],
            ],
            [
                'category' => 'Chicken Entrees', 'name' => 'Chicken Parmigiana', 'food_type' => 'non_veg',
                'description' => 'One plated chicken Parmigiana meal with pasta.',
                'options' => [
                    $fixed('plate', 'Individual plate', '1.00', 'Plates', 1, '15.00'),
                ],
            ],
            [
                'category' => 'Beef Entrees', 'name' => 'Italian Meatballs', 'food_type' => 'non_veg',
                'description' => 'Italian-style meatballs in tomato sauce, offered by portion or in a fixed piece serving.',
                'options' => [
                    $fixed('portion', 'Individual portion', '1.00', 'Portions', 1, '9.00'),
                    $fixed('portion', 'Six-meatball portion', '6.00', 'Pieces', 2, '16.00', '14.00'),
                ],
            ],
            [
                'category' => 'Seafood Entrees', 'name' => 'Shrimp Scampi', 'food_type' => 'non_veg',
                'description' => 'Shrimp with lemon, butter, and white wine. Each option has its own fixed weight and total price.',
                'options' => [
                    $fixed('by_weight', 'Half-kilogram serving', '0.50', 'Kg', 3, '45.00'),
                    $fixed('by_weight', '250-gram serving', '250.00', 'Grams', 2, '25.00'),
                    $fixed('by_weight', 'Eight-ounce serving', '8.00', 'Oz', 2, '23.00'),
                    $fixed('by_weight', 'One-pound serving', '1.00', 'Lb', 3, '42.00', '38.00'),
                ],
            ],
            [
                'category' => 'Vegetarian Entrees', 'name' => 'Eggplant Rollatini', 'food_type' => 'veg',
                'description' => 'Cheese-filled eggplant rolls with marinara sauce in fixed-price packages.',
                'options' => [
                    $package('Family package', 6, '30.00'),
                    $package('Event package', 15, '65.00', '60.00'),
                ],
            ],
            [
                'category' => 'Pasta', 'name' => 'Penne Alla Vodka', 'food_type' => 'veg',
                'description' => 'Penne in a creamy tomato sauce, sold as a catering package.',
                'options' => [
                    $package('Pasta party package', 15, '35.00'),
                ],
            ],
            [
                'category' => 'Salads', 'name' => 'Tossed Salad', 'food_type' => 'veg',
                'description' => 'Lettuce, cucumber, tomato, and carrots served in a large sharing bowl.',
                'options' => [
                    $fixed('bowl', 'Large sharing bowl', '1.00', 'Bowls', 4, '18.00', '16.00'),
                ],
            ],
            [
                'category' => 'Sides', 'name' => 'Vegetable Platter', 'food_type' => 'veg',
                'description' => 'A sharing portion of fresh vegetables with dip.',
                'options' => [
                    $fixed('portion', 'Sharing portion', '1.00', 'Portions', 10, '24.95'),
                ],
            ],
            [
                'category' => 'Sandwiches & Wraps', 'name' => 'Wrap Lunch Boxes', 'food_type' => 'non_veg',
                'description' => 'Chicken wraps offered as an individual lunch box or a box of mini wrap pieces.',
                'options' => [
                    $fixed('box', 'Individual lunch box', '1.00', 'Boxes', 1, '12.00'),
                    $fixed('box', '12-piece mini wrap box', '12.00', 'Pieces', 6, '48.00', '42.00'),
                ],
            ],
            [
                'category' => 'Beverages', 'name' => 'Fresh Lemonade', 'food_type' => 'veg',
                'description' => 'Lemonade in fixed-volume containers. The listed price covers the full selected size.',
                'options' => [
                    $fixed('by_volume', 'One-litre bottle', '1.00', 'Litres', 4, '8.00'),
                    $fixed('by_volume', '250-millilitre bottle', '250.00', 'Ml', 1, '3.00'),
                    $fixed('by_volume', '12-fluid-ounce bottle', '12.00', 'Fl oz', 1, '4.00'),
                    $fixed('by_volume', 'Four-cup pitcher', '4.00', 'Cups', 4, '9.00', '8.00'),
                ],
            ],
        ];
    }

    protected function seedFeatures(array $categories): array
    {
        $categoryIds = collect($categories)->map->getKey()->all();

        // Remove demo feature types that are no longer exposed by the Add Feature UI.
        FoodFeature::where('restaurant_id', self::RESTAURANT_ID)
            ->whereIn('feature_code', [
                'MENU_SERVING_SCALE',
                'MENU_CUISINE',
                'MENU_AVAILABLE_FROM',
            ])
            ->delete();

        $group = $this->seedFeature('MENU_DEMO_GROUP', 'Menu Options', 'G', 'demo_menu_group', '', '', 1, 0, $categoryIds);

        $yesNo = $this->seedFeature('MENU_RECOMMENDED', 'Recommended', 'C', 'find_products', 'checkbox', 'checkbox', 1, $group->feature_id, $categoryIds);
        $singleChoice = $this->seedFeature('MENU_SPICE_LEVEL', 'Spice Level', 'S', 'find_products', 'text', 'checkbox', 2, $group->feature_id, $categoryIds);
        $multipleChoice = $this->seedFeature('MENU_DIETARY_OPTIONS', 'Dietary Options', 'M', 'find_products', 'multiple_checkbox', 'checkbox', 3, $group->feature_id, $categoryIds);
        $text = $this->seedFeature('MENU_CHEF_NOTES', 'Chef Notes', 'T', 'describe_product', 'text', '', 4, $group->feature_id, $categoryIds);
        $number = $this->seedFeature('MENU_PREP_MINUTES', 'Preparation Time (minutes)', 'O', 'find_products', 'number', 'slider', 5, $group->feature_id, $categoryIds);

        return [
            'yes_no' => $yesNo,
            'single_choice' => $singleChoice,
            'single_choice_variants' => $this->seedVariants($singleChoice, ['Mild', 'Medium', 'Hot']),
            'multiple_choice' => $multipleChoice,
            'multiple_choice_variants' => $this->seedVariants($multipleChoice, ['Gluten Free Option', 'Dairy Free Option', 'Nut Free Option']),
            'text' => $text,
            'number' => $number,
        ];
    }

    protected function seedFeature(string $code, string $name, string $type, string $purpose, string $style, string $filterStyle, int $position, int $parentId, array $categoryIds): FoodFeature
    {
        $feature = FoodFeature::updateOrCreate(
            ['restaurant_id' => self::RESTAURANT_ID, 'feature_code' => $code],
            [
                'purpose' => $purpose,
                'feature_style' => $style,
                'filter_style' => $filterStyle,
                'feature_type' => $type,
                'parent_id' => $parentId,
                'display_on_product' => 'Y',
                'display_on_catalog' => 'Y',
                'display_on_header' => 'N',
                'status' => 'A',
                'position' => $position,
                'comparison' => 'N',
            ]
        );

        $feature->descriptions()->updateOrCreate(
            ['feature_id' => $feature->feature_id, 'lang_code' => 'en'],
            ['name' => $name, 'description' => $type === 'G' ? 'Demo feature options for restaurant menu items.' : null]
        );
        $feature->categories()->sync($categoryIds);

        return $feature;
    }

    protected function seedVariants(FoodFeature $feature, array $names): array
    {
        $variants = [];
        foreach ($names as $position => $name) {
            $variant = FoodFeatureVariant::firstOrCreate(
                ['feature_id' => $feature->feature_id, 'position' => $position + 1],
                ['status' => 'A']
            );
            $variant->update(['status' => 'A']);
            $variant->descriptions()->updateOrCreate(
                ['variant_id' => $variant->variant_id, 'lang_code' => 'en'],
                ['description' => $name]
            );
            $variants[$name] = $variant;
        }

        $feature->variants()->whereNotIn('variant_id', collect($variants)->map->getKey()->all())->delete();
        return $variants;
    }

    protected function seedFeatureValues(FoodItem $item, array $entry, array $features): void
    {
        $this->createFeatureValue(
            $item,
            $features['yes_no'],
            $entry['food_type'] === 'veg' ? 'Y' : 'N'
        );

        $spice = $entry['category'] === 'Beverages' ? 'Mild' : 'Medium';
        $this->createFeatureVariantValue(
            $item,
            $features['single_choice'],
            $features['single_choice_variants'][$spice]
        );

        $multipleNames = $entry['food_type'] === 'veg'
            ? ['Gluten Free Option', 'Dairy Free Option']
            : ['Gluten Free Option'];
        foreach ($multipleNames as $name) {
            $this->createFeatureVariantValue(
                $item,
                $features['multiple_choice'],
                $features['multiple_choice_variants'][$name]
            );
        }

        $this->createFeatureValue(
            $item,
            $features['text'],
            'Prepared fresh; final garnish may vary.'
        );

        $minutes = match ($entry['category']) {
            'Beverages' => 15,
            'Appetizers' => 25,
            'Seafood Entrees' => 30,
            'Pasta' => 35,
            'Sandwiches & Wraps' => 20,
            default => 40,
        };
        $this->createFeatureValue(
            $item,
            $features['number'],
            (string) $minutes,
            (float) $minutes
        );
    }

    protected function createFeatureValue(FoodItem $item, FoodFeature $feature, string $value, ?float $valueInt = null): void
    {
        FoodFeatureValue::create([
            'feature_id' => $feature->feature_id,
            'food_item_id' => $item->getKey(),
            'variant_id' => null,
            'value' => $value,
            'value_int' => $valueInt,
            'lang_code' => 'en',
        ]);
    }

    protected function createFeatureVariantValue(FoodItem $item, FoodFeature $feature, FoodFeatureVariant $variant): void
    {
        FoodFeatureValue::create([
            'feature_id' => $feature->feature_id,
            'food_item_id' => $item->getKey(),
            'variant_id' => $variant->variant_id,
            'value' => '',
            'value_int' => null,
            'lang_code' => 'en',
        ]);
    }
}
