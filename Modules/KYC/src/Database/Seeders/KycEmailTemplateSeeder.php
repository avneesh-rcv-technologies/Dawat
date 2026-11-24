<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;

class KycEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        (new KycStatusNotificationSeeder())->seedEmailTemplate();
        (new KycMessageNotificationSeeder())->seedEmailTemplate();
        (new KycDocumentSubmissionNotificationSeeder())->seedEmailTemplate();
        $this->call(KycDocumentReSubmissionNotificationSeeder::class);
    }
}
