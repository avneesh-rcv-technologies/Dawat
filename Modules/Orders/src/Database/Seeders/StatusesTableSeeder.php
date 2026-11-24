<?php

namespace Modules\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Status;
use Modules\Orders\Services\OrderCacheService;

class StatusesTableSeeder extends Seeder
{
    /**
     * Canonical definitions shared by fresh installs and existing installations.
     */
    public static function statuses(): array
    {
        return array_map(fn (array $definition) => [
            'type_code' => 'O',
            'status_code' => $definition[0],
            'name' => $definition[1]->label(),
            'color' => $definition[2],
            'new' => $definition[0] === Order::STATUS_NEW,
        ], [
            [Order::STATUS_NEW, OrderStatus::RECEIVED, '#2563EB'],
            [Order::STATUS_PENDING, OrderStatus::PENDING, '#D97706'],
            [Order::STATUS_ACCEPTED, OrderStatus::CONFIRMED, '#0284C7'],
            [Order::STATUS_PROCESSING, OrderStatus::PREPARING, '#7C3AED'],
            [Order::STATUS_READY, OrderStatus::READY, '#059669'],
            [Order::STATUS_OUT_FOR_DELIVERY, OrderStatus::OUT_FOR_DELIVERY, '#0891B2'],
            [Order::STATUS_DELIVERED, OrderStatus::DELIVERED, '#16A34A'],
            [Order::STATUS_CANCELLED, OrderStatus::CANCELLED, '#DC2626'],
            [Order::STATUS_FAILED, OrderStatus::FAILED, '#B91C1C'],
        ]);
    }

    public function run(): void
    {
        DB::transaction(function () {
            foreach (self::statuses() as $status) {
                Status::updateOrCreate([
                    'type_code' => $status['type_code'],
                    'status_code' => $status['status_code'],
                ], [
                    'name' => $status['name'],
                    'color' => $status['color'],
                    'new' => $status['new'],
                ]);
            }
        });

        app(OrderCacheService::class)->forgetOrderStatuses('O');

        $this->command?->info('All '.count(self::statuses()).' order statuses refreshed successfully.');
    }
}
