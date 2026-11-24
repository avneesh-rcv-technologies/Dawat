<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DietaryLabelSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(\Modules\DietaryManagment\Database\Seeders\DietaryLabelSeeder::class);
    }
}
