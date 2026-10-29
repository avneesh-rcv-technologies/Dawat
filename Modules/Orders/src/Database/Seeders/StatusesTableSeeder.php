<?php

namespace Modules\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'type_code' => 'O',
                'status_code' => 'O',
                'name' => 'New',
                'color' => 'blue',
                'new' => 1
            ],
            [
                'type_code' => 'O',
                'status_code' => 'N',
                'name' => 'Incomplete',
                'color' => 'yellow',
                'new' => 0
            ],
            [
                'type_code' => 'O',
                'status_code' => 'P',
                'name' => 'Pending',
                'color' => 'red',
                'new' => 0
            ],
            [
                'type_code' => 'O',
                'status_code' => 'H',
                'name' => 'Processing',
                'color' => 'yellow',
                'new' => 0
            ],
            [
                'type_code' => 'O',
                'status_code' => 'Z',
                'name' => 'Completed',
                'color' => 'green',
                'new' => 0
            ],
            [
                'type_code' => 'O',
                'status_code' => 'F',
                'name' => 'Failed',
                'color' => 'red',
                'new' => 0
            ],
            [
                'type_code' => 'O',
                'status_code' => 'R',
                'name' => 'Refunded',
                'color' => 'purple',
                'new' => 0
            ],
            [
                'type_code' => 'O',
                'status_code' => 'C',
                'name' => 'Cancelled',
                'color' => 'red',
                'new' => 0
            ],
        ];

        // Add timestamps
        $now = now();
        foreach ($statuses as &$status) {
            $status['created_at'] = $now;
            $status['updated_at'] = $now;
        }

        DB::table('statuses')->upsert(
            $statuses,
            ['type_code', 'status_code'], // Unique keys
            ['name', 'color', 'new', 'updated_at'] // Columns to update
        );

        $this->command->info('Order statuses seeded successfully!');
    }
}