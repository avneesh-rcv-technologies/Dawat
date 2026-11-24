# Restaurant transaction list and KPI APIs

Base: `{{base_url}}/api/restaurant/transactions`

## Authentication and scope

```http
Authorization: Bearer <restaurant_owner_token>
Accept: application/json
```

The same owner requirements as restaurant orders apply: active, email-verified
Admin with a draft/active restaurant. The owner's first restaurant by ID is used.
Queries require the linked order's vendor type and vendor ID to match that
restaurant. There is no super-admin fallback to all transactions. Orphaned
transactions and other restaurants' transactions are excluded. These APIs are
read-only and do not modify payments, transactions or order statuses.

Existing admin sessions are also supported. Responses are private and not cached.

## Endpoints

| Method | URL | Body |
|---|---|---|
| GET | `/api/restaurant/transactions` | None |
| GET | `/api/restaurant/transactions/kpis` | None |

## Filters

Both endpoints accept the following optional query parameters. KPIs apply the
same filters across all matching transactions; page/sort parameters do not limit
the aggregation. No default date restriction is applied.

| Parameter | Meaning |
|---|---|
| `search` | Transaction number, gateway reference or linked order number; max 255 characters |
| `type` | `payment`, `refund`, `capture`, `authorization`, `adjustment`, `chargeback` |
| `status` | Transaction status: `pending`, `completed`, `failed`, `voided`, `declined`, legacy `reversed` |
| `order_id` | Exact linked order ID, still restricted to the owner |
| `payment_id` | Exact linked payment ID |
| `customer_id` | Linked order's customer ID |
| `order_status` | Linked order status: `W`, `O`, `P`, `H`, `Z`, `N`, `D`, `C`, `F`, `R` |
| `payment_status` | Linked order's payment status: `pending`, `unpaid`, `paid`, `completed`, `captured`, `failed`, `refunded`, `partially_refunded`, `cancelled`, `authorized`, `processing` |
| `payment_method` | Exact payment configuration ID recorded on the transaction's payment; falls back to the order's stored method only when `payment_id` is null |
| `order_type` | `delivery`, `pickup`, `takeaway`, `dine_in`; pickup maps to takeaway |
| `currency` | Three-character code, normalized to uppercase |
| `date_from`, `date_to` | Inclusive transaction creation dates, `YYYY-MM-DD` |
| `processed_from`, `processed_to` | Inclusive processing dates; unprocessed records do not match |
| `min_amount`, `max_amount` | Numeric transaction amount bounds; negative amounts are supported for historical adjustments |
| `sort_by` | `id`, `transaction_number`, `created_at` (default), `processed_at`, `amount`, `status`, `type` |
| `sort_dir` | `desc` (default), `asc` |
| `page` | Default 1; positive integer |
| `per_page` | Default 15; 1–100 |

Dates use the database's stored date values. End dates cannot precede start dates,
and `max_amount` cannot be below `min_amount`. Upper bounds work independently.

## List example

```http
GET {{base_url}}/api/restaurant/transactions?search=TXN&type=payment&status=completed&currency=USD&date_from=2026-11-01&date_to=2026-11-19&page=1&per_page=15&sort_by=created_at&sort_dir=desc
```

Illustrative response **200**:

```json
{
  "success": true,
  "message": "Transactions retrieved successfully.",
  "data": [
    {
      "id": 51,
      "transaction_number": "TXN-EXAMPLE",
      "type": "payment",
      "type_label": "Payment",
      "status": "completed",
      "status_label": "Completed",
      "amount": "425.25",
      "currency": "USD",
      "amount_formatted": "USD 425.25",
      "gateway_reference": "COD-ORDER-27",
      "notes": "Cash received",
      "processed_at": "2026-11-18T16:46:00+05:30",
      "created_at": "2026-11-18T16:45:00+05:30",
      "updated_at": "2026-11-18T16:46:00+05:30",
      "order": {
        "id": 27,
        "order_number": "ORD20261118-AWOUPT",
        "status": "D",
        "status_name": "Delivered",
        "order_type": "takeaway",
        "payment_status": "paid",
        "total": "425.25",
        "currency": "USD"
      },
      "customer": {"id": 12, "name": "Dev", "email": "dev@example.com", "phone": "1234567890"},
      "restaurant": {"id": 397, "name": "Test Kitchen", "email": "kitchen@example.com", "phone": "1234567890"},
      "payment": {
        "id": 44,
        "payment_method_id": 2,
        "status": "completed",
        "payment_type": "order_payment",
        "amount": "425.25",
        "currency": "USD",
        "failure_reason": null,
        "processed_at": "2026-11-18T11:16:00.000000Z"
      },
      "payment_method": {"id": 2, "name": "Cash on Delivery", "code": "cod"},
      "cash_collection": {
        "collected_by": 12,
        "collected_by_name": "Restaurant Owner",
        "collected_at": "2026-11-18T16:46:00+05:30",
        "notes": "Cash received"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1,
    "has_more_pages": false
  },
  "filters": {
    "search": "TXN",
    "type": "payment",
    "status": "completed",
    "currency": "USD",
    "date_from": "2026-11-01",
    "date_to": "2026-11-19",
    "page": "1",
    "per_page": "15",
    "sort_by": "created_at",
    "sort_dir": "desc"
  }
}
```

An empty match returns `data: []` and zero pagination totals. Optional customer,
payment, method and cash-collection objects may be null. A payment linked to a
different order is never exposed. Payment configuration credentials, arbitrary
gateway response details and arbitrary transaction metadata are not serialized.
Fetch full order/item details through `GET /api/restaurant/orders/{order.id}`.

## KPI example

```http
GET {{base_url}}/api/restaurant/transactions/kpis?date_from=2026-11-01&date_to=2026-11-19&currency=USD
```

Illustrative response **200**:

```json
{
  "success": true,
  "message": "Transaction KPIs retrieved successfully.",
  "data": {
    "total_transactions": 9,
    "currencies": [
      {
        "currency": "USD",
        "total_transactions": 9,
        "completed_payments": {"count": 9, "amount": "2976.75", "formatted": "USD 2,976.75"},
        "completed_refunds": {"count": 0, "amount": "0.00", "formatted": "USD 0.00"},
        "completed_chargebacks": {"count": 0, "amount": "0.00", "formatted": "USD 0.00"},
        "pending_payments": {"count": 0, "amount": "0.00", "formatted": "USD 0.00"},
        "failed_payments": {"count": 0, "amount": "0.00", "formatted": "USD 0.00"},
        "net_payments": "2976.75",
        "net_payments_formatted": "USD 2,976.75",
        "by_status": [
          {"status": "completed", "count": 9, "amount": "2976.75", "formatted": "USD 2,976.75"}
        ],
        "by_type": [
          {"type": "payment", "count": 9, "amount": "2976.75", "formatted": "USD 2,976.75"}
        ]
      }
    ]
  },
  "filters": {"date_from": "2026-11-01", "date_to": "2026-11-19", "currency": "USD"}
}
```

Definitions:

- Total Transactions counts matching rows, including repeated/failed attempts. It
  is not an order count.
- Completed Payments includes only `type=payment, status=completed` rows.
- Completed Refunds/Chargebacks include completed rows of their respective types.
- Pending Payments includes pending payment attempts.
- Failed Payments includes failed and declined payment attempts.
- Net Payments = completed payments − completed refunds − completed chargebacks.
  Refund/chargeback amounts are expected to be positive, as in the existing ledger.
- Captures, authorizations and adjustments are available in `by_type`; they are
  excluded from Net Payments to avoid counting a payment and its capture twice.
  This figure is not an accounting settlement balance for capture-only gateways.
- `by_status` and `by_type` sums describe all matching rows, including attempts and
  different transaction types; their amounts should not be treated as revenue.
- Each currency has independent amounts. No conversion or cross-currency sum is
  performed. Filtering to `type=payment`, for example, also filters refunds out of
  the KPI calculation; omit type/status filters for the complete period picture.

Empty result: `{"total_transactions":0,"currencies":[]}` under `data`.

## Errors and verification

401: unauthenticated. 403: owner account is not eligible. 404: no eligible restaurant.
422: invalid filter, range or pagination. Errors follow the existing restaurant
order API envelope with `success: false`, `message`, and optional `errors`.

```bash
vendor/bin/phpunit tests/Feature/RestaurantTransactionsApiTest.php
```

Tests cover authentication, pagination, filter validation, query ownership,
payment-link consistency, safe response fields, currency separation, refunds and
capture exclusion. They use mocked MySQL query execution and do not query or modify
live financial records. Module routes are explicitly loaded in the test bootstrap.
