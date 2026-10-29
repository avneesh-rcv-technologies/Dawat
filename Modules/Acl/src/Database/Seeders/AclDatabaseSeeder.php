<?php

namespace Modules\Acl\Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AclDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(AdminSeeder::class);
        $this->call(CustomerNotificationSeeder::class);
        $this->call(CustomerPasswordResetNotificationSeeder::class);
        $this->call(AdminEmailVerificationSeeder::class);
        $this->call(AdminPasswordResetNotificationSeeder::class);
    }
}
