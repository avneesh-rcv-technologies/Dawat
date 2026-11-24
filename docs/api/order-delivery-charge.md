# Calculate delivery charges

`POST /api/orders/delivery-charge`

Implemented in the Orders module. Send a customer bearer token, `Accept: application/json`, and `Content-Type: application/json`. The endpoint allows 60 requests per minute per customer.

## Request using customer coordinates

```json
{
  "restaurant_id": 404,
  "latitude": 40.730610,
  "longitude": -73.935242,
  "order_subtotal": 40.00
}
```

`latitude` and `longitude` are the customer's delivery destination. The origin comes from the restaurant's saved coordinates.

## Request using a saved customer address

```json
{
  "restaurant_id": 404,
  "saved_address_id": 22,
  "order_subtotal": 40.00
}
```

Send either a saved address ID or both coordinates. A saved address must belong to the authenticated customer and have valid latitude and longitude. Coordinates can be zero; latitude must be between -90 and 90, longitude between -180 and 180.

`order_subtotal` is required, nonnegative, and permits up to two decimal places (maximum 99,999,999.99). Use the food subtotal after discounts and before delivery fees, service charges, and tax, in the site's configured currency. The policy amounts and returned delivery fee use that same currency.

## Successful response

For a restaurant at latitude `40.7128`, longitude `-74.0060`, with a 15-mile radius, a rate of 2.50 per mile, and free delivery from 50.00:

```json
{
  "success": true,
  "message": "Delivery charge calculated successfully.",
  "data": {
    "restaurant_id": 404,
    "distance_miles": 3.9042,
    "distance_method": "straight_line",
    "order_subtotal": 40,
    "delivery_available": true,
    "reason": null,
    "delivery_fee": 9.76,
    "is_free_delivery": false,
    "free_delivery_applied": false,
    "policy": {
      "delivery_radius_miles": 15,
      "delivery_cost_per_mile": 2.5,
      "free_delivery_enabled": true,
      "free_delivery_threshold": 50
    }
  }
}
```

## Calculation rules

- Distance uses the Haversine formula: straight-line miles between saved restaurant coordinates and customer coordinates. Driving distance requires a separate routing integration.
- `delivery_fee = round(distance_miles × delivery_cost_per_mile, 2)`, calculated using the full distance before rounding. Displayed distance has up to four decimal places.
- When free delivery is enabled and the subtotal meets or exceeds the configured threshold, the fee is zero and `free_delivery_applied` is true.
- A zero per-mile rate also produces a zero fee (`is_free_delivery: true`); it does not set `free_delivery_applied` unless the free-delivery threshold was met.
- The radius check applies before free delivery. A null radius has no distance limit.
- Disabled delivery or a destination outside the radius returns HTTP 200 with `success: true` (the calculation succeeded), `delivery_available: false`, `delivery_fee: null`, and `reason: "delivery_disabled"` or `"outside_delivery_radius"`. Free-delivery flags are false. The distance and policy remain in the response.

Google's existing geocoding service resolves missing restaurant or saved-customer address coordinates. The distance calculation uses those coordinates and the Haversine formula. Existing valid coordinates are reused. Failed geocoding produces a validation error for a standalone quote; cart and address APIs return an unavailable-delivery state while allowing the address edit to complete.

Quotes also return `estimated_delivery_minutes`, `estimated_delivery_at`, and `estimate_method: "preparation_plus_straight_line_travel"`. The estimate uses `orders.delivery.preparation_minutes` (default 30) plus travel at `orders.delivery.average_speed_mph` (default 25). It is an estimate from straight-line distance, rather than a road or traffic ETA. Unavailable delivery has null estimates.

Customer carts now persist their delivery fee and estimated delivery time. Checkout recalculates from the current cart subtotal, address, and restaurant policy before placing an order. See [customer address and cart delivery APIs](customer-address-delivery.md).

## Errors

| HTTP status | Meaning |
| --- | --- |
| 401 | Customer authentication is required. |
| 404 | Restaurant not found, or saved address not found for this customer. |
| 422 | Invalid/missing input, or missing/invalid restaurant or saved-address coordinates. |
| 429 | Too many quote requests. |

Validation errors use Laravel's JSON `message` and `errors` fields. For example, if restaurant coordinates are missing:

```json
{
  "message": "The restaurant does not have valid address coordinates. Please update its address.",
  "errors": {
    "restaurant_id": [
      "The restaurant does not have valid address coordinates. Please update its address."
    ]
  }
}
```
