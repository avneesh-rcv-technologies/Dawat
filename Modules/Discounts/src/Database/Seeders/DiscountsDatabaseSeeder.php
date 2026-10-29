<?php

namespace Modules\Discounts\Database\Seeders;

use Illuminate\Database\Seeder;

class DiscountsDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DiscountsSeeder::class,
        ]);
    }
}
