<?php

namespace Modules\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Payments\Database\Seeders\CashPaymentNotificationSeeder;

class OrdersDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            OrderCommissionNotificationSeeder::class,
            StatusesTableSeeder::class,
            OrderNotificationSeeder::class,
            CashPaymentNotificationSeeder::class,
        ]);
    }
}
