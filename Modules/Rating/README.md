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

### API Routes

Public API routes for frontend integration:

#### List Reviews
```http
GET /api/v1/ratings?ratable_type=App\Models\Product&ratable_id=1&per_page=10
```

Response:
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [...],
    "total": 50
  },
  "stats": {
    "average": 4.5,
    "count": 50
  }
}
```

#### Add Review
```http
POST /api/v1/ratings
Content-Type: application/json

{
  "ratable_type": "App\\Models\\Product",
  "ratable_id": 1,
  "rating": 5,
  "review": "Great product!",
  "guest_name": "John Doe",
  "guest_email": "john@example.com"
}
```

Response:
```json
{
  "success": true,
  "message": "Thank you! Your review has been submitted and is pending approval.",
  "data": {...}
}
```

#### Get Rating Statistics
```http
GET /api/v1/ratings/stats?ratable_type=App\Models\Product&ratable_id=1
```

Response:
```json
{
  "success": true,
  "data": {
    "average": 4.5,
    "count": 50
  }
}
```

#### Get Single Rating
```http
GET /api/v1/ratings/{id}
```

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

## Frontend Integration Example

```javascript
// Fetch ratings for a product
async function fetchRatings(productId, page = 1) {
  const response = await fetch(
    `/api/v1/ratings?ratable_type=App\\Models\\Product&ratable_id=${productId}&per_page=10&page=${page}`
  );
  const data = await response.json();
  return data;
}

// Submit a rating
async function submitRating(productId, ratingData) {
  const response = await fetch('/api/v1/ratings', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      ratable_type: 'App\\Models\\Product',
      ratable_id: productId,
      rating: ratingData.rating,
      review: ratingData.review,
      guest_name: ratingData.name,
      guest_email: ratingData.email,
    }),
  });
  const data = await response.json();
  return data;
}

// Get rating statistics
async function getRatingStats(productId) {
  const response = await fetch(
    `/api/v1/ratings/stats?ratable_type=App\\Models\\Product&ratable_id=${productId}`
  );
  const data = await response.json();
  return data;
}
```

## License

This module is part of the Laravel application and follows the same license.
