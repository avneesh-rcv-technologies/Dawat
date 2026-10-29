<?php

namespace Modules\Rating\Database\Seeders;

use Illuminate\Database\Seeder;

class FoodOnlySeeder extends Seeder
{
    /**
     * Run only the food-related rating seeders.
     */
    public function run(): void
    {
        $this->command->info('Seeding food-specific rating data...');
        
        // Food-specific rating seeders only
        $this->call(FoodRatingSeeder::class);
        $this->call(FoodReviewSeeder::class);
        $this->call(RestaurantRatingSeeder::class);
        
        $this->command->info('Food rating seeders completed successfully!');
        $this->command->info('');
        $this->command->info('Summary:');
        $this->command->info('- FoodRatingSeeder: General food ratings with weighted distribution');
        $this->command->info('- FoodReviewSeeder: Detailed cuisine-specific reviews');
        $this->command->info('- RestaurantRatingSeeder: Service, delivery, quality & value reviews');
        $this->command->info('');
        $this->command->info('Total ratings created for FoodItems, FoodCategories, and FoodEvents');
    }
}