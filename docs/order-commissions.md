# Paid-order commissions

`order_commissions` holds one financial snapshot per order (`order_id` is unique). It records the restaurant, accepted commission percentage, commissionable amount, platform fee, restaurant share, currency, policy reference/snapshot and calculation time. Processing changes are retained in `order_commission_status_histories`.

Commission is calculated from the final order total, matching the existing acceptance agreement (“every order amount”). For example, USD 100.00 at an accepted rate of 10% records USD 10.00 platform commission and USD 90.00 restaurant share. Calculations round to cents, with both shares adding back to the original amount.

A record is created only when:

- The order is paid, completed or captured and is not cancelled, failed or refunded.
- Successful order-payment rows cover the order total.
- The vendor is a restaurant with `platform_commission_accepted = true` and a recorded percentage between 0 and 100.

Payment and order observers run the bookkeeping callback after the payment transaction commits. This covers payment completion at checkout, Stripe return/webhook confirmation and manual COD collection. Checkout orders awaiting payment do not accrue commission. Repeated callbacks reuse the same row and preserve its original accepted rate, amounts and status audit. Later global commission-setting changes do not rewrite old records.

The existing application stores acceptance in the restaurant record and has no separate commission-policy table. `policy_reference` therefore records `general.platform_commission_percentage`; `policy_snapshot` captures the accepted rate, acceptance time and `order_total` basis.

New records default to **Pending**, with no processor or processing timestamp. An authorized administrator marks the commission Processed or Failed after handling it. Authorized platform staff can change status to Pending, Processed or Failed and add processing notes. Owners can view/export records for their own restaurants. After a successful partial refund, the revised restaurant payable can be marked processed once no refund requests remain pending/processing. This is an accounting record of manual processing; it does not transfer funds. Successful refunds preserve the original snapshot and update separate reversal and payable totals.

## Screens and permissions

Open **Payouts & commission** in the sidebar:

- `GET /order-commissions` — shared DataView grid, order/restaurant search, status/date filters, sorting and pagination.
- `GET /order-commissions/{id}` — financial snapshot and processing history.
- `PATCH /order-commissions/{id}` — administrative processing status update (`status`, optional `admin_notes`).

Platform staff require `admin.order-commissions.index` for read access and `admin.order-commissions.update` for processing. Owners have access to their own records and cannot process commissions. Direct detail and export requests use the same restaurant scope.

Restaurant details provide CSV and Excel downloads in the Payouts tab. The existing ledger export URLs also retain search and filters. Examples:

```text
/order-commissions?export=1&format=csv
/order-commissions?filters[status][]=processed&export=1&format=xlsx
```

## Notifications

The `order_commission_updated` email includes the order number, restaurant, order amount, accepted percentage, commission fee, restaurant share and processing status. The default recipient mapping sends it to the restaurant owner and platform admin. A newly recorded commission sends one notification; subsequent actual status changes notify both recipients. Duplicate callbacks or unchanged submissions do not repeat the notification. Messages are dispatched after commit, so mail failures do not reverse payment or commission accounting.

## Installation

```sh
php artisan migrate --path=Modules/Orders/src/Database/Migrations/2026_11_20_000006_create_order_commissions_table.php
php artisan db:seed --class='Modules\Orders\Database\Seeders\OrderCommissionNotificationSeeder'
```

These steps install the schema and templates. They do not backfill historical paid orders or send commission notices for existing orders.

## Read APIs

Authenticate with the owner/staff Sanctum bearer token and `Accept: application/json`.

- `GET /api/order-commissions` lists commissions in a standard Laravel paginator at `data` (rows at `data.data`, plus `total`, `current_page`, `per_page`, `last_page`, and pagination URLs).
- `GET /api/order-commissions/{id}` returns one commission, accepted policy snapshot, processing notes, processor name, and chronological status history. Use the `id` or `detail_url` from the list; display the order number to users.

List query parameters (all optional):

| Parameter | Meaning |
| --- | --- |
| `restaurant_id` | Exact restaurant filter within the caller's allowed restaurants |
| `status` | `pending`, `processed`, or `failed` |
| `order_number` | Partial order number |
| `search` | Partial order number or restaurant name |
| `currency` | Three-letter currency code; case insensitive |
| `date_from`, `date_to` | Inclusive calculation date range in `YYYY-MM-DD` |
| `per_page` | 1–100; default 20 |
| `page` | Page number starting at 1 |

Example: `/api/order-commissions?restaurant_id=2&status=pending&currency=USD&date_from=2026-11-01&date_to=2026-11-30&per_page=10&page=1`.

Amounts are exact decimal strings; currency is provided separately. Responses use `success: true` and `Cache-Control: no-store, private`. Invalid filters return 422, unauthorized roles return 403, and unavailable or other-owner detail records return 404. Owners can only read their restaurants; staff require `admin.order-commissions.index`. These APIs do not change settlement status or transfer funds.


## Cancellation hold

Cancelling an order through the web or API automatically changes its existing commission record to `on_hold` and adds a status-history entry. The original commission amounts and prior processing details are preserved. Repeated cancellation updates do not duplicate hold histories. No commission is created for an unpaid order.

Cancelled-order commissions remain on hold during refund review. Once all active requests are resolved, a successful partial refund releases the revised payable to pending, and a full refund cancels an unpaid commission. The status is available in the web form, grid filters and API responses/filters.

Apply `2026_11_23_000002_add_on_hold_status_to_order_commissions.php` to enable the hold status. Existing records initialize automatically during cancellation, refund updates and commission processing.


## Successful refund reversal

The refund update form accepts the actual amount refunded and previews proportional commission reversal, effective commission, restaurant refund deduction and restaurant payable. Its slider and number field share the remaining captured-payment limit. The preview applies only when `refund_status=succeeded`; pending/processing refunds keep the commission on hold, while failed/rejected refunds do not reduce financial amounts.

The additional decimal fields are `refunded_amount`, `commission_reversed_amount`, `restaurant_refund_deduction`, `final_payable_amount`, and `paid_amount`. `effective_commission` is a decimal-string accessor, calculated as original platform commission minus reversal. The original order amount, percentage, platform commission and restaurant share remain unchanged. All allocation arithmetic uses integer cents and the accepted percentage; cumulative successful refunds are rounded once so partial payments do not introduce rounding drift. Reversal is capped at the original order entitlement.

For an order of USD 200 at 10%, a USD 50 successful refund records USD 5 reversal, USD 45 restaurant deduction, USD 15 effective commission and USD 135 payable, with status pending. A full USD 200 refund records USD 20 reversal and USD 180 deduction, zero effective commission/payable and status cancelled. Other active refund requests keep the status on hold until resolved.

Marking a revised payable processed records that payable in `paid_amount`. Previously processed records preserve their recorded payout and processing details after refund; they remain processed and expose `recovery_due_amount = max(0, paid_amount - final_payable_amount)`. This is a recorded recovery balance; no funds are collected automatically. Initialization treats legacy processed records (including held records with an existing processing timestamp) as paid for their original restaurant share, matching the previous processing workflow.

Both web and API refund updates call the same transactional reconciliation, locking the order, payment, refund and commission. Repeating an identical successful refund does not apply reversal twice or duplicate commission history. Refund history snapshots include the commission settlement before and after the update. Management refund APIs return `amount_range`, `commission_settlement`, and `commission_preview`; commission list/detail APIs return the new totals and effective commission. Grid exports retain original snapshots and include current reversal/payable columns, and dashboard totals use effective amounts.

New paid orders initialize their payable automatically. Existing balances and successful refunds are reconciled during cancellation, refund updates (including identical retries), and commission processing. No separate synchronization command is required. Apply the schema migrations:

```sh
php artisan migrate --path=Modules/Orders/src/Database/Migrations/2026_11_23_000003_add_refund_amounts_to_order_commissions.php --path=Modules/Orders/src/Database/Migrations/2026_11_23_000004_add_cancelled_status_to_order_commissions.php
```

Refunds remain recorded after execution through the payment provider. The existing successful-refund immutability and one-request-per-payment constraint remain in effect.


## Restaurant details — Payouts tab

The Payouts tab shows the selected restaurant’s commission ledger in a read-only spreadsheet layout, with column letters, row numbers, frozen order numbers, sortable headings, and horizontal scrolling. Visible fields are order/date, original amount, refunded amount, commission percentage, reversal, net commission, restaurant payable, paid amount, currency and payout status. Currency-grouped totals cover all matching rows; the table paginates at 5 records by default, with 5/10/25/50 rows-per-page choices and always-visible page controls. The sheet scrolls horizontally and vertically in a bounded area while its headers and order numbers stay frozen.

Use order-number search, payout status, currency and inclusive calculation-date filters. The tab remains active after filtering, sorting, pagination or reloading. CSV/Excel buttons are located in this restaurant tab; the main Restaurant Payouts page keeps its compact grid without those buttons.

`GET /restaurant/{id}/payouts/export?format=xlsx` (or `csv`) exports all matching records for that restaurant. Paging uses `payout_page` and `payout_per_page`; exports always include every filtered row regardless of those paging choices. It accepts the same `payout_search`, `payout_status`, `payout_currency`, `payout_from`, `payout_to`, `payout_sort`, and `payout_direction` filters as the table. Exports include original commission/share, successful refunds, reversal, net commission, restaurant deductions, current payable, recorded payout, recovery due and processing time. XLSX contains numeric amounts, percentage/date formats, frozen columns and a native filterable Excel table. CSV includes UTF-8 BOM and readable dates.

The tab and downloads require the existing commission-read permission (or restaurant-owner read access). Owners are restricted to their own restaurants, including direct export requests. Downloads are private and are not cached.

## Commission-based revenue KPIs

Orders Analytics, Restaurant Analytics, their revenue charts and restaurant-order KPI APIs use the commission ledger. Platform revenue is `platform_commission_amount - commission_reversed_amount`; restaurant-owner earnings are `final_payable_amount`. An unpaid order without a commission contributes no revenue. Successful full refunds reduce both entitlements to zero; partial refunds use the recorded proportional reversal. Pending/held payouts and failed payout attempts retain their current entitlement: revenue is not the same as cash already transferred.

Date filters select orders by their creation date. Revenue summaries and related daily/payment-method/order-type charts share that same scoped order cohort. Owners see only their own restaurant; an owner without a restaurant receives zero orders/revenue. Closed cancelled/failed unpaid orders are excluded from pending-payment KPIs. Customer payment-value metrics remain distinct from commission revenue.

Revenue responses include `basis`, `label`, `description`, `currency`, `commissioned_orders` and `earning_orders`. Existing `average_order_value`, `highest_order` and `lowest_order` revenue fields now describe earnings per order with a positive remaining entitlement. Restaurant-order KPIs expose `revenue_basis`, `revenue_label` and `average_earnings_per_order`; `average_paid_order_value` remains the gross paid-order metric. Different currencies use configured direct/reverse exchange rates for site-currency reporting; missing rates are reported instead of adding incompatible currencies. The restaurant KPI API keeps each currency separate.
