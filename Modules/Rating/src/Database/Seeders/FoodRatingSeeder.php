<?php

namespace Modules\Rating\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Rating\Models\Rating;
use Modules\FoodManagements\Models\FoodItem;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodEvent;
use App\Models\User;

class FoodRatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Food-specific reviews for different ratings
        $foodReviews = [
            5 => [
                'Absolutely delicious! The flavors were perfectly balanced and the presentation was stunning.',
                'Best meal I\'ve had in years! Fresh ingredients and amazing taste. Highly recommended!',
                'Outstanding food quality! Every bite was a delight. Will definitely order again.',
                'Perfect! The chef really knows how to create magic with ingredients. 5 stars!',
                'Exceptional taste and quality. This is exactly what I was craving. Amazing!',
                'Incredible flavors! The spices were perfectly balanced and the portion was generous.',
                'Absolutely fantastic! Fresh, flavorful, and beautifully prepared. Top-notch quality!',
                'Divine taste! This exceeded all my expectations. Best food delivery experience ever.',
                'Perfect meal! Everything was cooked to perfection and arrived hot and fresh.',
                'Amazing! The quality and taste are unmatched. This is my new favorite dish.',
            ],
            4 => [
                'Really good food! Just a tiny bit too salty for my taste, but overall excellent.',
                'Great taste and quality. Delivery was a bit slow but the food made up for it.',
                'Very satisfied! Good portion size and flavors. Would order again.',
                'Tasty and well-prepared. The only downside was it could have been a bit warmer.',
                'Good food quality. Fresh ingredients and nice presentation. Recommended!',
                'Pretty good! The flavors were nice, just wished there was a bit more spice.',
                'Solid choice! Good taste and reasonable price. Will try other items from the menu.',
                'Nice meal! Everything was fresh and tasty. Delivery was prompt too.',
                'Good quality food. The portion was adequate and taste was satisfying.',
                'Enjoyable meal! Good flavors and fresh ingredients. Worth the price.',
            ],
            3 => [
                'It was okay. Nothing special but not bad either. Average taste and quality.',
                'Decent food. Met my expectations but didn\'t exceed them. Fair for the price.',
                'Average meal. The taste was fine but nothing to write home about.',
                'It\'s alright. Good enough for a quick meal but not exceptional.',
                'Fair quality. The food was edible and filling but lacked that wow factor.',
                'Okay taste. Not the best I\'ve had but not the worst either. Average experience.',
                'Decent enough. The food was fresh but the flavors were a bit bland.',
                'It was fine. Good for a regular meal but nothing extraordinary.',
                'Average food quality. Portion was good but taste could be improved.',
                'Acceptable. The food was okay but I\'ve had better elsewhere.',
            ],
            2 => [
                'Disappointing. The food was cold when it arrived and lacked flavor.',
                'Not great. The taste was bland and the portion was smaller than expected.',
                'Below average. The food quality didn\'t match the price. Not satisfied.',
                'Poor experience. The food was overcooked and dry. Expected better.',
                'Not impressed. The flavors were off and the presentation was sloppy.',
                'Subpar quality. The food tasted stale and wasn\'t worth the money.',
                'Disappointing meal. The ingredients didn\'t seem fresh and taste was poor.',
                'Not good. The food was lukewarm and the seasoning was inadequate.',
                'Poor quality. The dish lacked flavor and the texture was unpleasant.',
                'Below expectations. The food was mediocre and overpriced.',
            ],
            1 => [
                'Terrible! The food was spoiled and inedible. Complete waste of money.',
                'Awful experience! The food was cold, stale, and tasted horrible.',
                'Disgusting! I couldn\'t even finish the meal. Poor quality ingredients.',
                'Worst food ever! Everything was wrong - taste, temperature, and quality.',
                'Horrible! The food was clearly old and had a bad smell. Unacceptable!',
                'Terrible quality! The food was burnt and completely inedible.',
                'Awful! The meal was a disaster. Poor preparation and terrible taste.',
                'Disgusting food! I had to throw it away. Complete waste of money.',
                'Horrible experience! The food was spoiled and made me feel sick.',
                'Worst meal ever! Everything was wrong. I want my money back.',
            ],
        ];

        $guestNames = [
            'Alex Johnson', 'Maria Garcia', 'David Chen', 'Sarah Williams',
            'Michael Brown', 'Emma Davis', 'James Wilson', 'Lisa Anderson',
            'Robert Taylor', 'Jennifer Martinez', 'Christopher Lee', 'Amanda White',
            'Daniel Rodriguez', 'Jessica Thompson', 'Matthew Jackson', 'Ashley Harris',
            'Andrew Clark', 'Stephanie Lewis', 'Joshua Walker', 'Michelle Hall',
            'Kevin Young', 'Nicole King', 'Brian Wright', 'Samantha Green',
            'Ryan Adams', 'Rachel Baker', 'Tyler Nelson', 'Lauren Carter',
            'Brandon Mitchell', 'Megan Roberts', 'Justin Turner', 'Kimberly Phillips',
        ];

        $guestEmails = [
            'alex.j@foodie.com', 'maria.g@taste.com', 'david.c@flavor.com', 'sarah.w@delish.com',
            'michael.b@yummy.com', 'emma.d@cuisine.com', 'james.w@gourmet.com', 'lisa.a@foodlover.com',
            'robert.t@tasty.com', 'jennifer.m@savory.com', 'christopher.l@spice.com', 'amanda.w@fresh.com',
            'daniel.r@hungry.com', 'jessica.t@bite.com', 'matthew.j@meal.com', 'ashley.h@dish.com',
            'andrew.c@chef.com', 'stephanie.l@cook.com', 'joshua.w@eat.com', 'michelle.h@food.com',
            'kevin.y@dine.com', 'nicole.k@feast.com', 'brian.w@menu.com', 'samantha.g@recipe.com',
            'ryan.a@kitchen.com', 'rachel.b@plate.com', 'tyler.n@serving.com', 'lauren.c@portion.com',
            'brandon.m@flavor.com', 'megan.r@taste.com', 'justin.t@spicy.com', 'kimberly.p@sweet.com',
        ];

        // Get existing users
        $users = User::limit(10)->pluck('id')->toArray();

        // Get food items, categories, and events
        $foodItems = FoodItem::limit(20)->get();
        $foodCategories = FoodCategory::limit(10)->get();
        $foodEvents = FoodEvent::limit(5)->get();

        $this->command->info('Creating ratings for Food Items...');
        $this->createRatingsForModel($foodItems, $foodReviews, $guestNames, $guestEmails, $users, 'FoodItem');

        $this->command->info('Creating ratings for Food Categories...');
        $this->createRatingsForModel($foodCategories, $foodReviews, $guestNames, $guestEmails, $users, 'FoodCategory');

        $this->command->info('Creating ratings for Food Events...');
        $this->createRatingsForModel($foodEvents, $foodReviews, $guestNames, $guestEmails, $users, 'FoodEvent');

        $this->command->info('Food rating seeders completed successfully!');
    }

    /**
     * Create ratings for a specific model type
     */
    private function createRatingsForModel($models, $foodReviews, $guestNames, $guestEmails, $users, $modelType)
    {
        foreach ($models as $model) {
            // Create 3-8 ratings per item
            $ratingsCount = rand(3, 8);
            
            for ($i = 0; $i < $ratingsCount; $i++) {
                // Generate rating (weighted towards higher ratings)
                $rating = $this->generateWeightedRating();
                $reviewText = $foodReviews[$rating][array_rand($foodReviews[$rating])];
                
                // 60% chance of being a user review, 40% guest
                $isUser = !empty($users) && rand(1, 10) <= 6;
                
                $data = [
                    'ratable_type' => 'Modules\\FoodManagements\\Models\\' . $modelType,
                    'ratable_id' => $model->id,
                    'rating' => $rating,
                    'review' => $reviewText,
                    'ip_address' => $this->generateRandomIP(),
                    'is_approved' => rand(1, 10) > 1, // 90% approved
                    'status' => rand(1, 10) > 1, // 90% active
                    'created_at' => now()->subDays(rand(0, 180)),
                    'updated_at' => now()->subDays(rand(0, 30)),
                ];

                if ($isUser) {
                    // User review
                    $data['user_id'] = $users[array_rand($users)];
                    $data['guest_name'] = null;
                    $data['guest_email'] = null;
                } else {
                    // Guest review
                    $index = array_rand($guestNames);
                    $data['user_id'] = null;
                    $data['guest_name'] = $guestNames[$index];
                    $data['guest_email'] = $guestEmails[$index];
                }

                Rating::create($data);
            }
        }
    }

    /**
     * Generate weighted rating (more likely to be higher)
     * Distribution: 1★(5%), 2★(10%), 3★(15%), 4★(35%), 5★(35%)
     */
    private function generateWeightedRating(): int
    {
        $rand = rand(1, 100);
        
        if ($rand <= 5) return 1;
        if ($rand <= 15) return 2;
        if ($rand <= 30) return 3;
        if ($rand <= 65) return 4;
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