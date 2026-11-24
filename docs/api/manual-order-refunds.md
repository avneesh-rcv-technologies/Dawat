# Manual cancellation refunds

Cancellation creates a refund request for each captured order payment with money remaining to refund. Unpaid, pending, failed and authorized payments do not qualify. Requests are unique per order/payment, so retries cannot duplicate a request. Existing negative refund payment rows are deducted from the refundable amount.

Each request receives a unique public refund number such as `RFN-20261120-A1B2C3D4`. Titles, grids, notifications and API responses use this number, while internal IDs continue to identify routes and relationships. Refund transactions use the existing short `TXN-YYYYMMDD-XXXXXXXX` format. The number migration backfills existing requests and shortens legacy UUID refund transaction numbers, retaining the old transaction number in metadata for audit.

This flow records refunds performed externally. It does not call Stripe or move money. `stripe_refund_id` accepts either a Stripe refund ID or a reference for a bank/cash/manual refund.

## Install

Apply the two new migrations and install the new notification template:

```sh
php artisan migrate --path=Modules/Payments/src/Database/Migrations/2026_11_20_000000_support_partial_refund_order_payment_status.php --path=Modules/Payments/src/Database/Migrations/2026_11_20_000001_create_refunds_table.php --path=Modules/Payments/src/Database/Migrations/2026_11_20_000002_add_refund_numbers.php
php artisan db:seed --class='Modules\Payments\Database\Seeders\RefundNotificationSeeder'
```

The first migration widens the legacy order payment-status enum to a string so it can store `partially_refunded`. Rollback intentionally preserves this widened column to avoid losing recorded statuses. `processed_by` and history actors reference `admins`, the application's owner/staff table.

## Web

Open **Refunds** in the sidebar, or **Refund requests** on an order. Owners can only view orders belonging to their restaurants and cannot update refunds, even if their role has a refund update permission. Only platform admins can process requests. Staff require `admin.refunds.index` to read and `admin.refunds.update` to process requests. Grant these permissions through role settings where needed.

The refund list uses the shared DataView grid, including order-number search, column sorting, pagination and filters for status, source, restaurant and creation date. The API also retains the optional `order_id` filter for existing integrations.

Enter an amount, status, refund reference and internal notes. Processing and succeeded requests require a refund reference. Amounts must be positive, have at most two decimal places, and cannot exceed the remaining captured payment. References are unique. Succeeded requests are final; identical repeated submissions have no additional effect.

Allowed transitions:

| Current | Next |
| --- | --- |
| pending | pending, processing, succeeded, failed, rejected |
| processing | processing, succeeded, failed, rejected |
| failed | failed, processing, succeeded, rejected |
| rejected | rejected, pending |
| succeeded | succeeded (identical retry only) |

## Customer API

Use the existing authenticated cancellation endpoint:

```http
POST /api/orders/9/cancel
Content-Type: application/json

{"reason":"Please cancel this order"}
```

Its `data` includes `payment_status`, the latest request's `refund_status` (or null if nothing was paid), and `refunds`. The requested refund amount initially equals the remaining captured payment amount. Cancellation does not immediately change a paid payment into a refunded payment.

```http
GET /api/orders/9/refunds
GET /api/orders/9
```

Both expose customer-safe refund details. Customers can access only their own orders. Internal notes, processor identity, and internal audit snapshots are excluded.

## Owner/staff API

Use an authenticated admin Sanctum token. Customer tokens cannot manage refunds. Restaurant scope and staff permissions are identical to the web screens.

```http
GET /api/payment/refunds?status=pending&order_number=ORD-2026&per_page=20&page=1
GET /api/payment/refunds/1
PATCH /api/payment/refunds/1
Content-Type: application/json

{
  "refund_amount": "25.50",
  "refund_status": "succeeded",
  "stripe_refund_id": "re_external_refund_reference",
  "admin_notes": "Confirmed in gateway dashboard"
}
```

Requests must include `Authorization: Bearer <admin-token>` and `Accept: application/json`. PATCH requests also require `Content-Type: application/json`.

List filters: `status`, `order_id`, `order_number` (partial), `refund_number` (partial), `page`, and `per_page` (1–100).

GET details and PATCH responses include `meta.can_update` and `meta.allowed_statuses` for the acting owner/staff member. Restaurant owners always receive `can_update: false` and an empty `allowed_statuses` list; PATCH requests return 403. Succeeded refunds are final and report no available edits.

PATCH supports partial updates: omitted amounts, references and notes retain their current values. Explicit `null` clears an optional reference or note, subject to status validation. To change only the status, use:

```http
PATCH /api/payment/refunds/1/status
Authorization: Bearer <admin-token>
Accept: application/json
Content-Type: application/json

{
  "refund_status": "processing",
  "stripe_refund_id": "re_external_refund_reference"
}
```

If a reference is already saved, the body can contain only `refund_status`. The standard `PATCH /api/payment/refunds/1` accepts any combination of `refund_amount`, `refund_status`, `stripe_refund_id`, and `admin_notes`; at least one must be supplied.

Management responses include payment/transaction summaries and internal change history. Gateway configuration and credentials are never serialized. Validation failures return 422; cross-restaurant records return 404; unauthorized access returns 403.

## Accounting, audit and notifications

Each request creates one refund transaction, which is updated throughout its lifecycle. Its generic transaction status is pending for pending/processing refunds, completed for succeeded, failed for failed, and declined for rejected. Metadata retains the exact refund status. Original payment transactions and original paid amounts remain intact.

Only succeeded requests count toward refunded totals. A full payment refund marks the original payment `refunded`; a partial refund marks it `partially_refunded`. The order remains Cancelled (`C`) for both full and partial refunds. Only its payment status changes to `refunded` or `partially_refunded`. Cancellation transactions and timestamps remain intact; refund processing is recorded separately in the refund transaction and status histories. Pending, processing, failed and rejected requests do not reduce paid totals. A partial succeeded request is final; the remaining balance is not automatically submitted as another refund.

Every saved change creates an audit snapshot of the refund amount, reference, notes, actor, order status, order payment status, and payment status. The web order timeline and refund detail screen display these histories. Refund transactions must be updated through their refund request, rather than generic transaction status controls.

After a committed status change, the `refund_status_updated` notification uses the existing notification dispatcher and seeded customer email/web-push mappings. Notification/channel failures are logged without rolling back accounting. Internal notes are not included in customer notifications. Order caches are invalidated after commit.
