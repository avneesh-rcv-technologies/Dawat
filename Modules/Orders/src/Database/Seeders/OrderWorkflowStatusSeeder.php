<?php

namespace Modules\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Orders\Models\Status;
use Modules\Orders\Services\OrderCacheService;

class OrderWorkflowStatusSeeder extends Seeder
{
    public function run(): void
    {
        // Migrations fill missing definitions while preserving existing customizations.
        foreach (StatusesTableSeeder::statuses() as $status) {
            Status::firstOrCreate([
                'type_code' => $status['type_code'],
                'status_code' => $status['status_code'],
            ], [
                'name' => $status['name'], 'color' => $status['color'], 'new' => $status['new'],
            ]);
        }

        app(OrderCacheService::class)->forgetOrderStatuses('O');
    }
}
