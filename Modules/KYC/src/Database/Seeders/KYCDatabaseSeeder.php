<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;

class KYCDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OwnerIdentityDocumentSeeder::class,
            KycTypeSeeder::class,
            KycNotificationSeeder::class,
            KycStatusNotificationSeeder::class,
            KycMessageNotificationSeeder::class,
            KycDocumentSubmissionNotificationSeeder::class,
        ]);
    }
}
