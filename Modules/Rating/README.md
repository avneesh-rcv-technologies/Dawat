# Rating Module

A comprehensive rating and review system for Laravel applications.

## Features

- ⭐ Star-based rating system (1-5 stars)
- 📝 Written reviews with rating
- 👤 Support for both guest and logged-in user reviews
- ✅ Review approval system
- 🎛️ Flexible configuration through General Settings
- 📊 Rating statistics and averages
- 🔐 Permission-based access control
- 📱 RESTful API for frontend integration
- 📋 DataView for easy management

## Installation

The module has been created using:
```bash
php artisan module:make Rating
```

### Run Migrations

```bash
php artisan migrate
```

This will create the `ratings` table with the following structure:
- `id`: Primary key
- `ratable_type`: Polymorphic type (e.g., Product, Service)
- `ratable_id`: Polymorphic ID
- `user_id`: User who gave the rating (nullable for guests)
- `guest_name`: Guest reviewer name
- `guest_email`: Guest reviewer email
- `rating`: Rating value (1-5)
- `review`: Review text
- `ip_address`: IP address of reviewer
- `is_approved`: Approval status
- `status`: Active/Inactive status
- `created_at`, `updated_at`: Timestamps

## Configuration

### General Settings

Navigate to **Settings → Ratings & Reviews** to configure:

1. **Who Can Review**
   - Both Guests and Logged In Users
   - Only Logged In Users
   - Only Guests

2. **Require Approval**
   - Enable/disable review moderation

3. **Allow Edit**
   - Allow users to edit their reviews

4. **Require Review Text**
   - Make written review mandatory

5. **Min/Max Rating**
   - Configure rating range (default: 1-5)

### Environment Variables

Add to your `.env` file:
```env
RATING_WHO_CAN_REVIEW=both  # Options: guest, logged_in, both
RATING_REQUIRE_APPROVAL=true
RATING_ALLOW_EDIT=false
RATING_MIN=1
RATING_MAX=5
RATING_REQUIRE_REVIEW_TEXT=false
```

## Usage

### Admin Routes

All admin routes are protected with `auth:admin` middleware:

- `GET /admin/ratings` - List all ratings
- `GET /admin/ratings/create` - Create rating form
- `POST /admin/ratings` - Store new rating
- `GET /admin/ratings/{id}` - View rating
- `GET /admin/ratings/{id}/edit` - Edit rating form
- `PUT /admin/ratings/{id}` - Update rating
- `DELETE /admin/ratings/{id}` - Delete rating
- `POST /admin/ratings/{id}/approve` - Approve rating
- `POST /admin/ratings/{id}/toggle-status` - Toggle status
- `POST /admin/ratings/bulk-delete` - Bulk delete ratings

### Customer Mobile API

Customer rating submission is authenticated with Laravel Sanctum and tied to a delivered restaurant order. A customer can submit one restaurant review and ratings for eligible food items in that order.

#### Authentication

Send the customer's Sanctum token on the order form and submit requests:

```http
Authorization: Bearer <customer-token>
Accept: application/json
Content-Type: application/json
```

#### Get the order rating form

```http
GET /api/v1/ratings/orders/{orderId}/form
```

Example response:

```json
{
  "success": true,
  "data": {
    "order_id": 123,
    "restaurant": { "id": 7, "name": "Dawat Kitchen" },
    "restaurant_already_rated": false,
    "food_items": [
      { "order_item_id": 456, "name": "Paneer Tikka", "already_rated": false }
    ]
  }
}
```

Use each returned `order_item_id` as the key in `food_ratings`. The API checks the authenticated customer owns the order, that it is delivered, and that each food item belongs to that order's restaurant.

#### Submit restaurant and food-item ratings

```http
POST /api/v1/ratings/orders/submit
```

Request body:

```json
{
  "order_id": 123,
  "restaurant_rating": 5,
  "restaurant_review": "Great meal and friendly service.",
  "food_ratings": {
    "456": {
      "rating": 5,
      "review": "The dish was delicious."
    },
    "457": {
      "rating": 4,
      "review": "Good flavor."
    }
  }
}
```

`restaurant_rating` is required and must be 1–5. `restaurant_review` is optional and limited to 2,000 characters. `food_ratings` is optional; each submitted item's rating must be 1–5 and its review is optional (up to 2,000 characters). Do not send food item IDs as keys: the keys are order item IDs from the form response.

Successful response (`201`):

```json
{
  "success": true,
  "message": "Your ratings were submitted for review.",
  "data": [
    {
      "id": 9001,
      "type": "restaurant",
      "ratable_id": 7,
      "order_id": 123,
      "order_item_id": null,
      "rating": 5,
      "review": "Great meal and friendly service.",
      "status": "pending"
    }
  ]
}
```

If the order has not been delivered yet, the API returns a readable `422` response without a debug trace:

```json
{
  "success": false,
  "code": "ORDER_NOT_DELIVERED",
  "message": "You can rate this order after it has been delivered or pickup is complete.",
  "errors": {
    "order_id": ["You can rate this order after it has been delivered or pickup is complete."]
  }
}
```

If a submitted `food_ratings` key is not an `order_item_id` from that order, the API returns a field-specific message:

```json
{
  "success": false,
  "code": "FOOD_ITEM_NOT_IN_ORDER",
  "message": "This item is not part of the order. Refresh the order details and select one of its food items.",
  "errors": {
    "food_ratings.999": [
      "This item is not part of the order. Refresh the order details and select one of its food items."
    ]
  }
}
```

Ratings start pending moderation. In-app notifications are attempted for the admin team and restaurant owner; notification failures are logged and do not fail the rating submission.

#### List public reviews

Only approved and active reviews are returned. Use the stable `restaurant` or `food_item` type aliases:

```http
GET /api/v1/ratings?ratable_type=restaurant&ratable_id=7&per_page=10
GET /api/v1/ratings?ratable_type=food_item&ratable_id=42&per_page=10
```

`per_page` is optional and accepts 1–50 (default 10). The response includes the paginator under `data` and the selected entity's average and count under `stats`.

#### Get rating statistics

```http
GET /api/v1/ratings/stats?ratable_type=restaurant&ratable_id=7
```

Example response:

```json
{
  "success": true,
  "data": { "average": 4.5, "count": 24 }
}
```

#### Get a public review by ID

```http
GET /api/v1/ratings/{id}
```

This returns `404` for a review that is not approved and active. `POST /api/v1/ratings` is not the customer submission endpoint; use the authenticated order submission route above.

## Using in Your Models

To make a model ratable, add the relationship:

```php
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Rating\Models\Rating;

class Product extends Model
{
    /**
     * Get all ratings for the product.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'ratable');
    }

    /**
     * Get approved ratings only.
     */
    public function approvedRatings(): MorphMany
    {
        return $this->ratings()->where('is_approved', true)->where('status', true);
    }

    /**
     * Get average rating.
     */
    public function averageRating(): float
    {
        return $this->approvedRatings()->avg('rating') ?? 0;
    }

    /**
     * Get rating count.
     */
    public function ratingCount(): int
    {
        return $this->approvedRatings()->count();
    }
}
```

## Services

### RatingService

```php
use Modules\Rating\Services\RatingService;

$ratingService = app(RatingService::class);

// Get all ratings
$ratings = $ratingService->getAll();

// Get paginated ratings
$ratings = $ratingService->getPaginated(15);

// Get approved ratings
$ratings = $ratingService->getApproved(10);

// Get ratings by ratable
$ratings = $ratingService->getByRatable('App\\Models\\Product', 1, 10);

// Get rating statistics
$stats = $ratingService->getRatingStats('App\\Models\\Product', 1);
// Returns: ['average' => 4.5, 'count' => 50]

// Create rating
$rating = $ratingService->create([
    'ratable_type' => 'App\\Models\\Product',
    'ratable_id' => 1,
    'rating' => 5,
    'review' => 'Excellent!',
    'guest_name' => 'John Doe',
    'guest_email' => 'john@example.com',
]);

// Update rating
$rating = $ratingService->update($id, ['rating' => 4]);

// Approve rating
$rating = $ratingService->approve($id);

// Toggle status
$rating = $ratingService->toggleStatus($id);

// Delete rating
$result = $ratingService->delete($id);

// Bulk delete
$result = $ratingService->bulkDelete([1, 2, 3]);
```

## Permissions

The following permissions are registered:

- `ratings.index` - View Ratings
- `ratings.create` - Create Ratings
- `ratings.store` - Store Ratings
- `ratings.edit` - Edit Ratings
- `ratings.update` - Update Ratings
- `ratings.destroy` - Delete Ratings
- `ratings.toggle-status` - Change Rating Status
- `ratings.approve` - Approve Ratings
- `ratings.bulk-delete` - Delete Bulk Ratings

## DataView

The module includes a powerful DataView component (`RatingsGrid`) that provides:

- Sortable columns
- Searchable fields
- Filterable options (rating, approval status, status)
- Date range filters
- Bulk actions
- Inline actions (approve, toggle status, edit, delete)

## Architecture

The module follows clean architecture principles:

- **Models**: `Rating` model with relationships
- **Repositories**: `RatingRepository` for data access
- **Services**: `RatingService` for business logic
- **Controllers**: `RatingController` for admin panel, `RatingApiController` for API
- **DataView**: `RatingsGrid` for data presentation
- **Listeners**: Permission and menu registration
- **Hooks**: Integration with General Settings

## Events & Listeners

### RegisterRatingPermissions
Registers all rating-related permissions in the ACL system.

### Settings
Adds "Ratings & Reviews" menu item to the settings page.

## Hooks

The module provides hooks for General Settings integration:

- `generalsetting/tabs.post.blade.php` - Adds rating tab
- `generalsetting/tab_content.post.blade.php` - Adds rating configuration form

## Mobile Integration Example

```javascript
// Load the order and its eligible restaurant/food items before showing the rating form.
async function getOrderRatingForm(orderId, token) {
  const response = await fetch(`/api/v1/ratings/orders/${orderId}/form`, {
    headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
  });
  return response.json();
}

// Submit the restaurant review plus any food-item reviews for this delivered order.
async function submitOrderRatings(orderId, token, restaurantRating, restaurantReview, foodRatings) {
  const response = await fetch('/api/v1/ratings/orders/submit', {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({
      order_id: orderId,
      restaurant_rating: restaurantRating,
      restaurant_review: restaurantReview,
      food_ratings: foodRatings, // Keys are order_item_id values from getOrderRatingForm().
    }),
  });
  return { status: response.status, body: await response.json() };
}

// Read public ratings and summary statistics for a restaurant or food item.
async function getPublicRatings(type, id) {
  const response = await fetch(
    `/api/v1/ratings?ratable_type=${encodeURIComponent(type)}&ratable_id=${id}&per_page=10`
  );
  return response.json();
}
```

## License

This module is part of the Laravel application and follows the same license.
