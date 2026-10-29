<?php

namespace Modules\Rating\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Rating\Models\Rating;
use Modules\FoodManagements\Models\FoodItem;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodEvent;
use App\Models\User;

class RestaurantRatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Restaurant-specific reviews with focus on service, delivery, and food quality
        $restaurantReviews = [
            'delivery' => [
                5 => [
                    'Lightning fast delivery! Food arrived hot and fresh within 20 minutes. Excellent packaging kept everything perfect.',
                    'Outstanding delivery service! Driver was polite and food was delivered exactly on time. Everything was still steaming hot.',
                    'Perfect delivery experience! Food arrived earlier than expected and was packaged beautifully. Temperature was perfect.',
                    'Exceptional delivery! The food was hot, fresh, and arrived in record time. Best delivery service in the area.',
                ],
                4 => [
                    'Great delivery! Food arrived hot and on time. Just a small issue with finding the address but overall excellent.',
                    'Good delivery service. Food was warm and arrived within the estimated time. Driver was friendly.',
                    'Solid delivery experience. Food was well-packaged and arrived in good condition. Slightly delayed but worth the wait.',
                ],
                3 => [
                    'Average delivery. Food arrived lukewarm and took longer than expected. Packaging was okay.',
                    'Decent delivery service. Food was edible but not as hot as I\'d like. Timing was average.',
                ],
                2 => [
                    'Poor delivery experience. Food was cold and took much longer than promised. Packaging was inadequate.',
                    'Disappointing delivery. Food arrived cold and the driver seemed rushed. Not satisfied.',
                ],
                1 => [
                    'Terrible delivery! Food was ice cold and took over an hour. Driver was rude and unprofessional.',
                    'Awful delivery service! Food was spoiled when it arrived and packaging was damaged.',
                ],
            ],
            'service' => [
                5 => [
                    'Incredible customer service! Staff was friendly, helpful, and went above and beyond. Felt truly valued as a customer.',
                    'Outstanding service! Every interaction was pleasant and professional. They really care about customer satisfaction.',
                    'Perfect service experience! Staff was knowledgeable, patient, and made excellent recommendations.',
                    'Exceptional customer care! Quick responses, helpful staff, and they resolved my issue immediately.',
                ],
                4 => [
                    'Great service! Staff was friendly and helpful. Just a minor wait time but overall very good experience.',
                    'Good customer service. Staff was polite and accommodating. Would definitely return.',
                    'Solid service experience. Staff was professional and the service was efficient.',
                ],
                3 => [
                    'Average service. Staff was okay but nothing special. Got the job done but lacked enthusiasm.',
                    'Decent service. No major complaints but nothing stood out as exceptional.',
                ],
                2 => [
                    'Poor service. Staff seemed disinterested and wasn\'t very helpful. Expected better.',
                    'Below average service. Long wait times and staff wasn\'t very attentive.',
                ],
                1 => [
                    'Terrible service! Staff was rude and unprofessional. Worst customer service experience ever.',
                    'Awful service! No one seemed to care about helping customers. Very disappointing.',
                ],
            ],
            'quality' => [
                5 => [
                    'Exceptional food quality! Every ingredient was fresh, flavors were perfectly balanced, and presentation was restaurant-grade.',
                    'Outstanding quality! You can taste the freshness in every bite. Clearly made with premium ingredients.',
                    'Perfect quality! Food was prepared with care and attention to detail. Best quality I\'ve experienced.',
                    'Amazing quality! Fresh ingredients, perfect cooking, and beautiful presentation. Worth every penny.',
                ],
                4 => [
                    'Great quality food! Fresh ingredients and good preparation. Just a minor seasoning issue but overall excellent.',
                    'Good quality! Food was fresh and well-prepared. Definitely above average quality.',
                    'Solid quality! Good ingredients and proper cooking. Would order again.',
                ],
                3 => [
                    'Average quality. Food was okay but nothing special. Met basic expectations.',
                    'Decent quality. Food was edible and reasonably fresh but not outstanding.',
                ],
                2 => [
                    'Poor quality. Food seemed to be sitting for a while and wasn\'t very fresh.',
                    'Below average quality. Ingredients didn\'t seem fresh and preparation was lacking.',
                ],
                1 => [
                    'Terrible quality! Food was clearly old and ingredients were not fresh. Unacceptable.',
                    'Awful quality! Food was spoiled and inedible. Complete waste of money.',
                ],
            ],
            'value' => [
                5 => [
                    'Excellent value for money! Generous portions, high quality, and reasonable prices. Best deal in town.',
                    'Outstanding value! Great food at fair prices with large portions. Definitely worth every dollar.',
                    'Perfect value proposition! Quality and quantity exceed the price point. Highly recommended.',
                    'Amazing value! Premium quality at reasonable prices. You get more than what you pay for.',
                ],
                4 => [
                    'Good value! Fair prices for the quality and portion size. Would order again.',
                    'Great value for money! Reasonable prices and good quality food.',
                    'Solid value! Good balance between price, quality, and portion size.',
                ],
                3 => [
                    'Average value. Prices are fair but nothing exceptional for the money.',
                    'Decent value. You get what you pay for, nothing more, nothing less.',
                ],
                2 => [
                    'Poor value. Overpriced for the quality and portion size received.',
                    'Below average value. Expected more for the price paid.',
                ],
                1 => [
                    'Terrible value! Way overpriced for the poor quality received. Not worth it.',
                    'Awful value! Extremely expensive for subpar food. Complete rip-off.',
                ],
            ],
        ];

        // Professional reviewer names
        $professionalReviewers = [
            'Restaurant Critic', 'Food Inspector', 'Culinary Expert', 'Dining Reviewer',
            'Food Quality Analyst', 'Service Evaluator', 'Hospitality Expert', 'Food Blogger Pro',
            'Cuisine Specialist', 'Dining Experience Expert', 'Food Service Reviewer', 'Quality Assessor',
            'Restaurant Evaluator', 'Food Industry Expert', 'Dining Consultant', 'Culinary Reviewer',
        ];

        $professionalEmails = [
            'critic@restaurant-review.com', 'inspector@food-quality.com', 'expert@culinary-pro.com',
            'reviewer@dining-guide.com', 'analyst@food-quality.org', 'evaluator@service-review.com',
            'expert@hospitality-pro.com', 'blogger@food-pro.com', 'specialist@cuisine-expert.com',
            'expert@dining-experience.com', 'reviewer@food-service.com', 'assessor@quality-check.com',
            'evaluator@restaurant-pro.com', 'expert@food-industry.com', 'consultant@dining-pro.com',
            'reviewer@culinary-expert.com',
        ];

        // Get existing users
        $users = User::limit(12)->pluck('id')->toArray();

        // Get food models
        $foodItems = FoodItem::limit(25)->get();
        $foodCategories = FoodCategory::limit(12)->get();
        $foodEvents = FoodEvent::limit(6)->get();

        $this->command->info('Creating restaurant-focused ratings for Food Items...');
        $this->createRestaurantReviews($foodItems, $restaurantReviews, $professionalReviewers, $professionalEmails, $users, 'FoodItem');

        $this->command->info('Creating restaurant-focused ratings for Food Categories...');
        $this->createRestaurantReviews($foodCategories, $restaurantReviews, $professionalReviewers, $professionalEmails, $users, 'FoodCategory');

        $this->command->info('Creating restaurant-focused ratings for Food Events...');
        $this->createRestaurantReviews($foodEvents, $restaurantReviews, $professionalReviewers, $professionalEmails, $users, 'FoodEvent');

        $this->command->info('Restaurant rating seeders completed successfully!');
    }

    /**
     * Create restaurant-focused reviews
     */
    private function createRestaurantReviews($models, $restaurantReviews, $professionalReviewers, $professionalEmails, $users, $modelType)
    {
        foreach ($models as $model) {
            // Create 2-6 restaurant reviews per item
            $reviewsCount = rand(2, 6);
            
            for ($i = 0; $i < $reviewsCount; $i++) {
                // Generate rating with restaurant-focused distribution
                $rating = $this->generateRestaurantRating();
                
                // Select review category (delivery, service, quality, value)
                $reviewCategories = array_keys($restaurantReviews);
                $selectedCategory = $reviewCategories[array_rand($reviewCategories)];
                
                $reviewText = $restaurantReviews[$selectedCategory][$rating][array_rand($restaurantReviews[$selectedCategory][$rating])];
                
                // 50% chance of being a user review, 50% professional reviewer
                $isUser = !empty($users) && rand(1, 10) <= 5;
                
                $data = [
                    'ratable_type' => 'Modules\\FoodManagements\\Models\\' . $modelType,
                    'ratable_id' => $model->id,
                    'rating' => $rating,
                    'review' => $reviewText,
                    'ip_address' => $this->generateRandomIP(),
                    'is_approved' => rand(1, 10) > 1, // 90% approved
                    'status' => rand(1, 10) > 1, // 90% active
                    'created_at' => now()->subDays(rand(0, 90)),
                    'updated_at' => now()->subDays(rand(0, 15)),
                ];

                if ($isUser) {
                    // User review
                    $data['user_id'] = $users[array_rand($users)];
                    $data['guest_name'] = null;
                    $data['guest_email'] = null;
                } else {
                    // Professional reviewer
                    $index = array_rand($professionalReviewers);
                    $data['user_id'] = null;
                    $data['guest_name'] = $professionalReviewers[$index];
                    $data['guest_email'] = $professionalEmails[$index];
                }

                Rating::create($data);
            }
        }
    }

    /**
     * Generate restaurant-focused rating distribution
     * Distribution: 1★(5%), 2★(10%), 3★(20%), 4★(35%), 5★(30%)
     */
    private function generateRestaurantRating(): int
    {
        $rand = rand(1, 100);
        
        if ($rand <= 5) return 1;
        if ($rand <= 15) return 2;
        if ($rand <= 35) return 3;
        if ($rand <= 70) return 4;
        return 5;
    }

    /**
     * Generate a random IP address
     */
    private function generateRandomIP(): string
    {
        return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
    }
}