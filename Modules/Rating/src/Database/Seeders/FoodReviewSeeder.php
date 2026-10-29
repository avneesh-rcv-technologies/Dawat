<?php

namespace Modules\Rating\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Rating\Models\Rating;
use Modules\FoodManagements\Models\FoodItem;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodEvent;
use App\Models\User;

class FoodReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Detailed food reviews categorized by cuisine type and rating
        $detailedFoodReviews = [
            'pizza' => [
                5 => [
                    'Perfect pizza! The crust was crispy on the outside and chewy on the inside. Fresh mozzarella and the sauce had the perfect balance of herbs and spices.',
                    'Outstanding pizza! Authentic Italian taste with high-quality ingredients. The wood-fired flavor really comes through.',
                    'Best pizza in town! Generous toppings, perfect cheese pull, and delivered hot. This is what pizza should taste like!',
                ],
                4 => [
                    'Great pizza! Really enjoyed the flavors, just wished there was a bit more cheese. Overall very satisfying.',
                    'Good quality pizza. The crust was nice and crispy, sauce was flavorful. Delivery was quick too.',
                ],
                3 => [
                    'Decent pizza. Nothing extraordinary but it satisfied my craving. Fair price for the quality.',
                    'Average pizza. The toppings were fresh but the crust was a bit too thick for my liking.',
                ],
                2 => [
                    'Pizza was okay but arrived lukewarm. The cheese had hardened and it wasn\'t as fresh as expected.',
                ],
                1 => [
                    'Terrible pizza! The crust was burnt, toppings were sparse, and it was cold when delivered.',
                ],
            ],
            'burger' => [
                5 => [
                    'Incredible burger! The patty was juicy and perfectly seasoned. Fresh lettuce, tomatoes, and the bun was soft and toasted.',
                    'Amazing burger! Cooked to perfection with high-quality beef. The combination of flavors was outstanding.',
                    'Best burger ever! Everything was fresh, the meat was tender, and the sauce complemented everything perfectly.',
                ],
                4 => [
                    'Really good burger! The meat was tasty and well-cooked. Just wished the fries were a bit crispier.',
                    'Great burger! Good portion size and fresh ingredients. The pickle added a nice tangy touch.',
                ],
                3 => [
                    'Decent burger. The patty was cooked well but the bun was a bit soggy. Average taste overall.',
                    'Fair burger. Nothing special but it filled me up. The price was reasonable.',
                ],
                2 => [
                    'Disappointing burger. The meat was dry and the vegetables weren\'t fresh. Expected better.',
                ],
                1 => [
                    'Awful burger! The patty was overcooked and dry, vegetables were wilted, and it was cold.',
                ],
            ],
            'pasta' => [
                5 => [
                    'Exceptional pasta! Al dente perfection with a rich, creamy sauce. The parmesan was freshly grated and melted beautifully.',
                    'Outstanding pasta dish! The sauce was perfectly balanced and the pasta texture was spot on. Restaurant quality!',
                    'Perfect pasta! Fresh ingredients, perfectly cooked noodles, and the sauce had amazing depth of flavor.',
                ],
                4 => [
                    'Great pasta! Really enjoyed the flavors and texture. Just a bit too much salt for my taste.',
                    'Good pasta dish. The sauce was creamy and flavorful. Portion size was generous.',
                ],
                3 => [
                    'Decent pasta. The noodles were cooked well but the sauce was a bit bland. Average experience.',
                    'Fair pasta dish. Nothing extraordinary but it was filling and reasonably priced.',
                ],
                2 => [
                    'Below average pasta. The noodles were overcooked and the sauce lacked flavor.',
                ],
                1 => [
                    'Terrible pasta! Mushy noodles and the sauce tasted like it came from a jar. Very disappointing.',
                ],
            ],
            'asian' => [
                5 => [
                    'Authentic Asian flavors! The spices were perfectly balanced and the vegetables were crisp and fresh.',
                    'Amazing Asian cuisine! Every bite was bursting with flavor. The rice was perfectly steamed.',
                    'Outstanding! The sauce was rich and flavorful, meat was tender, and presentation was beautiful.',
                ],
                4 => [
                    'Great Asian food! Good balance of flavors and fresh ingredients. Just a bit too spicy for me.',
                    'Really good! The dish had authentic taste and good portion size. Would order again.',
                ],
                3 => [
                    'Decent Asian food. The flavors were okay but nothing special. Fair for the price.',
                    'Average dish. The taste was fine but lacked the authentic Asian flavors I was expecting.',
                ],
                2 => [
                    'Disappointing. The food was bland and didn\'t have the authentic taste I was hoping for.',
                ],
                1 => [
                    'Awful! The food was oversalted and the vegetables were overcooked. Not authentic at all.',
                ],
            ],
            'dessert' => [
                5 => [
                    'Divine dessert! Perfectly sweet with amazing texture. The presentation was beautiful and taste was heavenly.',
                    'Incredible dessert! Rich, creamy, and not too sweet. This is exactly what I was craving.',
                    'Perfect dessert! Fresh ingredients and amazing flavors. Best way to end a meal.',
                ],
                4 => [
                    'Great dessert! Really enjoyed the sweetness level and texture. Just wished the portion was bigger.',
                    'Good dessert. Nice flavors and good presentation. Worth the price.',
                ],
                3 => [
                    'Decent dessert. It was sweet and satisfying but nothing extraordinary.',
                    'Average dessert. Good enough to satisfy my sweet tooth but not memorable.',
                ],
                2 => [
                    'Below average dessert. Too sweet for my liking and the texture was off.',
                ],
                1 => [
                    'Terrible dessert! Way too sweet and the texture was unpleasant. Couldn\'t finish it.',
                ],
            ],
        ];

        // Food-specific reviewer names
        $foodieNames = [
            'Chef Marco', 'Foodie Sarah', 'Taste Tester Mike', 'Culinary Emma',
            'Gourmet David', 'Kitchen Lisa', 'Flavor Hunter', 'Spice Master',
            'Food Critic Anna', 'Hungry James', 'Dish Explorer', 'Meal Reviewer',
            'Cuisine Lover', 'Recipe Tester', 'Food Blogger', 'Taste Expert',
            'Cooking Enthusiast', 'Food Adventurer', 'Flavor Seeker', 'Meal Judge',
        ];

        $foodieEmails = [
            'chef.marco@foodreview.com', 'foodie.sarah@tastebud.com', 'mike.tester@flavor.com',
            'culinary.emma@gourmet.com', 'gourmet.david@cuisine.com', 'kitchen.lisa@recipe.com',
            'flavor.hunter@spice.com', 'spice.master@taste.com', 'anna.critic@food.com',
            'hungry.james@meal.com', 'dish.explorer@plate.com', 'meal.reviewer@dish.com',
            'cuisine.lover@kitchen.com', 'recipe.tester@cook.com', 'food.blogger@bite.com',
            'taste.expert@flavor.com', 'cooking.enthusiast@chef.com', 'food.adventurer@eat.com',
            'flavor.seeker@savory.com', 'meal.judge@delicious.com',
        ];

        // Get existing users
        $users = User::limit(15)->pluck('id')->toArray();

        // Get food models
        $foodItems = FoodItem::limit(30)->get();
        $foodCategories = FoodCategory::limit(15)->get();
        $foodEvents = FoodEvent::limit(8)->get();

        $this->command->info('Creating detailed food reviews for Food Items...');
        $this->createDetailedReviews($foodItems, $detailedFoodReviews, $foodieNames, $foodieEmails, $users, 'FoodItem');

        $this->command->info('Creating detailed food reviews for Food Categories...');
        $this->createDetailedReviews($foodCategories, $detailedFoodReviews, $foodieNames, $foodieEmails, $users, 'FoodCategory');

        $this->command->info('Creating detailed food reviews for Food Events...');
        $this->createDetailedReviews($foodEvents, $detailedFoodReviews, $foodieNames, $foodieEmails, $users, 'FoodEvent');

        $this->command->info('Detailed food review seeders completed successfully!');
    }

    /**
     * Create detailed reviews for a specific model type
     */
    private function createDetailedReviews($models, $detailedFoodReviews, $foodieNames, $foodieEmails, $users, $modelType)
    {
        foreach ($models as $model) {
            // Create 2-5 detailed reviews per item
            $reviewsCount = rand(2, 5);
            
            for ($i = 0; $i < $reviewsCount; $i++) {
                // Generate rating with realistic distribution
                $rating = $this->generateRealisticRating();
                
                // Select appropriate review based on model name/type
                $reviewText = $this->selectAppropriateReview($model, $rating, $detailedFoodReviews);
                
                // 70% chance of being a user review, 30% foodie guest
                $isUser = !empty($users) && rand(1, 10) <= 7;
                
                $data = [
                    'ratable_type' => 'Modules\\FoodManagements\\Models\\' . $modelType,
                    'ratable_id' => $model->id,
                    'rating' => $rating,
                    'review' => $reviewText,
                    'ip_address' => $this->generateRandomIP(),
                    'is_approved' => rand(1, 10) > 1, // 90% approved
                    'status' => rand(1, 10) > 1, // 90% active
                    'created_at' => now()->subDays(rand(0, 120)),
                    'updated_at' => now()->subDays(rand(0, 20)),
                ];

                if ($isUser) {
                    // User review
                    $data['user_id'] = $users[array_rand($users)];
                    $data['guest_name'] = null;
                    $data['guest_email'] = null;
                } else {
                    // Foodie guest review
                    $index = array_rand($foodieNames);
                    $data['user_id'] = null;
                    $data['guest_name'] = $foodieNames[$index];
                    $data['guest_email'] = $foodieEmails[$index];
                }

                Rating::create($data);
            }
        }
    }

    /**
     * Select appropriate review based on model name/type
     */
    private function selectAppropriateReview($model, $rating, $detailedFoodReviews)
    {
        $modelName = strtolower($model->name ?? '');
        
        // Try to match food type based on name
        foreach ($detailedFoodReviews as $foodType => $reviews) {
            if (strpos($modelName, $foodType) !== false) {
                if (isset($reviews[$rating]) && !empty($reviews[$rating])) {
                    return $reviews[$rating][array_rand($reviews[$rating])];
                }
            }
        }
        
        // Fallback to general food reviews
        $generalReviews = [
            5 => [
                'Absolutely fantastic! The quality and taste exceeded all my expectations. Highly recommended!',
                'Perfect in every way! Fresh ingredients, amazing flavors, and excellent presentation.',
                'Outstanding! This is exactly what I was looking for. Will definitely order again.',
            ],
            4 => [
                'Really good! Great taste and quality. Just a minor issue but overall very satisfied.',
                'Very pleased with this! Good flavors and fresh ingredients. Worth the price.',
                'Great choice! Enjoyed every bite. Good portion size and reasonable price.',
            ],
            3 => [
                'Decent option. Nothing extraordinary but it met my basic expectations.',
                'Average experience. The taste was okay but nothing special.',
                'Fair enough. Good for a quick meal but not memorable.',
            ],
            2 => [
                'Below expectations. The quality wasn\'t what I hoped for given the price.',
                'Disappointing. Had some issues with freshness and taste.',
                'Not great. Expected better quality and flavor.',
            ],
            1 => [
                'Very disappointing! Poor quality and taste. Would not recommend.',
                'Terrible experience! Everything was wrong - taste, quality, and presentation.',
                'Awful! Complete waste of money. Very poor quality.',
            ],
        ];
        
        return $generalReviews[$rating][array_rand($generalReviews[$rating])];
    }

    /**
     * Generate realistic rating distribution
     * Distribution: 1★(3%), 2★(7%), 3★(15%), 4★(40%), 5★(35%)
     */
    private function generateRealisticRating(): int
    {
        $rand = rand(1, 100);
        
        if ($rand <= 3) return 1;
        if ($rand <= 10) return 2;
        if ($rand <= 25) return 3;
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