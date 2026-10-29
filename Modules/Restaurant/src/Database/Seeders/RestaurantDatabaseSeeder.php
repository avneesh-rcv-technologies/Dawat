<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;

class RestaurantDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Add module seeders here, e.g. $this->call(ExampleSeeder::class);
        $this->call(RestaurantNotificationSeeder::class);
        $this->call(RestaurantOnboardingEmailVerificationSeeder::class);
        $this->call(RestaurantPasswordResetNotificationSeeder::class);
        $this->call(RestaurantBankingNotificationSeeder::class);
        $this->call(RestaurantTaxNotificationSeeder::class);
        $this->call(FinalOnboardingApprovedNotificationSeeder::class);
        $this->call(RestaurantResubmittedDetailsNotificationSeeder::class);
        $this->call(OnboardingRefillRequestedNotificationSeeder::class);
    }
}
