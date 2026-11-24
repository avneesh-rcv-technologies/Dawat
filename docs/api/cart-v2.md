# Cart API v2

Send `Accept: application/json` and `Content-Type: application/json` for POST,
PUT, and PATCH requests. Signed-in customers use their customer bearer token.
Guests use the `X-Session-ID` header. Do not send customer ownership or session
IDs in the JSON body.

## Guest sessions

For the first guest add, call `POST /api/v2/cart/add-items` without an
Authorization header. If `X-Session-ID` is also omitted, the server generates a
random 64-character hexadecimal session ID. It stores that ID in `carts.session_id`
with null customer ownership and returns it in both `data.session_id` and the
`X-Session-ID` response header. Save that ID and reuse it on subsequent requests:

```http
X-Session-ID: YOUR_RETURNED_SESSION_ID
```

The same ID identifies the guest cart for add, get, single-item get, update, and
remove. Only one restaurant may have a nonempty cart under that ID. Missing
IDs on reads/updates/removals return a friendly 422; malformed IDs also return 422.
A valid session with no cart returns an empty cart for GET. A different session's
cart item returns 404. The session ID remains in an empty-cart response after
the final item is removed, allowing the client to reuse it for a later add.

Customer tokens continue to identify account-owned carts by customer and
restaurant. A supplied session header does not grant access to or automatically
merge a guest cart into that account. An invalid Authorization header returns
401 even when a guest session header is supplied. Guest carts cannot access
account-owned carts using a session ID. Keep the returned guest session ID
private because it identifies access to that guest cart.

## One restaurant per active cart

`POST /api/v2/cart/add-items` rejects adding items from another restaurant while
the customer or guest session has a nonempty restaurant cart. Remove the existing
items before switching restaurants. Empty carts and carts belonging to other
customers or guest sessions do not block an add. The selected food item must also
belong to the requested restaurant, and its serving option must belong to that item.

Example HTTP 422 response:

```json
{
  "success": false,
  "message": "Your cart already contains items from another restaurant. Please clear your cart before adding items from this restaurant.",
  "errors": {
    "restaurant_id": [
      "Your cart already contains items from another restaurant. Please clear your cart before adding items from this restaurant."
    ]
  }
}
```

## Endpoints

| Method | URL | Purpose |
| --- | --- | --- |
| POST | `/api/v2/cart/add-items` | Add a serving selection and return the cart |
| GET | `/api/v2/cart?restaurant_id=126` | Get the customer's cart for one restaurant |
| GET | `/api/v2/cart/items/{cartItemId}` | Get one owned cart line |
| PATCH / PUT | `/api/v2/cart/items/{cartItemId}` | Update one owned cart line and return the cart |
| DELETE | `/api/v2/cart/items/{cartItemId}` | Remove an entire line and return the remaining cart |
| DELETE | `/api/v2/cart/items?restaurant_id=126` | Clear all items for the selected restaurant and return an empty cart |

Use the cart line's `id` for fetch/update/remove, not its food `item_id` or
`serving_option_id`.

To clear the cart, send `DELETE /api/v2/cart/items?restaurant_id=126` with the
customer bearer token or guest `X-Session-ID`. The restaurant ID is required.
This deletes all matching owned cart lines and their cart records, clearing the
coupon, event attendance, and delivery quote. Other customers, guest sessions,
and restaurants are unaffected. Repeating the request returns HTTP 200 with the
same empty cart structure. Guests retain their session ID in the response body
and header and can reuse it to add items again.

## Quantity and guest-count rules

| Serve types | Meaning of quantity | Guest count | Subtotal |
| --- | --- | --- | --- |
| tray, plate, bowl, box, portion, piece | Number of configured options | Omit | Effective option price × quantity |
| by_weight, by_volume | Number of fixed-size options | Omit | Effective size price × quantity |
| package | Number of packages | Omit | Effective package price × quantity |
| per_person | Must be exactly 1 | Required, within option limits | Effective guest price × guest_count |

A 500 g option with quantity 2 means two 500 g options; do not send 1000 as the
quantity. `serves` is descriptive capacity, not a multiplier for fixed options.

For per-person catering, quantity 10 and guest count 5 is rejected with 422;
quantity cannot act as a second guest multiplier. `total_serves` is exactly the
selected guest count. Repeating the same per-person selection does not increase
its quantity or guest count. Different guest counts or notes remain separate
selections; their guests are never added together to infer event attendance.

## Add a fixed-size option

Use actual IDs from the menu; these IDs are illustrative:

```json
{
  "restaurant_id": 126,
  "item_id": 15,
  "serving_option_id": 900,
  "quantity": 2,
  "note": "Less spicy, please"
}
```

## Add per-person catering

```json
{
  "restaurant_id": 126,
  "item_id": 15,
  "serving_option_id": 11,
  "quantity": 1,
  "guest_count": 5,
  "event_guest_count": 50,
  "note": "Less spicy, please"
}
```

`guest_count` is the number of people buying this particular food selection.
`event_guest_count` is optional, independent cart-level event attendance. It is
never inferred from item guests or package capacities. Omitting it preserves a
previous value; explicitly sending null clears it. It does not multiply prices
or override a serving option's guest limits. When unknown, the cart returns null.

## Update an existing cart item

Use `PATCH /api/v2/cart/items/{cartItemId}` (PUT is also accepted). Send only
fields to change. `quantity` replaces the current quantity; it is not added.

Fixed-size option:

```json
{
  "quantity": 3,
  "note": "Less spicy, please"
}
```

Per-person catering:

```json
{
  "quantity": 1,
  "guest_count": 20
}
```

For an existing per-person line, quantity may be omitted; it stays 1. Guest count
may also be omitted when changing only the note or event attendance. Sending null
for guest count on a per-person line is rejected.

Change the selected serving option to another option belonging to the same food:

```json
{
  "serving_option_id": 22,
  "guest_count": 20
}
```

IDs here are examples. When switching a fixed option to per-person catering,
quantity defaults to 1 if omitted and guest count must be supplied. When switching
back to a fixed option, omit guest count or send null, and send the desired
quantity. A non-null guest count on a fixed option is rejected.

You may also update `event_guest_count`, or clear it with null. Sending
`"note": null` clears special instructions. At least one supported field must be
provided. Food item and restaurant IDs cannot be changed using this endpoint.
Legacy lines without a serving selection must supply a valid `serving_option_id`;
lines with unsupported add-ons must be removed and added again.

The endpoint checks ownership, current restaurant ordering status, food stock,
option availability, quantity limits, and guest limits. It recalculates the whole
updated line using current menu prices/tax and refreshes its snapshot and labels.
Cart prices, discount amounts, percentages, breakdowns, and caches are updated.
As with add/remove, an existing coupon is cleared after the contents change.

If the updated selection would duplicate another line's food, option, guest
count, and note, a friendly 422 asks the customer to update that line instead.
Selections are not silently merged. Failed updates roll back all changes.

Success is HTTP 200 with the same full cart structure as add:

```json
{
  "success": true,
  "message": "Cart item updated successfully.",
  "data": {}
}
```

The `data` object above is abbreviated; it contains the updated cart and all
its items. Missing or another customer's line returns 404 with the same message.

## Pricing and response example

The server validates restaurant ownership and ordering hours, food availability,
manual menu availability, option availability, and guest limits. Serving configuration comes from
`Modules/FoodManagements/src/Config/serving-options.json`.

Client prices and labels are ignored; addon `selected_options` are rejected.
The effective unit price is the non-null discount price (including zero), or the
regular price when no discount is configured. Both are prices for the same unit:
one guest for `per_person`, one selected option for fixed types.

Example: regular guest price 8.00, discount guest price 2.00, 5 guests, tax 3.97%.
The regular subtotal is 40.00, item saving 30.00, discounted subtotal 10.00,
tax 0.40, and final total 10.40. Cart IDs below are illustrative. A successful
add returns HTTP 201:

```json
{
  "success": true,
  "message": "Item added to cart.",
  "data": {
    "id": 101,
    "session_id": null,
    "restaurant_id": 126,
    "event_guest_count": 50,
    "items": [
      {
        "id": 501,
        "item_id": 15,
        "item_name": "Mini Chicken Tacos",
        "serving_option_id": 11,
        "serving_option": {
          "serve_type": "per_person",
          "serving_name": "25-piece order",
          "quantity": null,
          "unit": null,
          "serves": null,
          "min_guests": 3,
          "max_guests": 8,
          "pricing_description": "Price is for one guest. Portion size per guest is not specified."
        },
        "quantity": 1,
        "guest_count": 5,
        "total_serves": 5,
        "note": "Less spicy, please",
        "requires_serving_selection": false,
        "is_available": true,
        "pricing": {
          "currency": "USD",
          "pricing_basis": "per_person",
          "price_unit": "guest",
          "billable_units": 5,
          "regular_unit_price": "8.00",
          "discounted_unit_price": "2.00",
          "unit_price": "2.00",
          "unit_price_including_tax": "2.08",
          "is_tax_inclusive": false,
          "tax_percentage": "3.97",
          "subtotal_before_discount": "40.00",
          "subtotal": "10.00",
          "tax_amount": "0.40",
          "final_price": "10.40",
          "discount_amount": "30.00"
        },
        "display": {
          "title": "25-piece order",
          "subtitle": "Per guest",
          "price_label": "Special offer",
          "unit_label": "Per guest",
          "unit_price_text": "$2.00",
          "unit_price_tax_label": "Excludes tax",
          "quantity_label": "5 guests",
          "total_serves_label": "Serves 5",
          "subtotal_text": "$10.00",
          "tax_text": "$0.40",
          "total_text": "$10.40"
        }
      }
    ],
    "line_count": 1,
    "counts": {
      "fixed_option_quantity": 0,
      "per_person_selection_count": 1,
      "legacy_line_count": 0
    },
    "subtotal_before_discount": "40.00",
    "subtotal": "10.00",
    "tax_amount": "0.40",
    "item_discount_amount": "30.00",
    "coupon_discount_amount": "0.00",
    "discount_amount": "30.00",
    "discount_percentage": "75.00",
    "discount_breakdown": {
      "summary_version": 1,
      "coupon_code": null,
      "subtotal_before_discount": "40.00",
      "subtotal_after_item_discount": "10.00",
      "item_discount_amount": "30.00",
      "coupon_discount_amount": "0.00",
      "coupon_discount_percentage": null,
      "discount_amount": "30.00",
      "discount_percentage": "75.00"
    },
    "total": "10.40",
    "tax_breakdown": {
      "item_tax": {
        "name": "Item tax",
        "rate": 3.97,
        "amount": 0.4
      }
    }
  }
}
```

### Price meanings

- `unit_price` always means one guest or one fixed option, excluding tax for v2
  serving selections. `price_unit` identifies which.
- `unit_price_including_tax` includes that unit's tax; it is rounded for display.
  Tax is calculated and rounded once on the line subtotal. Do not multiply
  rounded display prices to reconstruct line totals.
- `subtotal_before_discount` is the regular pre-tax price for all billable units.
- `discount_amount` on a line is its pre-tax saving from the serving offer.
- `subtotal` already includes that saving. Do not subtract it again.
- Cart `item_discount_amount` sums the item savings; `coupon_discount_amount`
  is separate. Cart `discount_amount` is their sum.
- `total = subtotal + tax_amount - coupon_discount_amount`, floored at zero.
- The database's cart-level `discount_amount` stores item savings plus any coupon
  saving. `discount_percentage` is the weighted effective saving against the
  regular pre-tax subtotal: total discount / subtotal before discount × 100.
  Both are recalculated and saved when items are added or removed.
- `discount_breakdown` stores the regular subtotal, discounted subtotal, item
  saving, coupon saving, and coupon percentage separately. Only the coupon
  saving is subtracted from already-discounted line totals. Recalculating the
  cart again does not apply the item discount a second time.
- The database's cart-item unit fields retain selection-level amounts for legacy
  consumers; the v2 response resolves the canonical per-guest/per-option unit
  price from the saved quote. Existing checkout repricing still needs conversion.

### Labels, snapshots, and menu names

`serving_option` contains descriptive metadata only. `pricing` contains numeric
prices. `display` provides formatted UI text. The internal saved snapshot is not
returned as a second copy of pricing/display. The UI should render labels from
`display`, and use `pricing` values for calculations.

The serving name is a merchant-entered label. A name such as "25-piece order"
does not establish whether 25 pieces are included per guest or per order. A
per-person option has no structured size/piece definition in the current menu
configuration. The response states that the portion size is unspecified. The
merchant must correct the label or choose a fixed-piece/package option after
confirming the intended offer; the API does not invent portion quantities.

### Cart counters

`line_count` counts cart selections. `counts.fixed_option_quantity` counts
purchased fixed options. `counts.per_person_selection_count` counts per-person
selections. `counts.legacy_line_count` identifies older selections without a
serving snapshot. None represents unique guests or a total number of packages.
There is no mixed `total_quantity` field and no sum of guest coverage across
foods. Event attendance is exclusively `event_guest_count` when supplied.

## Cart behavior

- Same fixed food + serving option + note increases the existing line quantity.
- Same per-person food + serving option + guest count + note keeps quantity 1.
- Different notes/options/guest counts create separate selections.
- Repeating an add refreshes that line from current menu prices and labels.
- GET preserves the saved quote even after menu prices/names change.
  `is_available` reflects current food/option availability, including deletion.
- Mutations clear an existing coupon so it cannot retain a discount calculated
  for old contents. Coupon application is outside these v2 endpoints.
- Removing the last line deletes the cart and its event context. The empty
  response has null IDs, empty items, zero counters, and zero totals.
- GET without an existing cart returns an empty cart with the requested restaurant ID.
- Legacy lines return `requires_serving_selection: true` and are never merged
  into a serving-aware line.
- Default limits: 100 fixed options per line and 50 selections per cart.

Use v2 consistently for serving selections. V1 add/update/guest-merge and checkout
repricing still use food-level prices. Checkout must validate serving selections
before placing orders.

## Compatibility changes

The corrected v2 contract replaces these ambiguous fields:

| Previous response field | Corrected field |
| --- | --- |
| `total_quantity` | `counts` with separate counters |
| `pricing.base_price` | `pricing.regular_unit_price` |
| `pricing.discount_price` | `pricing.discounted_unit_price` |
| `pricing.unit_price_used` | `pricing.unit_price` |
| `pricing.unit_display_price` | `pricing.unit_price_including_tax` |
| `serving_option` financial/display fields | Only in item `pricing` / `display` |
| `display.per_person_price` | Use `pricing.unit_price` for per-person selections |
| Cart `discount_amount` as coupon-only | Combined saving, with explicit item/coupon fields |

Update clients to send quantity 1 for per-person options. Group multiplication
is no longer supported.

## Errors

200: retrieved/removed; 201: added; 400: malformed JSON; 401: invalid customer
authorization; 404: resource missing or another customer's/session's line;
422: missing/invalid guest session, invalid selection, or limits.

The top-level message is the first actionable error; field errors remain under
`errors`. Example for an option allowing at most 8 guests:

```json
{
  "success": false,
  "message": "This serving option supports up to 8 guests. Please reduce the guest count.",
  "errors": {
    "guest_count": [
      "This serving option supports up to 8 guests. Please reduce the guest count."
    ]
  }
}
```

## Database deployment

Apply these module migrations in order:

```bash
php artisan migrate --path=Modules/Cart/src/Database/Migrations
```

The initial serving-field migration adds cart-item snapshots and display fields.
The next migration adds nullable `carts.event_guest_count`.

## Inventory behavior

Food inventory quantities are not managed. Placing an order does not reserve or
decrease food stock, and cancellation, payment failure, or refund does not
increase it. Saved food quantity does not cap fixed-option purchases. Manual
menu availability (`is_active`, `is_available`, and `in_stock`), positive order
quantities, serving limits, and legacy grouping minimum guests still apply.
