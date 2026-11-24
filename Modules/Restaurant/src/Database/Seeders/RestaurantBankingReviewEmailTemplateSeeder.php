<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;

class RestaurantBankingReviewEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $seeder = new RestaurantBankingNotificationSeeder();
        $seeder->seedReviewEmailTemplate('approved');
        $seeder->seedReviewEmailTemplate('rejected');
    }
}
