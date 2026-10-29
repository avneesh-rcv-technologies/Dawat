# Owner banking and company information

All endpoints require a restaurant owner's Sanctum bearer token and `Accept: application/json`.
The restaurant is selected from the authenticated owner's `admins.id`; clients cannot supply a restaurant or owner ID.
Draft and pending onboarding accounts can use these endpoints. Inactive or closed restaurants cannot.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/api/restaurant/me/banking/get-banking-details` | Read the owner's banking and company record (200) |
| POST | `/api/restaurant/me/banking/store-banking-details` | Submit the first record (201) |
| PUT | `/api/restaurant/me/banking/update-banking-details` | Replace required banking and company fields (200) |
| PATCH | `/api/restaurant/me/banking/update-banking-details` | Update only supplied fields (200) |

These are the paths currently registered in `src/Routes/api.php`. The bare
`/api/restaurant/me/banking` path is not registered.

POST and PUT body (`Content-Type: application/json`):

```json
{
  "account_holder_name": "Example Restaurant LLC",
  "routing_number": "012345678",
  "account_number": "00123456789",
  "account_number_confirmation": "00123456789",
  "business_legal_name": "Example Restaurant LLC",
  "doing_business_as": "Example Cafe",
  "address": "123 Main Street",
  "country_id": 233,
  "state_id": 1433,
  "city_id": 111041,
  "postal_code": "02108"
}
```

The form follows the supplied routing-number format: exactly 9 digits for routing numbers, and 4–17 digits for account numbers. Send both as strings to preserve leading zeros. Account confirmation is required whenever an account number is supplied. DBA is optional; PATCH with `"doing_business_as": null` clears it. PUT replaces the required fields; omitted DBA is preserved.

Example company-only PATCH:

```json
{"business_legal_name": "Updated Restaurant LLC", "city_id": 111041}
```

GET returns `success`, `data`, and `onboarding_step`. POST/PUT/PATCH return
`success`, `message`, and `data`; write responses do not include `onboarding_step`.
Data contains company fields, routing number, `account_number_masked`, and
`account_number_last_four`. Full account numbers and their confirmations are
never returned. GET returns 200 with `data: null` when the owner has not submitted
banking details yet. A token account without a restaurant profile returns 404.
POST returns 422 if a record already exists; use PUT/PATCH to edit. PUT/PATCH
requires an existing record and returns 404 until the first POST submission.
Validation failures return 422 with field errors.

Initial submission sets the banking onboarding step to `submitted`. Owner PUT/PATCH updates to an approved step save details while preserving approval, submission time, review metadata, and overall onboarding status, without resubmission notifications. Updates to unapproved steps resubmit them, including unchanged details. Resubmission refreshes `submitted_at` and clears previous review metadata. Admins must start review again before approving or rejecting. History records `resubmitted`; tabs and timelines show the resubmission. Other steps and the restaurant active/inactive status are preserved; overall onboarding becomes `action_required`. After commit, the owner and admin receive the Resubmitted Details email/web push notification, without bank identifiers. Admin web Edit Information saves preserve the review status.

Bank identifiers are encrypted using Laravel's configured application key. Keep that key available when deploying or rotating keys.

Admins can view the Banking Information tab at `/restaurant/approvals/{id}?step=banking` (with the configured admin prefix). Access requires the existing application-details permission plus `admin.restaurant.approvals.banking.show`. Company details and routing number are shown, with a masked account number.

Admins must use **Start Review** before approving or rejecting banking details.
Starting review requires `admin.restaurant.approvals.start-review` and banking
view access. Decision controls require the corresponding
`admin.restaurant.approvals.banking.approve` or
`admin.restaurant.approvals.banking.reject` permission, plus banking view access.
Each action asks for confirmation; rejection requires a reason of up to 500
characters. Reviews record `reviewed_by`, `reviewed_at`, and the banking decision.
Only an under-review step can receive a decision. When every required step is
approved according to `OnboardingStep::canSkip()`, the observer also approves
overall onboarding and activates the restaurant.

The web actions are POST `/restaurant/approvals/{id}/banking/approve` and POST `/restaurant/approvals/{id}/banking/reject` with admin authentication, CSRF protection, and `step_key=banking`. Rejection also needs `rejection_reason`. Successful actions return to the banking tab and use the existing toast messages. The owner receives email and web push notifications after commit, including the rejection reason when applicable. Owner edits to rejected banking details reset the step to `submitted`, allowing a new review. Owner updates to approved banking details preserve approval.

## Setup

Admin **Edit information** saves preserve the banking step status, submission
time, reviewer, review time, and rejection reason. They record detail changes
without resubmission or submission notifications. Registration and tax web
edit forms likewise preserve their existing statuses. Explicit review actions
and owner API resubmissions remain separate.

Run pending migrations and the notification seeder after deploying:

```bash
php artisan migrate
php artisan db:seed --class='Modules\Restaurant\Database\Seeders\RestaurantBankingNotificationSeeder'
```

The notification channels must already include `email` and `webpush`. The seeder is also registered in the Restaurant module seeder and the main database seeder. It creates or updates submitted, updated, approved, and rejected events and email/web push templates. Submissions and updates notify the owner and admin; review decisions notify the owner. Enable and configure the channels using the existing notification settings. Web push uses the existing provider configuration and requires a registered device subscription for each recipient. Re-run the banking notification seeder after adding either channel to create its mappings, and after deploying the new review events. Grant banking view and review permissions to custom admin roles that need access.

## Onboarding action history

`restaurant_onboarding_status_histories` also records step actions. The nullable `step_key` identifies registration, menu, banking, tax, or KYC; `action` identifies initialization, submission, resubmission, edit, approval, rejection, or an overall status change. Existing overall history remains supported. Each entry includes the previous and resulting status, actor, timestamp, and rejection reason where applicable. Edits are logged even when the status is unchanged, including approved banking edits. Account numbers, routing numbers, and submitted document contents are not copied into history.

Step model changes are logged automatically. Registration and banking detail changes, and individual KYC submissions and review updates, are also logged through the shared history service. Database query-builder bulk updates bypass Eloquent events; use model saves for onboarding changes. Apply the pending history migration with `php artisan migrate` before using these actions.

Seed resubmission notifications (email and web push) with:

```bash
php artisan db:seed --class='Modules\Restaurant\Database\Seeders\RestaurantResubmittedDetailsNotificationSeeder'
```

Resubmitting any onboarding step sets `restaurants.onboarding_status` to `action_required`. Overall onboarding returns to `approved` once all required steps are approved. Admin web Edit Information saves preserve status.

Banking locations use `country_id`, `state_id`, and `city_id` from the Countries API. Example IDs are illustrative; select valid IDs from your database. The state must belong to the country and the city must belong to both. PATCH merges saved IDs with supplied fields before validating the full hierarchy. Changing country or state requires matching child IDs. Old `city`, `state`, and `country` text inputs are rejected. Responses include the location IDs and related location objects.

For existing installations, run the banking location migration and populate valid location IDs for existing records before the next save. Existing text columns, if present, must be nullable or removed before creating records that supply only IDs.
