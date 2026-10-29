<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Acl\Models\Customer;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create dummy customers for testing API authentication
        $customers = [
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@example.com',
                'phone' => '+1234567893',
                'password' => Hash::make('password123'),
                'status' => 1, // 1 = active, 0 = inactive
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'phone' => '+1234567894',
                'password' => Hash::make('password123'),
                'status' => 1, // 1 = active, 0 = inactive
            ],
            [
                'name' => 'Carol Davis',
                'email' => 'carol@example.com',
                'phone' => '+1234567895',
                'password' => Hash::make('password123'),
                'status' => 1, // 1 = active, 0 = inactive
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::firstOrCreate(
                ['email' => $customerData['email']],
                $customerData
            );
        }

        $this->command->info('Created 3 dummy customers for testing API authentication');
        $this->command->info('Login credentials:');
        $this->command->info('- alice@example.com / password123');
        $this->command->info('- bob@example.com / password123');
        $this->command->info('- carol@example.com / password123');
    }
}