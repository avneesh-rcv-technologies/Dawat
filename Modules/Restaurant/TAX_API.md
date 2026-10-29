# Restaurant tax information API

All endpoints require `Authorization: Bearer <restaurant-owner-token>` and
`Accept: application/json`. Send JSON with `Content-Type: application/json`.
The authenticated owner's restaurant is selected automatically. No restaurant ID
is accepted. These endpoints are available during onboarding before approval.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| POST | `/api/restaurant/me/tax-information` | Create and submit tax information (201) |
| GET | `/api/restaurant/me/tax-information` | Retrieve saved information and tax onboarding status (200) |
| PATCH | `/api/restaurant/me/tax-information` | Edit supplied fields or resubmit rejected information (200) |

POST example for an individual (replace location IDs with real, matching IDs):

```json
{
  "tax_type": "individual",
  "full_name": "Example Owner",
  "ssn": "123-45-6789",
  "address": "123 Main Street",
  "city_id": 1,
  "state_id": 2,
  "country_id": 3,
  "zip_code": "02101",
  "same_mailing_address": true,
  "receive_1099_electronically": true,
  "backup_tax_withholding": false,
  "tax_certification": true
}


{
  "tax_type": "business",
  "company_legal_name": "Royal Palm Banquet",
  "doing_business_as": "Royal Palm Banquet Hall",
  "ein": "12-3456789",
  "address": "123 Main Street",
  "city_id": 1,
  "state_id": 2,
  "country_id": 3,
  "zip_code": "02101",
  "same_mailing_address": true,
  "receive_1099_electronically": true,
  "backup_tax_withholding": false,
  "tax_certification": true
}
```

For `tax_type: "business"`, replace `full_name` and `ssn` with
`company_legal_name` and `ein` (nine digits or `12-3456789`).
`doing_business_as` is optional. SSN accepts nine digits or `123-45-6789`.
The country, state and city must exist and belong to the selected hierarchy.

If `same_mailing_address` is false, also provide `mailing_address`,
`mailing_city`, `mailing_state` and `mailing_zip_code` as strings.
The mailing fields retain the original schema's string types.

PATCH example:

```json
{
  "address": "456 Updated Street",
  "zip_code": "02102"
}
```

PATCH merges supplied fields with saved information, then validates the complete
submission. When switching tax type, supply the newly required identity fields.
PATCH requires `tax_type`. Sending only `{"tax_type":"individual"}` or `{"tax_type":"business"}` resubmits saved information when the tax step is unapproved. Approved steps keep their approval.

Successful responses contain `success`, `data`, and `onboarding_step`.
Write responses also contain `message`. SSN and EIN are encrypted in storage and
omitted from responses. `tax_certified_at` is set by the server.
GET returns `data: null` when no tax record has been created.

Creating tax information or editing an unapproved step sets the tax onboarding step to `submitted`,
refreshes `submitted_at`, and clears its prior review and rejection details.
Owner updates to unapproved steps, including unchanged information, are recorded as `resubmitted` in history and require admin review again. After commit, the owner and admin receive a Resubmitted Details email/web push notification (`restaurant_tax_resubmitted`). Owner updates to approved steps save details and record an edit while preserving approval, submission time, review metadata, and overall onboarding status, without sending resubmission notifications. Other onboarding steps are unaffected. Admin web Edit Information saves preserve status.

Seed both banking and tax resubmission events using `php artisan db:seed --class='Modules\Restaurant\Database\Seeders\RestaurantResubmittedDetailsNotificationSeeder'`.

Validation errors return 422 with `success: false`, `message`, and `errors`.
Duplicate POST returns 422; PATCH before creation returns 404. Authentication
is required (401); non-owner account types and inactive/closed restaurants are
denied (403). An owner without a restaurant receives 404.

The compatibility migration `2026_10_26_000002` corrects legacy location columns
only when the old table is empty. If legacy tax records exist, it stops before
altering them so their address names can be mapped to IDs. Its rollback retains
the correction; rolling back the create migration removes the table.

Resubmitting any onboarding step sets `restaurants.onboarding_status` to `action_required`. Overall onboarding returns to `approved` once all required steps are approved. Admin web Edit Information saves preserve status.
