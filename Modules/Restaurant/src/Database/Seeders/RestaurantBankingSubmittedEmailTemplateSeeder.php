<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;

class RestaurantBankingSubmittedEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        (new RestaurantBankingNotificationSeeder())->seedSubmittedEmailTemplate();
    }
}
