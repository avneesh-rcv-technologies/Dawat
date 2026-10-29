<?php

/**
 * Example: How to use the Rating Module in your models
 * 
 * This file shows how to integrate the Rating module with your existing models.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Rating\Traits\Ratable;

/**
 * Example 1: Product Model with Ratings
 */
class Product extends Model
{
    use Ratable;

    // Your existing model code...

    /**
     * Usage Examples:
     */
    
    // Get all ratings for this product
    // $product->ratings
    
    // Get approved ratings only
    // $product->approvedRatings
    
    // Get average rating (returns float, e.g., 4.5)
    // $product->averageRating()
    
    // Get total rating count (returns int)
    // $product->ratingCount()
    
    // Get rating distribution (returns array with counts for each star)
    // $product->ratingDistribution()
    // Example output: [1 => 2, 2 => 5, 3 => 10, 4 => 20, 5 => 13]
    
    // Get full rating statistics
    // $product->ratingStats()
    // Example output: ['average' => 4.5, 'count' => 50, 'distribution' => [...]]
    
    // Check if a user has rated this product
    // $product->hasRatedBy($userId)
    
    // Get a user's rating for this product
    // $product->ratingBy($userId)
}

/**
 * Example 2: Service Model with Ratings
 */
class Service extends Model
{
    use Ratable;

    // Your existing model code...
}

/**
 * Example 3: Blog Post Model with Ratings
 */
class Post extends Model
{
    use Ratable;

    // Your existing model code...
}

/**
 * Controller Usage Examples
 */

// Example 4: Display product with ratings
class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::with(['approvedRatings.user'])->findOrFail($id);
        
        $ratingStats = [
            'average' => $product->averageRating(),
            'count' => $product->ratingCount(),
            'distribution' => $product->ratingDistribution(),
        ];
        
        return view('products.show', compact('product', 'ratingStats'));
    }
}

/**
 * Blade Template Usage Examples
 */

// Example 5: Display ratings in a view (products/show.blade.php)
/*
@php
    $average = $product->averageRating();
    $count = $product->ratingCount();
    $distribution = $product->ratingDistribution();
@endphp

<div class="product-rating">
    <div class="rating-summary">
        <span class="average-rating">{{ number_format($average, 1) }}</span>
        <span class="stars">{{ str_repeat('⭐', round($average)) }}</span>
        <span class="count">({{ $count }} reviews)</span>
    </div>

    <div class="rating-distribution">
        @for($i = 5; $i >= 1; $i--)
            <div class="rating-bar">
                <span>{{ $i }} ⭐</span>
                <div class="bar">
                    <div class="fill" style="width: {{ $count > 0 ? ($distribution[$i] / $count * 100) : 0 }}%"></div>
                </div>
                <span>{{ $distribution[$i] }}</span>
            </div>
        @endfor
    </div>

    <div class="reviews-list">
        @foreach($product->approvedRatings()->latest()->get() as $rating)
            <div class="review">
                <div class="review-header">
                    <strong>{{ $rating->reviewer_name }}</strong>
                    <span>{{ str_repeat('⭐', $rating->rating) }}</span>
                    <span class="date">{{ $rating->created_at->diffForHumans() }}</span>
                </div>
                @if($rating->review)
                    <p class="review-text">{{ $rating->review }}</p>
                @endif
            </div>
        @endforeach
    </div>
</div>
*/

/**
 * API Usage Examples
 */

// Example 6: Get product with ratings via API
class ApiProductController extends Controller
{
    public function show($id)
    {
        $product = Product::findOrFail($id);
        
        return response()->json([
            'product' => $product,
            'ratings' => [
                'average' => $product->averageRating(),
                'count' => $product->ratingCount(),
                'distribution' => $product->ratingDistribution(),
                'reviews' => $product->approvedRatings()
                    ->latest()
                    ->take(10)
                    ->get()
            ]
        ]);
    }
}

/**
 * Service Usage Examples
 */

// Example 7: Using RatingService directly
use Modules\Rating\Services\RatingService;

class RatingExampleController extends Controller
{
    protected $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    public function submitRating(Request $request, $productId)
    {
        try {
            $rating = $this->ratingService->create([
                'ratable_type' => Product::class,
                'ratable_id' => $productId,
                'rating' => $request->rating,
                'review' => $request->review,
                'guest_name' => $request->guest_name,
                'guest_email' => $request->guest_email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully!',
                'data' => $rating
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getProductRatings($productId)
    {
        $ratings = $this->ratingService->getByRatable(
            Product::class,
            $productId,
            $perPage = 10
        );

        $stats = $this->ratingService->getRatingStats(
            Product::class,
            $productId
        );

        return response()->json([
            'ratings' => $ratings,
            'stats' => $stats
        ]);
    }
}

/**
 * Query Examples
 */

// Example 8: Advanced queries
class ProductRepository
{
    // Get top-rated products
    public function getTopRated($limit = 10)
    {
        return Product::withCount(['approvedRatings'])
            ->withAvg('approvedRatings', 'rating')
            ->having('approved_ratings_count', '>', 0)
            ->orderByDesc('approved_ratings_avg_rating')
            ->limit($limit)
            ->get();
    }

    // Get products with high ratings (4+ stars)
    public function getHighlyRated()
    {
        return Product::whereHas('approvedRatings', function($query) {
            $query->havingRaw('AVG(rating) >= 4');
        })->get();
    }

    // Get recently reviewed products
    public function getRecentlyReviewed($limit = 10)
    {
        return Product::whereHas('approvedRatings')
            ->with(['approvedRatings' => function($query) {
                $query->latest()->limit(1);
            }])
            ->get()
            ->sortByDesc(function($product) {
                return $product->approvedRatings->first()->created_at ?? null;
            })
            ->take($limit);
    }
}

/**
 * Event/Observer Examples
 */

// Example 9: Listen to rating events (optional - you can create these)
class RatingObserver
{
    public function created(Rating $rating)
    {
        // Send notification to admin when new rating is submitted
        // Update product's average rating cache
        // etc.
    }

    public function updated(Rating $rating)
    {
        if ($rating->wasChanged('is_approved') && $rating->is_approved) {
            // Notify user that their rating was approved
            // Update cached statistics
        }
    }
}

/**
 * Seeder Example
 */

// Example 10: Seed ratings for testing
use Modules\Rating\Models\Rating;

class RatingSeeder extends Seeder
{
    public function run()
    {
        $products = Product::all();

        foreach ($products as $product) {
            // Create 5-10 random ratings for each product
            $count = rand(5, 10);
            
            for ($i = 0; $i < $count; $i++) {
                Rating::create([
                    'ratable_type' => Product::class,
                    'ratable_id' => $product->id,
                    'rating' => rand(3, 5), // Random rating between 3-5
                    'review' => fake()->paragraph(),
                    'guest_name' => fake()->name(),
                    'guest_email' => fake()->email(),
                    'ip_address' => fake()->ipv4(),
                    'is_approved' => true,
                    'status' => true,
                ]);
            }
        }
    }
}
