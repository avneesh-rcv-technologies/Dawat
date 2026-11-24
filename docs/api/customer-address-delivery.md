# Customer addresses and cart delivery pricing

Send the customer bearer token and `Accept: application/json` on each request. IDs in URLs identify the address; there is no need to repeat `id` in the request body. Customers can access only their own addresses.

## Endpoints

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/api/customer/addresses` | List addresses. |
| POST | `/api/customer/addresses` | Add an address and calculate delivery pricing. |
| GET | `/api/customer/addresses/{id}` | Read an address for editing. |
| PUT or PATCH | `/api/customer/addresses/{id}` | Update address fields and recalculate affected carts. |
| DELETE | `/api/customer/addresses/{id}` | Delete an address and recalculate carts. |
| GET | `/api/customer/addresses/get-default` | Read the default address. |
| POST | `/api/customer/addresses/set-default` | Select the delivery address and recalculate customer carts. |
| POST | `/api/orders/delivery-charge` | Calculate a standalone quote. |
| GET | `/api/v2/cart?restaurant_id=404` | Read the cart with current delivery pricing and estimates. |

## Add an address

```json
{
  "address_type": "home",
  "address_line_1": "123 Main Street",
  "address_line_2": "Apartment 4",
  "country_id": 233,
  "state_id": 1452,
  "city_id": 12345,
  "zip_code": "10001",
  "is_default": true
}
```

Use IDs from the location APIs for your actual location. `address_line_1` and `zip_code` are required for creation. Both coordinates may optionally be supplied as `latitude` and `longitude`. With no coordinates, the server uses the configured Google Maps geocoding key to resolve the street address. ZIP codes are strings, preserving leading zeros. The first address becomes default automatically.

## Update an address

`PATCH /api/customer/addresses/22`

```json
{
  "address_line_1": "456 Updated Street",
  "zip_code": "10002"
}
```

Sending street, city, state, country, or ZIP fields without a coordinate pair discards the old coordinates and attempts Google geocoding, even when those fields match the saved address. Failed geocoding leaves coordinates unset, so charges are never calculated from the previous location. Updates containing only non-location fields, such as `is_default`, reuse valid saved coordinates.

To select a saved address for delivery, send:

`POST /api/customer/addresses/set-default`

```json
{ "address_id": 22 }
```

## Response changes

Add, update, and set-default responses keep address fields under `data` and add `data.delivery_carts`. Delete responses return the recalculated carts under `data.delivery_carts` as well. Example cart entry:

```json
{
  "id": 9,
  "cartable_id": 404,
  "delivery_address_id": 22,
  "delivery_fee": "9.76",
  "delivery_distance_miles": "3.9042",
  "delivery_available": true,
  "delivery_unavailable_reason": null,
  "estimated_delivery_minutes": 40,
  "estimated_delivery_at": "2026-11-18T12:40:00Z",
  "delivery_calculated_at": "2026-11-18T12:00:00Z",
  "total": "53.76"
}
```

Example amounts assume a food total including tax of 44.00, a per-mile rate of 2.50, and a 3.9042-mile destination. Timestamps above are illustrative; actual timestamps use the application timezone. The configured estimate defaults to 30 minutes preparation plus travel at 25 miles per hour. Google supplies coordinates; miles and estimated time use straight-line distance.

## Cart behavior

- Delivery pricing refreshes on address edits, selecting a default, deletion, adding/removing/updating cart items, coupon changes, cart reads, and checkout. Restaurant policy changes take effect on the next recalculation.
- Free-delivery eligibility uses the food subtotal after item and coupon discounts, before tax, service charges, and delivery. The restaurant's radius limit still applies.
- The cart has a single `delivery_fee`. Cart-item `delivery_fee` columns contain allocated shares whose sum exactly equals that cart fee. Food item prices remain the food prices. Cart `total` includes delivery exactly once; clients should use the supplied total.
- Both carts and cart items store `estimated_delivery_minutes` and `estimated_delivery_at`. Cart items share the cart's delivery estimate.
- V2 cart responses include `is_free_delivery` as a JSON boolean. It is true when delivery is available and the recalculated policy fee is zero (including an eligible free-delivery threshold or zero per-mile rate). Charged, unavailable, pickup, and empty carts return false.
- Selecting a default address applies it to the customer's restaurant carts. Checkout's `save-delivery-details` endpoint also selects its submitted saved/new address and returns recalculated carts.
- Deleting the default promotes a remaining address. Carts using the deleted destination select the remaining default; deleting the last address clears the quote and marks delivery unavailable. Pickup clears delivery fees and estimates.
- Missing coordinates or a Google failure returns `delivery_fee: null`, `delivery_available: false`, and a reason such as `geocoding_unavailable`. Disabled/out-of-radius delivery is also unavailable. Address/cart updates can still succeed. Checkout rejects an unavailable delivery with HTTP 422.

## Configuration and migration

Configure `general.google_maps_api_key` with a key enabled for Google's Geocoding API. Requests use a 3-second connection timeout and 8-second response timeout, and errors are caught and logged. Estimate settings live in `Modules/Orders/src/Config/config.php` under `delivery`.

Apply the cart migration before using these changes:

```sh
php artisan migrate --path=Modules/Cart/src/Database/Migrations/2026_11_18_000002_add_delivery_pricing_to_carts_and_items.php
```

Validation returns HTTP 422; missing or unowned addresses return 404; unexpected failures return a generic JSON error without internal exception details.

Both `POST /api/checkout/save-delivery-details` and `POST /api/checkout/save-pickup-details` require `restaurant_id` to be a positive integer identifying an existing restaurant. Missing or nonexistent restaurant IDs return HTTP 422 with `errors.restaurant_id` before any delivery or pickup details are saved.
