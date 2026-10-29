<?php

namespace Modules\Subscriptions\Database\Seeders;

use Illuminate\Database\Seeder;

class SubscriptionsDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([PlansSeeder::class, SubscriptionsSeeder::class, FeatureSeeder::class]);
    }
}
