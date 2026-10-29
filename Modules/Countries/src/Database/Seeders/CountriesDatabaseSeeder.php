<?php

namespace Modules\Countries\Database\Seeders;

use Illuminate\Database\Seeder;

class CountriesDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([CountriesTableSeeder::class]);
    }
}
