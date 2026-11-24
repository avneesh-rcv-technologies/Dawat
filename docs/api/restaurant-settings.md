# Restaurant settings and delivery policy

Use the restaurant owner's bearer token for all endpoints.

## Update settings

`POST /api/restaurant/me/settings`

```json
{
  "delivery_radius_miles": 10,
  "delivery_cost_per_mile": 2.5,
  "free_delivery_enabled": true,
  "free_delivery_threshold": 50
}
```

The endpoint accepts partial updates. Omitted settings keep their existing values, including service options, cancellation settings, food type, and restaurant status.

Example response (other settings reflect the restaurant's saved values):

```json
{
  "success": true,
  "message": "Settings updated successfully.",
  "data": {
    "delivery_radius_miles": "10.00",
    "delivery_cost_per_mile": "2.50",
    "free_delivery_enabled": true,
    "free_delivery_threshold": "50.00",
    "food_type": "all",
    "is_takeaway": true,
    "is_delivery": true,
    "is_accepting_orders": true,
    "order_time_limit": 48,
    "cancellation_time_limit": 15,
    "allow_order_cancellation": true,
    "cancellable_statuses": ["O", "P", "N", "H"]
  }
}
```

## Read settings

- `GET /api/restaurant/me/settings` returns the same settings under `data`, with message `Restaurant settings fetched successfully.`
- `GET /api/restaurant/me` includes all four delivery policy fields under `restaurant` alongside the existing profile fields.

Decimal values are returned as strings with two decimal places. `free_delivery_enabled` is a JSON boolean.

## Validation and defaults

| Field | Database type | Default | Validation |
| --- | --- | --- | --- |
| `delivery_radius_miles` | decimal(8,2), nullable | null | Greater than zero when supplied; null clears the configured radius |
| `delivery_cost_per_mile` | decimal(8,2) | 0.00 | Zero or greater; cannot be null |
| `free_delivery_enabled` | boolean | false | true or false |
| `free_delivery_threshold` | decimal(8,2), nullable | null | Zero or greater; required in the resulting saved policy when free delivery is enabled |

All decimal fields allow up to two decimal places and a maximum of 999999.99. Monetary values use the restaurant's currency. A saved threshold can be reused when enabling free delivery without resending it. To clear the threshold, disable free delivery in the same request.

Invalid settings return HTTP 422 and leave the saved policy unchanged:

```json
{
  "success": false,
  "message": "Please enter the minimum order value for free delivery.",
  "errors": {
    "free_delivery_threshold": [
      "Please enter the minimum order value for free delivery."
    ]
  }
}
```

The admin Settings tab contains Order Delivery Policy below Order Cancellation Policy. Saving or failing validation returns to the Settings tab and preserves submitted form values.

This change stores and exposes delivery policy settings. Checkout fee calculation and distance enforcement are separate from these settings endpoints.
