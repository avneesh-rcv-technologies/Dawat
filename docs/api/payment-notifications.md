# Payment success notifications

`PaymentNotificationSeeder` configures events, templates and recipient mappings.
Running it does not send receipts. Receipts are triggered by successful persisted
payment changes.

## Corrected lifecycle

`Payments` model created/updated → `PaymentObserver` →
`PaymentSuccessNotificationService` → outer transaction commit → reload payment,
order/customer, method and transactions → dispatch the mapped `payment_success`
channels.

This covers signed Stripe order-payment links, authenticated client confirmation,
webhook processing, immediate gateway responses, and transaction status events
that update the payment model. Bulk SQL payment updates intentionally bypass model
events and must use the model/service if receipt scheduling is required.

The previous signed-link and client-confirmation paths completed payments without
a PaymentSuccess trigger. The old payment response callback relied on customer
session authentication, which is absent for webhooks and signed-link payers; an
audit or session error could skip the receipt. Receipt scheduling now uses the
order's persisted customer, separately from audit logging and OrderCreated notices.

Eligibility: the payment must be completed/captured/paid, represent an order
payment, have a linked paid order, and have a customer email. COD uses the existing
`cod_payment_received` event instead of also sending an online receipt. Pending,
failed and refund records do not emit success receipts.

Notifications run after the payment commit. Scheduling/channel errors are caught
and logged without rolling back or changing payment/order state. Payments and
receipt callbacks serialize on the payment row. Successful channel dispatches are
recorded independently in `payments.details.payment_success_notification_channels`.
`payment_success_notification_queued_at` is written when all active mapped channels
have been accepted by their handlers. Receipt metadata is saved quietly, avoiding
recursive observer events. Gateway response updates preserve these markers.

Repeated webhook/client callbacks skip channels already queued; a failed push
attempt can be retried without requeueing a successful email. These flags record
queue acceptance, not final inbox/device delivery. Worker failures use the queue's
normal retry/failed-job handling. Channels without device subscriptions have no
push target; no subscription is created by this change.

## Setup and delivery

Seed the payment mappings explicitly:

```bash
php artisan db:seed --class='Modules\Payments\Database\Seeders\PaymentNotificationSeeder'
```

Or seed both online and COD notification mappings:

```bash
php artisan db:seed --class='Modules\Payments\Database\Seeders\PaymentDatabaseSeeder'
```

The plural `PaymentsDatabaseSeeder` alias is supported for module-runner
compatibility. These module seeders configure notifications rather than creating
a fake user. Seeding uses update-or-create for events/templates/mappings and does
not send messages. Defaults notify the customer; edit recipient/channel mappings
in the notification settings for other audiences. An existing disabled channel is
not automatically re-enabled by the seeder.

With `QUEUE_CONNECTION=database`, a worker must process email/default and push jobs:

```bash
php artisan queue:work --queue=default,push_notifications
```

Restart existing long-lived workers after deploying this code:

```bash
php artisan queue:restart
```

Check General Settings mail configuration and `config/mail.php`/`.env`. A `log`
mailer writes messages to the application log rather than delivering them to an
inbox, unless the app's stored mail settings select a configured delivery mailer.
SMTP credentials and push-provider/device setup must be valid independently.

## Recover a receipt missed before the fix

Use a payment ID:

```bash
php artisan payments:retry-success-notification 1 --dry-run
php artisan payments:retry-success-notification 1
```

Or use the transaction ID shown in the transaction-detail URL (example: `/transactions/5`):

```bash
php artisan payments:retry-success-notification --transaction-id=5 --dry-run
php artisan payments:retry-success-notification --transaction-id=5
```

Dry-run checks for a completed paid order payment without sending a receipt. It
does not certify that channel configuration or customer email is present. The
non-dry run queues only channels lacking receipt markers. It never charges the
customer or changes payment/order status. Missing/ineligible records or channels
that cannot be queued produce a nonzero exit status. COD receipts remain managed
through the COD collection flow.

## Diagnosis and verification

Look for these application log messages:

- `Payment success notification queued.`: includes payment/order IDs and channel.
- `Payment success notification has no active mappings. Run PaymentNotificationSeeder.`
- `Payment receipt has no customer email.`
- `Payment success notification channel was not queued.`
- `Payment success notification failed after commit.`
- `Email notification could not be queued.` / `Push notification delivery failed.`

Inspect `notification_logs`, queued `jobs` and `failed_jobs` to distinguish
dispatch from delivery. No live messages or payment operations were performed
during verification; tests used a mocked database, channel handlers and queue
boundary, including the signed-link completion method from the transaction UI.
