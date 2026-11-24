# Review order

`GET /api/checkout/review-order?restaurant_id=114`

Send `Accept: application/json` and `Authorization: Bearer <customer-token>`.
Guest customers with a customer token can also review their cart. An anonymous
cart session alone does not authorize checkout.

| Query parameter | Required | Description |
| --- | --- | --- |
| `restaurant_id` | Yes | Positive integer ID of an existing restaurant. |
| `payment_method` | No | Active payment configuration ID returned by `/api/checkout/payment-methods`. |

Save delivery or pickup details and add cart items before calling this endpoint.
The API uses the customer's cart for this restaurant, following the same
session-first, latest-cart fallback used by order placement.

```bash
curl --get "$BASE_URL/api/checkout/review-order" \
  --header "Accept: application/json" \
  --header "Authorization: Bearer $CUSTOMER_TOKEN" \
  --data-urlencode "restaurant_id=114" \
  --data-urlencode "payment_method=1"
```

A successful response has `success: true`, a message and `data` containing:

| Field | Details |
| --- | --- |
| `restaurant`, `restaurant_gallery` | Restaurant ID, name, phone, image and gallery URLs. |
| `cart_id`, `order_ref` | Cart ID and preview reference. The actual order number is generated when placing the order. |
| `customer` | Customer name, email and phone. |
| `delivery_method` | `Delivery` or `Pickup`. |
| `preferred_delivery_date` | Saved scheduled date/time, serialized as an ISO timestamp, or null if not saved. |
| `guest_count`, `event_guest_count` | Customer's saved guest count and cart event guest count. |
| `delivery_address` | The cart's selected address belonging to the customer; null for pickup. |
| `pickup_address` | Restaurant's primary address, with restaurant address fields as fallback; null for delivery. |
| `payment_method` | Preview of the supplied configuration ID, merchant name and method code; null when omitted. |
| `items`, `line_count` | Every cart line, including unavailable items. |
| `items_by_category` | Each line appears once, under its first restaurant category by ID, or `Other items`. |
| `coupon_code`, `pricing` | Recalculated cart amounts, discounts, taxes, delivery quote and service charge. |

Each item keeps the original review fields (`id` as the food item ID, `name`,
`quantity`, `unit_price`, `total_price`, `currency`) and adds `cart_item_id`,
`item_id`, availability, serving option, line guest count, notes, selected options,
display labels and detailed `pricing`. Use `cart_item_id` to identify distinct
selections of the same food item. For per-person selections, the unit price is
the price per guest, and `pricing.billable_units` is the line guest count.

`pricing.total` is the cart total plus `general.default_service_charge`, matching
order placement. `service_fee` and `service_charge` expose the same charge for
compatibility. Delivery and item discounts are already reflected in cart totals.
`subtotal` is after item discounts; `subtotal_before_discount` restores those
savings. `item_discount` and `coupon_discount` separate the two discount types.

Unavailable delivery keeps `delivery_fee: null`, `delivery_available: false` and
the unavailable reason. It is never labelled free delivery. Pickup has no
delivery fee and is not labelled free delivery. Check item `is_available` and
delivery availability before enabling the final order action; placement checks
the current checkout data again.

The payment preview does not persist a selection. Supply the same configuration
ID as `payment_method` to `POST /api/checkout/place-order`.

## Saved delivery/pickup schedule

`POST /api/checkout/save-delivery-details` accepts `preferred_delivery_date`
(for example, `2026-12-01`) and `preferred_delivery_time` (`06:30 PM`, in
`h:i A` format). The pickup save endpoint accepts the same fields. The save
endpoint combines these into `customers.preferred_delivery_date` using the
application timezone, alongside the guest count and delivery/pickup method.

When checkout places an order, it copies this combined timestamp into
`orders.preferred_delivery_date`. The placement response and order metadata
return `preferred_delivery_date` as an ISO timestamp. The order details page
shows this stored value as **Requested delivery** or **Requested pickup**,
separately from the calculated estimated delivery/ready time. Later changes to
the customer's checkout preference do not change an existing order's schedule.

Apply migration `2026_11_23_000005_add_preferred_delivery_date_to_orders` before
deploying this change. Older orders keep a null schedule: the customer's current
preference cannot establish what they requested for a previous order.

| Status | Meaning |
| --- | --- |
| 200 | Review returned successfully, including availability details. |
| 400 | Delivery/pickup details missing, or cart empty. |
| 401 | Customer authentication required. |
| 404 | Checkout data disappeared while processing. |
| 422 | Invalid query parameters; `errors` contains field messages. |
| 500 | Unexpected failure; a generic error is returned. |
