<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Settings extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = array(
            array('key' => 'general.company.logo', 'value' => '"uploads\\/company\\/logo.png"', 'created_at' => '2025-12-08 06:21:53', 'updated_at' => '2025-12-09 13:18:55'),
            array('key' => 'general.company.favicon', 'value' => '"uploads\\/company\\/favicon.png"', 'created_at' => '2025-12-08 06:21:53', 'updated_at' => '2025-12-09 13:18:55'),
            array('key' => 'general.company.name', 'value' => '"Dawat"', 'created_at' => '2025-12-08 06:21:53', 'updated_at' => '2025-12-09 13:18:55'),
            array('key' => 'general.company.email', 'value' => '"info@dawateats.com"', 'created_at' => '2025-12-08 06:21:53', 'updated_at' => '2025-12-09 13:18:55'),
            array('key' => 'general.company.phone', 'value' => '"+1 123-456-7890"', 'created_at' => '2025-12-08 06:21:53', 'updated_at' => '2025-12-09 13:18:55'),
            array('key' => 'general.company.address', 'value' => '"123 Main St, City, Country"', 'created_at' => '2025-12-08 06:21:53', 'updated_at' => '2025-12-09 13:18:55'),
            array('key' => 'general.default_role', 'value' => '2', 'created_at' => '2025-12-08 06:21:53', 'updated_at' => '2025-12-09 13:18:55'),

           );

        // Insert or update only the keys listed above; other settings keys are left untouched.
        $now = now();
        $rows = array_map(fn (array $setting) => [
            'key' => $setting['key'],
            'value' => $setting['value'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $settings);

        DB::table('settings')->upsert($rows, ['key'], ['value', 'updated_at']);
    }
}
