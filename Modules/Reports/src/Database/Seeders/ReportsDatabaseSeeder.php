<?php

namespace Modules\Reports\Database\Seeders;

use Illuminate\Database\Seeder;

class ReportsDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Business Analytics (Subscriptions, Bookings, Events)
            // BusinessAnalyticsReportsSeeder::class,
            
            // Order Analytics
            OrderAnalyticsReportsSeeder::class,
            
            // Customer Analytics
            CustomerAnalyticsReportsSeeder::class,
            
            // Restaurant/Vendor Analytics
            RestaurantAnalyticsReportsSeeder::class,
       ]);
    }
}
