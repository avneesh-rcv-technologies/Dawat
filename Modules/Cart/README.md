# Cart Module API Documentation

## Overview

The Cart Module provides a comprehensive API-based cart system for managing food items in a restaurant ordering system. It supports both guest (session-based) and authenticated user carts with full tax calculation, options handling, and quantity management.

## Features

- **Session-based carts** for guest users
- **User-based carts** for authenticated users
- **Tax calculation** (inclusive/exclusive) integration
- **Food item options** support with pricing
- **Quantity validation** (manual stock and grouping types)
- **Cart transfer** from guest to user account
- **Real-time totals** calculation
- **Comprehensive error handling** with ApiExceptionHandler

## API Endpoints

### Base URL: `/api/cart`

### 1. Get Cart Details
```http
GET /api/cart/{restaurantId}
```

**Headers:**
- `X-Session-ID` (optional): Custom session ID for guest users
- `Authorization: Bearer {token}` (optional): For authenticated users

**Response:**
```json
{
  "success": true,
  "cart": {
    "id": 1,
    "restaurant_id": 1,
    "subtotal": 25.00,
    "tax_amount": 2.50,
    "total": 27.50,
    "is_tax_inclusive": false,
    "currency": "USD",
    "tax_breakdown": {
      "VAT": {
        "name": "VAT",
        "rate": 10,
        "amount": 2.50
      }
    },
    "total_items": 3,
    "formatted_subtotal": "25.00 USD",
    "formatted_tax_amount": "2.50 USD",
    "formatted_total": "27.50 USD",
    "items": [
      {
        "id": 1,
        "food_item_id": 1,
        "food_item": {
          "id": 1,
          "name": "Margherita Pizza",
          "slug": "margherita-pizza",
          "description": "Classic pizza with tomato and mozzarella",
          "image": "food_items/pizza.jpg",
          "categories": [
            {
              "id": 1,
              "name": "Pizza",
              "slug": "pizza"
            }
          ]
        },
        "quantity": 2,
        "unit_price": 10.00,
        "unit_display_price": 11.00,
        "unit_tax_amount": 1.00,
        "total_price": 20.00,
        "total_display_price": 22.00,
        "total_tax_amount": 2.00,
        "is_tax_inclusive": false,
        "tax_breakdown": {
          "VAT": {
            "name": "VAT",
            "rate": 10,
            "amount": 2.00
          }
        },
        "selected_options": [
          {
            "id": 1,
            "name": "Extra Cheese",
            "price": 2.00
          }
        ],
        "options_total_price": 2.00,
        "selected_options_names": ["Extra Cheese"],
        "special_instructions": "Extra crispy",
        "display_name": "Margherita Pizza (Extra Cheese)",
        "formatted_unit_price": "10.00 USD",
        "formatted_unit_display_price": "11.00 USD",
        "formatted_total_price": "20.00 USD",
        "formatted_total_display_price": "22.00 USD",
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z"
      }
    ],
    "created_at": "2026-01-22T12:00:00.000000Z",
    "updated_at": "2026-01-22T12:00:00.000000Z"
  },
  "message": "Cart retrieved successfully"
}
```

### 2. Add Item to Cart
```http
POST /api/cart/{restaurantId}/add
```

**Request Body:**
```json
{
  "food_item_id": 1,
  "quantity": 2,
  "selected_options": [
    {
      "id": 1,
      "name": "Extra Cheese",
      "price": 2.00
    },
    {
      "id": 2,
      "name": "Large Size",
      "price": 3.00
    }
  ],
  "special_instructions": "Extra crispy please"
}
```

**Validation Rules:**
- `food_item_id`: required, integer, must exist in food_items table
- `quantity`: required, integer, min:1, max:100
- `selected_options`: optional, array
- `selected_options.*.id`: required_with:selected_options, integer, must exist in food_options table
- `selected_options.*.name`: required_with:selected_options, string, max:255
- `selected_options.*.price`: required_with:selected_options, numeric, min:0
- `special_instructions`: optional, string, max:500

**Response:** Same structure as Get Cart Details with added cart_item

### 3. Update Cart Item Quantity
```http
PUT /api/cart/items/{cartItemId}
```

**Request Body:**
```json
{
  "quantity": 3
}
```

**Validation Rules:**
- `quantity`: required, integer, min:0, max:100 (0 removes the item)

### 4. Remove Item from Cart
```http
DELETE /api/cart/items/{cartItemId}
```

**Response:**
```json
{
  "success": true,
  "cart": { /* Updated cart object */ },
  "message": "Item removed from cart successfully"
}
```

### 5. Clear Entire Cart
```http
DELETE /api/cart/{restaurantId}/clear
```

**Response:**
```json
{
  "success": true,
  "cart": {
    "id": 1,
    "restaurant_id": 1,
    "subtotal": 0,
    "tax_amount": 0,
    "total": 0,
    "total_items": 0,
    "items": []
  },
  "message": "Cart cleared successfully"
}
```

### 6. Get Cart Count
```http
GET /api/cart/{restaurantId}/count
```

**Response:**
```json
{
  "success": true,
  "count": 5,
  "message": "Cart count retrieved successfully"
}
```

### 7. Transfer Guest Cart to User Account
```http
POST /api/cart/{restaurantId}/transfer
```

**Headers:**
- `Authorization: Bearer {token}` (required)
- `X-Session-ID` (optional): Session ID to transfer from

**Response:**
```json
{
  "success": true,
  "cart": { /* Transferred cart object */ },
  "message": "Cart transferred successfully"
}
```

## Error Responses

All endpoints use consistent error response format:

```json
{
  "success": false,
  "errors": "Error message description"
}
```

**Common HTTP Status Codes:**
- `200`: Success
- `201`: Created (for add to cart)
- `401`: Unauthorized (for transfer endpoint without authentication)
- `422`: Validation Error
- `500`: Server Error

**Validation Error Response:**
```json
{
  "success": false,
  "errors": "Food item is required",
  "validation_errors": {
    "food_item_id": ["Food item is required"]
  }
}
```

## Session Management

### Guest Users
- Use `X-Session-ID` header to maintain cart across requests
- If not provided, Laravel's default session ID is used
- Cart is tied to session ID and restaurant ID

### Authenticated Users
- Cart is tied to user ID and restaurant ID
- Session-based cart can be transferred to user account
- User carts persist across sessions

### Cart Transfer Logic
1. If user has existing cart: merge guest cart items into user cart
2. If user has no cart: transfer ownership of guest cart to user
3. Guest cart is deleted after successful transfer

## Tax Integration

The Cart module integrates with the Tax module for accurate pricing:

- **Tax Calculation**: Uses `fn_calculate_food_item_tax()` helper
- **Display Prices**: Includes tax based on system settings (inclusive/exclusive)
- **Tax Breakdown**: Detailed tax information per item and cart total
- **Multiple Tax Categories**: Supports complex tax structures

## Quantity Validation

Supports FoodManagements module quantity types:

### Manual Type (Stock-based)
- Validates against available stock quantity
- Prevents ordering more than available

### Grouping Type (Minimum guests)
- Validates minimum guest count requirements
- Ensures quantity meets minimum threshold

## Options Handling

- **Selected Options**: Stored as JSON with ID, name, and price
- **Option Pricing**: Added to base food item price
- **Tax Calculation**: Options price included in tax calculation
- **Display**: Options shown in item display name

## Database Schema

### carts table
- `id`: Primary key
- `session_id`: For guest users (nullable)
- `user_id`: For authenticated users (nullable)
- `restaurant_id`: Restaurant association
- `subtotal`, `tax_amount`, `total`: Calculated totals
- `is_tax_inclusive`: Tax calculation type
- `currency`: Currency code
- `tax_breakdown`: JSON tax details

### cart_items table
- `id`: Primary key
- `cart_id`: Foreign key to carts
- `food_item_id`: Foreign key to food_items
- `quantity`: Item quantity
- `unit_price`, `unit_display_price`, `unit_tax_amount`: Unit pricing
- `total_price`, `total_display_price`, `total_tax_amount`: Total pricing
- `is_tax_inclusive`: Tax calculation type
- `tax_breakdown`: JSON tax details
- `selected_options`: JSON selected options
- `special_instructions`: Customer notes

## Usage Examples

### Frontend Integration

```javascript
// Add item to cart
const addToCart = async (restaurantId, foodItemId, quantity, options) => {
  const response = await fetch(`/api/cart/${restaurantId}/add`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Session-ID': getSessionId(), // For guest users
      'Authorization': `Bearer ${getAuthToken()}` // For authenticated users
    },
    body: JSON.stringify({
      food_item_id: foodItemId,
      quantity: quantity,
      selected_options: options,
      special_instructions: 'Extra crispy'
    })
  });
  
  return response.json();
};

// Get cart
const getCart = async (restaurantId) => {
  const response = await fetch(`/api/cart/${restaurantId}`, {
    headers: {
      'X-Session-ID': getSessionId(),
      'Authorization': `Bearer ${getAuthToken()}`
    }
  });
  
  return response.json();
};

// Update item quantity
const updateQuantity = async (cartItemId, quantity) => {
  const response = await fetch(`/api/cart/items/${cartItemId}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'X-Session-ID': getSessionId()
    },
    body: JSON.stringify({ quantity })
  });
  
  return response.json();
};
```

### 8. Cart Summary Endpoints

#### Get Cart Summary for Multiple Restaurants
```http
GET /api/cart/summary
```

**Request Body:**
```json
{
  "restaurant_ids": [1, 2, 3]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "restaurants": [
      {
        "restaurant_id": 1,
        "items_count": 3,
        "total_amount": 45.50,
        "currency": "USD",
        "formatted_total": "45.50 USD"
      },
      {
        "restaurant_id": 2,
        "items_count": 1,
        "total_amount": 12.00,
        "currency": "USD",
        "formatted_total": "12.00 USD"
      }
    ],
    "total_items": 4,
    "total_amount": 57.50,
    "currency": "USD"
  },
  "message": "Cart summary retrieved successfully"
}
```

#### Get Quick Cart Info
```http
GET /api/cart/quick-info/{restaurantId}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "restaurant_id": 1,
    "items_count": 3,
    "has_items": true,
    "total_amount": 45.50,
    "formatted_total": "45.50 USD"
  },
  "message": "Quick cart info retrieved successfully"
}
```

## Events System

The Cart module dispatches events for all major operations:

### Available Events

1. **CartItemAdded** - When an item is added to cart
2. **CartItemUpdated** - When cart item quantity is updated
3. **CartItemRemoved** - When an item is removed from cart
4. **CartCleared** - When entire cart is cleared

### Event Usage Example

```php
// Listen to cart events
Event::listen(CartItemAdded::class, function ($event) {
    // Log cart activity
    Log::info('Item added to cart', [
        'cart_id' => $event->cart->id,
        'item_id' => $event->cartItem->id,
        'user_id' => $event->userId,
        'session_id' => $event->sessionId
    ]);
    
    // Send notification
    // Update analytics
    // Trigger recommendations
});
```

## User Integration

### HasCart Trait

Add the `HasCart` trait to your User model for easy cart management:

```php
use Modules\Cart\Traits\HasCart;

class User extends Authenticatable
{
    use HasCart;
    
    // ... other code
}
```

### Available Methods

```php
// Get user's cart for a restaurant
$cart = $user->getCart($restaurantId);

// Get all user's carts
$carts = $user->getCarts();

// Get total items across all restaurants
$totalItems = $user->getTotalCartItems();

// Get items count for specific restaurant
$count = $user->getCartItemsCount($restaurantId);

// Check if user has items in cart
$hasItems = $user->hasCartItems($restaurantId);

// Add item to cart
$result = $user->addToCart($restaurantId, $foodItemId, 2);

// Clear cart
$result = $user->clearCart($restaurantId);

// Get cart summary
$summary = $user->getCartSummary();
```

## Dependencies

- **FoodManagements Module**: For food items and pricing
- **Tax Module**: For tax calculations
- **Restaurant Module**: For restaurant association
- **Laravel Sanctum**: For API authentication (optional)
- **ApiExceptionHandler**: For consistent error handling