<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Files extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $files = array(
            array('file_name' => 'logo.png', 'original_name' => 'logo.png', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => '163237', 'path' => 'uploads/company_logo/0', 'is_image' => '1', 'width' => NULL, 'height' => NULL, 'created_at' => '2025-12-11 08:00:18', 'updated_at' => '2025-12-11 08:00:18'),
            array('file_name' => 'favicon.png', 'original_name' => 'logo.png', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => '163237', 'path' => 'uploads/company_favicon/0', 'is_image' => '1', 'width' => NULL, 'height' => NULL, 'created_at' => '2025-12-11 08:00:18', 'updated_at' => '2025-12-11 08:00:18')
        );

        /* `core-setup`.`file_links` */
        $file_links = array(
            array('file_id' => '1', 'object_type' => 'company_logo', 'object_id' => '0', 'type' => 'A', 'position' => '0', 'created_at' => '2025-12-11 08:00:18', 'updated_at' => '2025-12-11 08:00:18'),
            array('file_id' => '2', 'object_type' => 'company_favicon', 'object_id' => '0', 'type' => 'A', 'position' => '0', 'created_at' => '2025-12-11 08:00:18', 'updated_at' => '2025-12-11 08:00:18')
        );

        DB::table('files')->insert($files);
        DB::table('file_links')->insert($file_links);
    }
}
