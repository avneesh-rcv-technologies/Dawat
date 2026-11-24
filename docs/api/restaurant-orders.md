# Restaurant orders API

Base URL: `{{base_url}}/api/restaurant/orders`

These APIs belong to the Restaurant module and manage orders owned by the signed-in
restaurant. They use the existing order workflow, presentation and cash collection
services. No migration is needed.

## Authentication

```http
Authorization: Bearer <restaurant_owner_token>
Accept: application/json
Content-Type: application/json
```

An active, email-verified Admin account with a draft or active restaurant is required.
The owner's first restaurant by ID is selected, consistently with the restaurant
V2 food-item API. Every order lookup includes both vendor type and restaurant ID.
An order belonging to another restaurant returns 404. Supplying a restaurant ID
does not change the authenticated restaurant. Existing admin sessions also work;
session-based writes require the CSRF token.

Responses have `Cache-Control: private, no-store`.

## Endpoints

| Method | Path | Purpose |
|---|---|---|
| GET | `/` | Filtered, paginated orders with full details |
| GET | `/{id}` | Full order details |
| GET | `/kpis` | KPIs using the same filters, across all matching pages |
| GET | `/statuses?order_type=delivery` | Workflow/status catalogue for the type |
| GET | `/statuses?order_id=27` | Status options checked against this order's payment and transaction state |
| GET | `/{id}/next-statuses` | Only the allowed next statuses for this order |
| PATCH | `/{id}/status` | Update the order status |
| POST | `/{id}/collect-cash` | Record COD collection through the web UI's cash service |
| DELETE | `/{id}` | Delete an eligible unpaid order |

`pickup` is accepted as an input alias for the persisted order type `takeaway`.

## List and filters

```http
GET {{base_url}}/api/restaurant/orders?search=ORD&order_type=delivery&status=O&payment_status=paid&date_from=2026-11-01&date_to=2026-11-19&page=1&per_page=15&sort_by=created_at&sort_dir=desc
```

No request body. Every filter is optional; no date restriction is applied by default.

| Parameter | Values / behavior |
|---|---|
| `search` | Order number or customer name, email, phone; max 255 characters |
| `status` | `W`, `O`, `P`, `H`, `Z`, `N`, `D`, `C`, `F`, `R` |
| `payment_status` | `pending`, `unpaid`, `paid`, `completed`, `captured`, `failed`, `refunded`, `partially_refunded`, `cancelled`, `authorized`, `processing` |
| `transaction_status` | Has at least one transaction in `pending`, `completed`, `failed`, `reversed`, `declined`, or `voided`; historical transactions also match |
| `payment_method` | Exact stored payment method/configuration ID as a query string |
| `order_type` | `delivery`, `pickup`, `takeaway`, `dine_in` |
| `customer_id` | Positive customer ID within this restaurant's orders |
| `currency` | Three-character code, normalized to uppercase |
| `date_from`, `date_to` | Inclusive creation dates in `YYYY-MM-DD`; dates use the database's stored creation date |
| `min_total`, `max_total` | Nonnegative order total bounds |
| `sort_by` | `id`, `created_at` (default), `updated_at`, `total`, `order_number`, `status` |
| `sort_dir` | `desc` (default), `asc` |
| `page` | Positive integer, default 1 |
| `per_page` | 1–100, default 15 |

`date_to` cannot precede `date_from`; `max_total` cannot be below `min_total`.
Either upper bound can be supplied alone.

Response: **200**. `data` is an array of the full order objects described below.

```json
{
  "success": true,
  "message": "Orders retrieved successfully.",
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 0,
    "last_page": 1,
    "has_more_pages": false
  },
  "filters": {}
}
```

An empty match returns an empty array, not 404. `filters` echoes validated inputs.

## Detail response

```http
GET {{base_url}}/api/restaurant/orders/27
```

Response: **200**, with `success: true`, message `Order retrieved successfully.`,
and one full order object under `data`. The listing uses this same object shape.
Illustrative order data (IDs, amounts and dates are examples):

```json
{
  "id": 27,
  "order_number": "ORD20261118-AWOUPT",
  "status": "D",
  "status_name": "Delivered",
  "order_type": "takeaway",
  "order_type_label": "Pickup",
  "currency": "USD",
  "customer": {"id": 12, "name": "Dev", "email": "dev@example.com", "phone": "1234567890"},
  "restaurant": {"id": 397, "name": "Test Kitchen", "email": "kitchen@example.com", "phone": "1234567890", "address_line1": null, "address_line2": null, "city": null, "state": null, "zip_code": null, "addresses": []},
  "delivery_address": {},
  "billing_address": {},
  "phone_number": "1234567890",
  "delivery_instructions": null,
  "notes": null,
  "coupon_code": null,
  "items_count": 1,
  "quantity_total": 9,
  "items": [
    {
      "id": 31,
      "item_id": 15,
      "item_type": "Modules\\FoodManagements\\Models\\FoodItem",
      "name": "Chicken Francaise",
      "item_name": "Chicken Francaise",
      "sku": null,
      "image": null,
      "categories": [{"id": 17, "name": "Chicken Entrees", "parent_name": null}],
      "quantity": 9,
      "price": "45.00",
      "unit_price": "45.00",
      "total_price": "405.00",
      "subtotal": "405.00",
      "serving_option_id": 3,
      "serve_type": "tray",
      "serving_name": "Half tray",
      "guest_count": null,
      "selected_option_snapshot": {
        "serving_name": "Half tray", "quantity": "0.5", "unit": "tray", "serves": 8, "pricing_basis": "per_order"
      },
      "tax_rate": "5.00",
      "tax_amount": 20.25,
      "discount_amount": "0.00",
      "final_price": "425.25",
      "is_tax_inclusive": false,
      "tax_breakdown": [],
      "delivery_fee": "0.00",
      "estimated_delivery_minutes": null,
      "estimated_delivery_at": null,
      "selected_options": [],
      "special_instructions": "Less spicy, please",
      "serving_summary": {
        "quantity_per_option": "0.5", "unit": "tray", "serves_per_option": 8,
        "quantity_ordered": 9, "total_serves": 72, "pricing_basis": "per_order"
      },
      "price_breakdown": {
        "unit_price": "45.00", "subtotal": "405.00", "discount": "0.00",
        "tax_rate": "5.00", "tax": "20.25", "total": "425.25",
        "delivery_fee": "0.00", "is_tax_inclusive": false
      }
    }
  ],
  "price_breakdown": {
    "subtotal": 405, "tax": 20.25, "delivery_fee": 0, "service_charge": 0,
    "discount": 0, "total": 425.25, "is_tax_inclusive": false,
    "tax_breakdown": [], "discount_breakdown": []
  },
  "fulfilment": {
    "currency_code": "USD", "event_guest_count": null,
    "delivery_distance_miles": null, "estimated_delivery_minutes": null,
    "estimated_delivery_time": null, "is_free_delivery": false,
    "delivery_policy_snapshot": null,
    "actual_delivery_time": "2026-11-18T16:46:00+05:30",
    "cancelled_at": null, "cancellation_reason": null
  },
  "payment": {
    "method": "2", "method_name": "Cash on Delivery", "status": "paid", "is_cod": true,
    "cash_collection": {
      "amount": "425.25", "currency": "USD",
      "collected_at": "2026-11-18T16:46:00+05:30",
      "collected_by_name": "Restaurant Owner", "notes": "Cash received"
    },
    "records": [
      {"id": 44, "transaction_id": 51, "payment_type": "order_payment", "amount": "425.25", "currency": "USD", "status": "completed", "gateway_reference": "COD-ORDER-27", "failure_reason": null, "processed_at": "2026-11-18T11:16:00.000000Z", "created_at": "2026-11-18T11:15:00.000000Z"}
    ]
  },
  "transactions": [
    {"id": 51, "transaction_number": "TXN-EXAMPLE", "payment_id": 44, "type": "payment", "amount": "425.25", "currency": "USD", "status": "completed", "gateway_reference": "COD-ORDER-27", "notes": "Cash received", "processed_at": "2026-11-18T11:16:00.000000Z", "created_at": "2026-11-18T11:15:00.000000Z"}
  ],
  "available_statuses": [
    {"code": "D", "key": "delivered", "name": "Delivered", "color": "success", "is_current": true, "can_transition": false, "restriction_reason": null}
  ],
  "actions": {
    "can_update_status": false, "can_delete": false,
    "delete_restriction_reason": "Only unpaid orders with pending (W), cancelled (C), or failed (F) status can be deleted.",
    "can_collect_cash": false, "refund_review_required": false
  },
  "created_at": "2026-11-18T16:45:00+05:30",
  "updated_at": "2026-11-18T16:46:00+05:30"
}
```

Money uses the existing order presentation conventions: decimal strings on stored
item/payment fields, JSON numbers on order-level breakdowns. KPI amounts are decimal
strings. Null values are retained when historical information is unavailable.
Serving selections/prices come from saved order snapshots; categories/images may
reflect the current food item and can be unavailable if it was deleted.

## Status catalogue and permitted changes

```http
GET {{base_url}}/api/restaurant/orders/statuses?order_type=delivery
GET {{base_url}}/api/restaurant/orders/statuses?order_type=pickup
GET {{base_url}}/api/restaurant/orders/statuses?order_id=27
```

No body. Response **200**:

```json
{
  "success": true,
  "message": "Order statuses retrieved successfully.",
  "data": {
    "order_id": null,
    "order_type": "takeaway",
    "statuses": [
      {"code": "W", "key": "pending", "name": "Pending", "color": "warning", "is_current": false, "can_transition": null, "restriction_reason": null}
    ]
  }
}
```

The example shows one entry; the actual catalogue includes the full applicable
workflow and terminal statuses. Without `order_id`, `can_transition` is null because
there is no order/payment context. With `order_id`, the order's actual type wins,
`is_current` identifies its current status, and each status has a boolean
`can_transition` and an optional `restriction_reason`. To build an update dropdown,
show entries where `is_current` or `can_transition` is true. Detail/list responses
already contain this filtered list in `available_statuses`.

| Code | Status |
|---|---|
| W | Pending |
| O | Order Received |
| P | Order Accepted |
| H | Preparing |
| Z | Ready |
| N | Out for Delivery (delivery only) |
| D | Delivered (also the existing completion label for pickup) |
| C | Cancelled |
| F | Failed |
| R | Refunded |

Normal delivery flow: `W → O → P → H → Z → N → D`.
Pickup/dine-in flow: `W → O → P → H → Z → D`.
From a nonterminal state, the existing workflow also offers C/F/R, subject to the
financial checks below. D/C/F/R are closed; requesting the current status is a
successful no-op. Skipping steps or reopening a closed order returns 422.

## Allowed next statuses by order ID

```http
GET {{base_url}}/api/restaurant/orders/27/next-statuses
```

Requires the restaurant owner token. No request body or `order_type` parameter is
needed: the order's stored type, current status, payment state and transactions
determine the result. Another restaurant's order returns 404.

Example **200** for a fully paid delivery order at Ready (`Z`):

```json
{
  "success": true,
  "message": "Allowed next order statuses retrieved successfully.",
  "data": {
    "order_id": 27,
    "order_type": "delivery",
    "current_status": "Z",
    "current_status_name": "Ready",
    "payment_status": "paid",
    "can_update_status": true,
    "statuses": [
      {"code": "N", "key": "out_for_delivery", "name": "Out for Delivery", "color": "info", "is_current": false, "can_transition": true, "restriction_reason": null},
      {"code": "C", "key": "cancelled", "name": "Cancelled", "color": "danger", "is_current": false, "can_transition": true, "restriction_reason": null}
    ]
  }
}
```

For an unpaid COD order at Ready, Failed (`F`) may also be permitted. For pickup at
Ready, the normal next status is Delivered (`D`), without Out for Delivery (`N`).
Current and forbidden statuses are excluded. Closed orders return `statuses: []`
and `can_update_status: false`. Use a returned `code` in the existing
`PATCH /api/restaurant/orders/{id}/status` request; that endpoint checks eligibility
again at update time, so stale selections can still return 422.

## Update order status

```http
PATCH {{base_url}}/api/restaurant/orders/27/status
```

```json
{"status": "P"}
```

Response **200**: `success: true`, message `Order status updated successfully.`,
and the full refreshed order object under `data`.

Payment/transaction restrictions:

- Online orders with a positive total require a paid/completed/captured order
  payment status and completed payment transactions, less completed refunds,
  covering the total in the same currency before fulfilment advances.
- COD can advance while unpaid/pending. Delivery completion does not fabricate
  a payment or automatically collect cash.
- Failed, cancelled, refunded or partially refunded payment states block fulfilment.
- An order with settled funds cannot be marked failed.
- Cancellation (`C`) and manual refund completion (`R`) require `confirmed: true`.
  The client must show a confirmation dialog before sending this flag.
- `C` cancels the order and creates pending refund requests/transactions for captured
  payments. An unpaid order creates no refund. Payment status stays unchanged until
  the manual refund is recorded as succeeded.
- `R` is restricted to authorized platform admins; restaurant owners receive 403 and must use `C` to request admin processing. It means cancel and record an **already completed manual refund**, and requires
  `stripe_refund_id` (a gateway or bank refund reference). It creates/updates the
  refund ledger, refund transaction and payment statuses atomically. It does not
  call a gateway. The order remains `C`, preserving its cancellation details and
  transaction; refund history is recorded separately.
- Orders with multiple open payment refunds must be processed individually through
  the refund APIs; this single-reference action returns 422 without changing them.
- Clients cannot set `payment_status` through the status endpoint.

The order, payment records and transactions are locked during mutations, and the
transition is checked again against the locked state. Completing delivery records
`actual_delivery_time` through the existing OrderService.

Order status notifications use the existing `Orders/OrderStatusUpdate` notification.
They run after the outer transaction commits. Notification failures, including
Throwable errors and false return values, are logged without undoing the saved
status or turning the update into an API failure. No status-change notification is
sent for a no-op. This also protects web UI status updates using the same service.

### Confirm cancellation or completed manual refund

```http
PATCH {{base_url}}/api/restaurant/orders/2/status
Accept: application/json
Content-Type: application/json
```

Cancel and create a pending refund request if money was captured:

```json
{"status":"C","confirmed":true,"cancellation_reason":"Restaurant cannot fulfil this order"}
```

Platform admin only: cancel and record a refund already completed outside the app (restaurant owner tokens return 403):

```json
{"status":"R","confirmed":true,"stripe_refund_id":"re_external_reference","cancellation_reason":"Restaurant cannot fulfil this order","admin_notes":"Refund recorded manually"}
```

Unconfirmed actions or missing refund references return 422 without changes. Repeating
an already completed refund with the same reference returns the current order without
duplicating transactions or histories. Failed actions roll back their cancellation.
The response includes `refunds` with refund numbers, status, amount and reference.
Status catalogue entries provide `requires_confirmation` and `requires_refund_reference`
so clients can configure the dialog. An order without captured funds cannot use `R`.

## Record COD collection

```http
POST {{base_url}}/api/restaurant/orders/27/collect-cash
```

```json
{"notes": "Cash received"}
```

`notes` is optional, max 2000 characters. Response **200** with message
`Cash payment collected successfully.` and the full order under `data`.

This uses `CodPaymentService`, including its payment/transaction reconciliation,
owner audit information, idempotency, cache refresh and caught after-commit receipt
notifications. Online, cancelled, failed and refunded orders cannot collect cash.
COD must resolve to a configured COD payment method. Conflicting/multiple payment
records are rejected for review. Repeating a successful collection creates no
duplicate transaction.

## Delete restrictions

```http
DELETE {{base_url}}/api/restaurant/orders/27
```

No body. Both the exact web model restriction and financial-record safeguards apply:

- Status must be W, C or F.
- Order `payment_status` must be exactly `unpaid`. `pending` is not the same value.
- No completed transaction may exist.
- No completed/paid/captured/authorized/processing/refunded/partially-refunded
  payment record may exist.

Response **200**:

```json
{"success": true, "message": "Order deleted successfully.", "data": {"id": 27}}
```

Database foreign-key cascades delete dependent items, payments and transactions.
An ineligible order returns 422 with an `errors.order` explanation. Order list/detail
responses provide `actions.can_delete` and `actions.delete_restriction_reason`.

## KPIs

```http
GET {{base_url}}/api/restaurant/orders/kpis?date_from=2026-11-01&date_to=2026-11-19&currency=USD
```

All listing filters also apply here. Pagination and sorting do not limit the
aggregate. KPIs default to all matching historical orders, so pass explicit dates
for a dashboard range. Amounts are grouped by currency; different currencies are
never summed into one revenue number.

Illustrative response **200**:

```json
{
  "success": true,
  "message": "Order KPIs retrieved successfully.",
  "data": {
    "total_orders": 9,
    "currencies": [
      {
        "currency": "USD",
        "total_orders": 9,
        "gross_order_value": "2976.75",
        "total_revenue": "2976.75",
        "total_revenue_formatted": "USD 2,976.75",
        "paid_payments": {"count": 9, "amount": "2976.75", "formatted": "USD 2,976.75"},
        "failed_payments": {"count": 0, "amount": "0.00", "formatted": "USD 0.00"},
        "pending_payments": {"count": 0, "amount": "0.00", "formatted": "USD 0.00"},
        "refunded_payments": {"count": 0, "amount": "0.00", "formatted": "USD 0.00"},
        "partially_refunded_payments": {"count": 0, "amount": "0.00", "formatted": "USD 0.00"},
        "cancelled_payments": {"count": 0, "amount": "0.00", "formatted": "USD 0.00"},
        "average_paid_order_value": "330.75"
      }
    ],
    "by_status": [{"code": "D", "name": "Delivered", "count": 9}],
    "by_order_type": [{"order_type": "takeaway", "count": 9}]
  },
  "filters": {"date_from": "2026-11-01", "date_to": "2026-11-19", "currency": "USD"}
}
```

KPI definitions match the web dashboard's order-total approach, not the sum of
payment attempts:

- Total Orders: count of filtered orders.
- Total Revenue / Paid Payments: sum of order totals whose current payment status
  is paid/completed/captured; these two cards intentionally share an amount.
- Failed Payments: order totals currently marked failed.
- Pending Payments: order totals currently pending/unpaid/processing/authorized.
- Refunded, Partially Refunded, Cancelled Payments: full order totals grouped by
  current payment state. Partial-refund amounts are not inferred from these totals;
  use the order's transaction records for actual refund amounts.
- Gross Order Value: all filtered order totals, including unpaid/failed orders.
- Average Paid Order Value: paid amount divided by paid order count.

An empty result returns `total_orders: 0`, `currencies: []`, `by_status: []`, and
`by_order_type: []`. The client can render zero cards in its selected currency.

## Errors and verification

| HTTP | Meaning |
|---|---|
| 401 | Missing/invalid authentication |
| 403 | Inactive/unverified account or non-owner account type |
| 404 | No eligible restaurant, missing order, or order owned by another restaurant |
| 422 | Invalid filter, forbidden transition, payment restriction or deletion restriction |
| 500 | Unexpected server/database failure; notification failures alone do not cause this |

Example restriction response:

```json
{
  "success": false,
  "message": "The online payment must be fully completed before progressing the order.",
  "errors": {"status": ["The online payment must be fully completed before progressing the order."]}
}
```

Run focused checks:

```bash
vendor/bin/phpunit tests/Unit/RestaurantOrderPolicyTest.php tests/Unit/OrderStatusNotificationTest.php tests/Feature/RestaurantOrdersApiTest.php
```

Tests cover HTTP validation/pagination/details, owner query scoping, payment and
deletion restrictions, pickup/delivery workflows, deferred notification failures,
and currency-separated KPI calculations. HTTP tests load the module routes
explicitly because the minimal test checkout does not discover enabled module
providers. Database queries use a mocked MySQL connection; tests do not modify live
orders or send notifications. A real database concurrency/integration test remains
an environment-dependent deployment check.
