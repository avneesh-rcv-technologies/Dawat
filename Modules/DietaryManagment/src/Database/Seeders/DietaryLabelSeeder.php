<?php

namespace Modules\DietaryManagment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\DietaryManagment\Models\DietaryLabel;

class DietaryLabelSeeder extends Seeder
{
    public function run(): void
    {
        $labels = [
            ['Halal', 'H', 'halal', 'shield-check', 'HALAL', 'Food prepared in accordance with Islamic dietary requirements, excluding pork, alcohol, and other prohibited ingredients.'],
            ['Kosher', 'K', 'kosher', 'sparkles', 'KOSHER', 'Food prepared in accordance with Jewish dietary laws, including permitted ingredients and the separation of meat and dairy.'],
            ['Vegetarian', 'V', 'vegetarian', 'leaf', 'VEGETARIAN', 'Food made without meat, poultry, fish, or seafood. May contain dairy products and eggs.'],
            ['Vegan', 'VG', 'vegan', 'leaf', 'VEGAN', 'Food made without animal-derived ingredients, including meat, poultry, fish, seafood, dairy, eggs, and honey.'],
            ['Gluten-Free', 'GF', 'gluten-free', 'wheat', 'GLUTEN_FREE', 'Food made without gluten-containing ingredients such as wheat, barley, and rye.'],
            ['Dairy-Free', 'DF', 'dairy-free', 'milk-off', 'DAIRY_FREE', 'Food made without milk or milk-derived ingredients, including cheese, butter, cream, whey, and casein.'],
        ];

        foreach ($labels as $index => [$name, $code, $slug, $icon, $value, $description]) {
            // Keep IDs and existing administrator edits when the seeder runs again.
            DietaryLabel::firstOrCreate(['backend_value' => $value], [
                'name' => $name, 'code' => $code, 'slug' => $slug, 'icon' => $icon,
                'description' => $description, 'display_order' => $index + 1, 'is_active' => true,
            ]);
        }
    }
    
}
