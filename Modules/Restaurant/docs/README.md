# Restaurant banking and tax APIs

Endpoints below match the current routes in [`api.php`](../src/Routes/api.php).
Use your application host as the base URL, for example `http://127.0.0.1:8000`.

## Authentication

All endpoints use the restaurant owner's Sanctum token:

```http
Authorization: Bearer <restaurant-owner-token>
Accept: application/json
Content-Type: application/json
```

No restaurant ID is needed. The account's restaurant is selected automatically.
These endpoints are available before onboarding approval. Inactive or closed
restaurants are denied. Send bank account, routing number, SSN, EIN, and postal
codes as strings to preserve leading zeros.

## Current endpoints

| API | Method | Endpoint |
| --- | --- | --- |
| Banking: retrieve | GET | `/api/restaurant/me/banking/get-banking-details` |
| Banking: submit | POST | `/api/restaurant/me/banking/store-banking-details` |
| Banking: partial edit | PATCH | `/api/restaurant/me/banking/update-banking-details` |
| Banking: required-field replacement | PUT | `/api/restaurant/me/banking/update-banking-details` |
| Tax: retrieve | GET | `/api/restaurant/me/tax-information/get-tax-information` |
| Tax: submit | POST | `/api/restaurant/me/tax-information/store-tax-information` |
| Tax: partial edit/resubmit | PATCH | `/api/restaurant/me/tax-information/update-tax-information` |

## Banking examples

POST `/api/restaurant/me/banking/store-banking-details`:

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

PATCH `/api/restaurant/me/banking/update-banking-details`:

```json
{
  "business_legal_name": "Updated Restaurant LLC",
  "city_id": 111041
}
```

PATCH accepts supplied fields only. PUT requires all required fields from the
POST example. Routing numbers must have 9 digits; account numbers need 4–17
digits and a matching confirmation whenever supplied. DBA is optional.
Owner banking updates preserve approved steps and their review metadata. Updates to unapproved steps, including submitted, under-review, and rejected steps, resubmit them for review even if the details are unchanged. Banking uses integer `country_id`, `state_id`, and `city_id` values. Admin web edits preserve the step status.

GET includes `onboarding_step` and returns `data: null` if no banking record
exists. Write responses contain `success`, `message`, and `data`. Account
numbers are returned only as masked values and last four digits.

See [banking-api.md](banking-api.md) for detailed behavior and admin review.

## Tax examples

POST `/api/restaurant/me/tax-information/store-tax-information`:

```json
{
  "tax_type": "individual",
  "full_name": "Example Owner",
  "ssn": "123-45-6789",
  "address": "123 Main Street",
  "city_id": 1,
  "state_id": 2,
  "country_id": 3,
  "zip_code": "02108",
  "same_mailing_address": true,
  "receive_1099_electronically": true,
  "backup_tax_withholding": false,
  "tax_certification": true
}
```

Use existing, matching location IDs. For a business, set `tax_type` to `business`
and replace `full_name`/`ssn` with `company_legal_name`/`ein`.
`doing_business_as` is optional. SSN accepts 9 digits or `123-45-6789`; EIN accepts
9 digits or `12-3456789`. If `same_mailing_address` is false, supply
`mailing_address`, `mailing_city`, `mailing_state`, and `mailing_zip_code`.

PATCH `/api/restaurant/me/tax-information/update-tax-information`:

```json
{
  "tax_type": "individual",
  "address": "456 Updated Street"
}
```

Both POST and PATCH require `tax_type`. One record can be saved per type.
PATCH edits the chosen record, merges saved fields, and validates the full result.
Successful writes synchronize `restaurants.tax_type`. Owner updates preserve an approved
tax step and its review metadata. Unapproved steps are submitted for review again.
To resubmit saved information for an unapproved step, send only
`{"tax_type":"individual"}` or `{"tax_type":"business"}`.

GET supports `?type=individual` or `?type=business`. Without `type`, it returns
both keys in `data`; each is its record or `null`. Tax responses also contain
the restaurant's selected `tax_type` and `onboarding_step`. Write responses add
`message`. SSN and EIN are encrypted and omitted from responses.

See [TAX_API.md](../TAX_API.md) for detailed behavior, admin review and notifications.

## Status codes

For KYC documents, POST `/api/restaurant/onboarding/documents/reupload` accepts a
`documents` array with distinct `kyc_type_id` values and the configured document
fields/files. Only existing pending, under-review, or rejected submissions can
be replaced. Approved documents return 422; a batch containing an approved
document is rejected before any replacements. Reuploaded documents return to
`pending` and the KYC onboarding step is submitted for review. Initial uploads
continue to use the existing upload endpoint.

| Code | Meaning |
| --- | --- |
| 200 | Read or update succeeded |
| 201 | New record submitted |
| 401 | Missing or invalid token |
| 403 | Account type or restaurant status denied |
| 404 | Restaurant missing, or update attempted before creating the selected record |
| 422 | Invalid fields or duplicate submission |

For overall onboarding progress and steps, see
[ONBOARDING_API.md](../ONBOARDING_API.md).

## Web edits and review PDF

Web **Edit information** saves in Registration, Banking, and Tax preserve the
current status, review dates, reviewer and rejection reason. Tax web edits also
preserve the selected type and certification timestamp. Owner API edit and
resubmission behavior above remains unchanged.

The **Review** tab at `/restaurant/approvals/{id}?step=review` consolidates the
saved details and decisions. **Download PDF** exports an A4 report via
`GET /restaurant/approvals/{id}/review/pdf` (with the configured admin prefix).
It requires `admin.restaurant.approvals.show` and
`admin.restaurant.approvals.review.pdf`. Banking, tax and KYC sections also respect
their existing view permissions. Bank account numbers and tax identifiers are
masked. Uploaded KYC files are summarized rather than embedded in the report.
Downloading or reviewing the report does not change any status.

## Final onboarding approval notification

When every step in `OnboardingStep::required()` is approved, the observer approves
overall onboarding and activates the restaurant. On the first transition to
Approved, it sends **Final Onboarding Approved** to the owner after commit.
Skippable steps do not block this notification. Already-approved onboarding does
not schedule another final notification.

`FinalOnboardingApprovedNotificationSeeder` registers the
`final_onboarding_approved` event and email/web-push templates. It is included in
both the application and Restaurant module seeders. The email attaches the A4
onboarding review PDF, with bank account numbers and SSN/EIN masked. Attachment
bytes are carried with the queued email rather than stored at a public URL.
Web push contains only the approval message, without the attachment data.
Failures use the project's exception handling and do not undo approval.

Owner banking and tax updates to unapproved steps clear review metadata and notify the owner and admin through Resubmitted Details email/web push events after commit. Approved steps retain their status, review metadata, and overall onboarding status, and do not send resubmission notifications. Seed their templates using:

```bash
php artisan db:seed --class='Modules\Restaurant\Database\Seeders\RestaurantResubmittedDetailsNotificationSeeder'
```

Resubmitting any onboarding step sets `restaurants.onboarding_status` to `action_required`. Overall onboarding returns to `approved` once all required steps are approved. Admin web Edit Information saves preserve status.

Banking locations use `country_id`, `state_id`, and `city_id` from the Countries API. Example IDs are illustrative; select valid IDs from your database. The state must belong to the country and the city must belong to both. PATCH merges saved IDs with supplied fields before validating the full hierarchy. Changing country or state requires matching child IDs. Old `city`, `state`, and `country` text inputs are rejected. Responses include the location IDs and related location objects.

For existing installations, run the banking location migration and populate valid location IDs for existing records before the next save. Existing text columns, if present, must be nullable or removed before creating records that supply only IDs.
