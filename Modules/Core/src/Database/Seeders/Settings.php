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
            array('key' => 'general.timezone', 'value' => '"America\\/New_York"', 'created_at' => '2025-12-09 08:25:36', 'updated_at' => '2025-12-09 08:25:36'),
            array('key' => 'general.image_driver', 'value' => '"public"', 'created_at' => '2025-12-09 08:25:36', 'updated_at' => '2025-12-09 08:25:36'),
            array('key' => 'general.currency', 'value' => '"USD"', 'created_at' => '2025-12-09 08:25:36', 'updated_at' => '2025-12-09 08:25:36'),
            array('key' => 'general.language', 'value' => '"en-gb"', 'created_at' => '2025-12-09 08:25:36', 'updated_at' => '2025-12-09 08:25:36'),
            array('key' => 'general.company.name', 'value' => '"RCV Technologies"', 'created_at' => '2025-12-09 13:18:55', 'updated_at' => '2025-12-11 07:50:40'),
            array('key' => 'general.company.mail', 'value' => '"support@example.com"', 'created_at' => '2025-12-09 13:18:55', 'updated_at' => '2025-12-11 07:50:40'),
            array('key' => 'general.company.address_street', 'value' => '"14 Avenue Park"', 'created_at' => '2025-12-09 13:18:55', 'updated_at' => '2025-12-11 07:50:40'),
            array('key' => 'general.company.address_city', 'value' => '"Noida"', 'created_at' => '2025-12-09 13:18:55', 'updated_at' => '2025-12-11 07:50:40'),
            array('key' => 'general.company.address_state', 'value' => '"Uttar Pradesh"', 'created_at' => '2025-12-09 13:18:55', 'updated_at' => '2025-12-11 07:50:40'),
            array('key' => 'general.company.address_postal', 'value' => '"201301"', 'created_at' => '2025-12-09 13:18:55', 'updated_at' => '2025-12-11 07:50:40'),
            array('key' => 'general.company.country', 'value' => '"IN"', 'created_at' => '2025-12-09 13:18:55', 'updated_at' => '2025-12-11 07:50:40'),
            array('key' => 'general.editor.type', 'value' => '"tinymce"', 'created_at' => '2025-12-11 07:49:28', 'updated_at' => '2025-12-11 07:49:28'),
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
