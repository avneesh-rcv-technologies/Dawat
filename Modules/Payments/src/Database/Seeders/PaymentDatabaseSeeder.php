<?php

namespace Modules\Payments\Database\Seeders;

use Illuminate\Database\Seeder;

class PaymentDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([PaymentNotificationSeeder::class, CashPaymentNotificationSeeder::class, RefundNotificationSeeder::class]);
    }
}
