<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;

class RestaurantApprovalSummaryNotificationSeeder extends Seeder
{
    public function run(): void
    {
        (new RestaurantNotificationSeeder())->seedApprovalEmailTemplate();
    }
}
