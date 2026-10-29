<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Restaurant\Models\Restaurant;

class RestaurantCancellationPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Restaurant::query()->update([
            'allow_order_cancellation' => true,
            'cancellation_time_limit' => 15,
            'cancellable_statuses' => json_encode(['P', 'O', 'H']), // Pending, New, Processing
        ]);

        $this->command->info('Restaurant cancellation policies updated successfully!');
    }
}
