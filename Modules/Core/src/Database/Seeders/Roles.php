<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Roles extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
{
    // $exists = DB::table('roles')->where('id', 1)->exists();

    // if (! $exists) {
    //     DB::table('roles')->insert([
    //         'id' => 1,
    //         'name' => 'Administrator',
    //         'guard' => 'admin',
    //         'description' => 'Full access to all system features',
    //         'permission_type' => 'all',
    //         'permissions' => null,
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);
    // }
}
}
