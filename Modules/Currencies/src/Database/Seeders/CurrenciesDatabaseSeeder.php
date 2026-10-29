<?php

namespace Modules\Currencies\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Currencies\Database\Seeders\CurrencySeeder;

class CurrenciesDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Add module seeders here, e.g. 
        $this->call(CurrencySeeder::class);
    }
}
