<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;

class RestaurantBankingResubmittedEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        (new RestaurantResubmittedDetailsNotificationSeeder())->seedBankingEmailTemplate();
    }
}
