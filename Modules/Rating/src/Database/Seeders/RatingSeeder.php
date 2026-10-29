<?php

namespace Modules\Rating\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Rating\Models\Rating;
use App\Models\User;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [
            ['rating' => 5, 'review' => 'Excellent service! Highly recommended. Everything was perfect from start to finish.'],
            ['rating' => 5, 'review' => 'Outstanding quality and great customer support. Will definitely use again!'],
            ['rating' => 4, 'review' => 'Very good experience overall. Just a minor delay in delivery but worth the wait.'],
            ['rating' => 4, 'review' => 'Great product! Meets all my expectations. Good value for money.'],
            ['rating' => 5, 'review' => 'Absolutely love it! Best purchase I have made this year. Cannot recommend enough.'],
            ['rating' => 3, 'review' => 'It\'s okay, meets basic requirements but nothing special. Could be improved.'],
            ['rating' => 4, 'review' => 'Pretty satisfied with the results. Would recommend to friends and family.'],
            ['rating' => 5, 'review' => 'Perfect in every way! Exceeded my expectations completely. Amazing!'],
            ['rating' => 2, 'review' => 'Not what I expected. Had some issues with quality. Needs improvement.'],
            ['rating' => 4, 'review' => 'Good quality and fast delivery. Very professional service.'],
            ['rating' => 5, 'review' => 'Amazing! Top-notch quality and excellent attention to detail.'],
            ['rating' => 3, 'review' => 'Average experience. Does the job but nothing extraordinary.'],
            ['rating' => 4, 'review' => 'Really happy with this! Great communication and quick turnaround.'],
            ['rating' => 5, 'review' => 'Fantastic! Could not ask for better. Will be a repeat customer.'],
            ['rating' => 1, 'review' => 'Very disappointed. Did not meet my expectations at all. Poor quality.'],
            ['rating' => 4, 'review' => 'Solid choice! Very reliable and good value. Recommended.'],
            ['rating' => 5, 'review' => 'Exceptional service! Professional, friendly, and efficient.'],
            ['rating' => 3, 'review' => 'Decent but has room for improvement. Not bad for the price.'],
            ['rating' => 5, 'review' => 'Absolutely brilliant! Best in class. Highly recommended to everyone.'],
            ['rating' => 4, 'review' => 'Very pleased with the outcome. Good quality and timely delivery.'],
        ];

        $guestNames = [
            'John Doe', 'Jane Smith', 'Michael Johnson', 'Emily Brown',
            'David Wilson', 'Sarah Davis', 'James Miller', 'Emma Garcia',
            'Robert Martinez', 'Olivia Rodriguez', 'William Anderson', 'Sophia Taylor',
            'Richard Thomas', 'Isabella Moore', 'Joseph Jackson', 'Mia White',
            'Charles Harris', 'Charlotte Martin', 'Christopher Thompson', 'Amelia Lewis'
        ];

        $guestEmails = [
            'john.doe@example.com', 'jane.smith@example.com', 'michael.j@example.com', 'emily.b@example.com',
            'david.w@example.com', 'sarah.d@example.com', 'james.m@example.com', 'emma.g@example.com',
            'robert.m@example.com', 'olivia.r@example.com', 'william.a@example.com', 'sophia.t@example.com',
            'richard.t@example.com', 'isabella.m@example.com', 'joseph.j@example.com', 'mia.w@example.com',
            'charles.h@example.com', 'charlotte.m@example.com', 'christopher.t@example.com', 'amelia.l@example.com'
        ];

        // Get existing users (if any)
        $users = User::limit(5)->pluck('id')->toArray();

        // Create 50 dummy ratings
        for ($i = 0; $i < 50; $i++) {
            $reviewData = $reviews[array_rand($reviews)];
            $isUser = !empty($users) && rand(0, 1); // 50% chance of being a user review

            $data = [
                'ratable_type' => 'App\Models\User', // Change this to your actual model
                'ratable_id' => rand(1, 10),
                'rating' => $reviewData['rating'],
                'review' => $reviewData['review'],
                'ip_address' => $this->generateRandomIP(),
                'is_approved' => rand(0, 10) > 2, // 80% approved
                'status' => rand(0, 10) > 1, // 90% active
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ];

            if ($isUser) {
                // User review
                $data['user_id'] = $users[array_rand($users)];
                $data['guest_name'] = null;
                $data['guest_email'] = null;
            } else {
                // Guest review
                $index = $i % count($guestNames);
                $data['user_id'] = null;
                $data['guest_name'] = $guestNames[$index];
                $data['guest_email'] = $guestEmails[$index];
            }

            Rating::create($data);
        }

        $this->command->info('Created 50 dummy ratings successfully!');
    }

    /**
     * Generate a random IP address
     */
    private function generateRandomIP(): string
    {
        return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
    }
}
