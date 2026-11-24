<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;

class RestaurantTaxEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $seeder = new RestaurantTaxNotificationSeeder();
        foreach (['submitted', 'resubmitted', 'approved', 'rejected'] as $action) {
            $seeder->seedEmailTemplate($action);
        }
    }
}
