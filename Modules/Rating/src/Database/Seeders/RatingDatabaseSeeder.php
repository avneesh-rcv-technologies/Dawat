<?php

namespace Modules\Rating\Database\Seeders;

use Illuminate\Database\Seeder;

class RatingDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Seeding Rating module data...');
        
        // Original rating seeder
        $this->call(RatingSeeder::class);
        
        // Food-specific rating seeders
        $this->call(FoodRatingSeeder::class);
        $this->call(FoodReviewSeeder::class);
        $this->call(RestaurantRatingSeeder::class);
        
        $this->command->info('Rating module seeding completed!');
    }
}
