# Dietary management API

Base URL: `{{base_url}}/api/dietarymanagment`

The path uses the existing module spelling, **dietarymanagment**.

These APIs use the same private document storage, review states, and onboarding
rules as the web Dietary Certificates screen.

## Authentication

Use a restaurant owner's bearer token:

```http
Authorization: Bearer {{restaurant_token}}
Accept: application/json
```

The account must be active and its email verified. The restaurant is selected
from the authenticated owner's account, including restaurants still onboarding.
For accounts with multiple restaurants, the first restaurant by ID is used,
matching the current owner banking API. Do not send `restaurant_id` or `admin_id`.

The web application can also use its admin session with a CSRF token, as with
the banking onboarding endpoints. Uploaded documents never have public URLs.

## Endpoints

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/labels` | List active dietary labels |
| GET | `/labels?is_certification_required=1` | List active labels requiring certificates |
| GET | `/certificates` | Get required certificate types and the owner's submissions/statuses |
| GET | `/certificates/{typeId}` | Get one type's submission or Not submitted state |
| POST | `/certificates` | Upload a new document by certification type |
| PATCH | `/certificates/{typeId}` | Replace and resubmit an existing document |
| GET | `/certificates/{typeId}/document` | Download the owner's document |
| GET | `/certificates/{typeId}/document?preview=1` | Preview the owner's document inline |

`{typeId}` is **certification_types.id**, not the document ID and not the dietary
label ID. Use the `certification_type_id` returned by the labels/certificates API.

## 1. Get dietary labels

```http
GET {{base_url}}/api/dietarymanagment/labels?is_certification_required=1
```

Remove the query parameter to include labels that do not require certificates.
Use `is_certification_required=0` to retrieve only those labels.

Example response:

```json
{
  "success": true,
  "message": "Dietary labels retrieved successfully.",
  "data": [
    {
      "id": "1",
      "name": "Halal",
      "code": "H",
      "slug": "halal",
      "description": "Food prepared in accordance with Islamic dietary requirements.",
      "icon": "shield-check",
      "is_certification_required": true,
      "certification_type_id": 1,
      "certification_type": {
        "id": 1,
        "name": "Halal certification",
        "is_active": true
      }
    }
  ]
}
```

Sample IDs are illustrative. A label without a configured certification type
returns `certification_type_id: null` and `certification_type: null`.

## 2. Get certificates and onboarding state

```http
GET {{base_url}}/api/dietarymanagment/certificates
GET {{base_url}}/api/dietarymanagment/certificates/1
```

The collection response has an array in `data`; a single-type response has one
object. Required active types appear even before the owner uploads a document.
Previously submitted types remain visible if their label/type is later disabled;
disabled types cannot receive new/replacement uploads.

Example single-type response:

```json
{
  "success": true,
  "message": "Dietary certificate retrieved successfully.",
  "data": {
    "id": 97,
    "certification_type_id": 1,
    "certification_name": "Halal certification",
    "dietary_label": {
      "id": "1",
      "name": "Halal",
      "code": "H",
      "icon": "shield-check",
      "description": "Food prepared in accordance with Islamic dietary requirements."
    },
    "status": "submitted",
    "status_label": "Submitted",
    "can_submit": false,
    "can_update": true,
    "admin_notes": null,
    "submitted_at": "2026-11-16T14:18:00+05:30",
    "reviewed_at": null,
    "reviewer": null,
    "document": {
      "file_name": "halal-certificate.pdf",
      "download_url": "{{base_url}}/api/dietarymanagment/certificates/1/document",
      "preview_url": "{{base_url}}/api/dietarymanagment/certificates/1/document?preview=1"
    }
  },
  "onboarding": {
    "restaurant_id": 126,
    "step_key": "dietary_certificates",
    "step_status": "submitted",
    "has_uploaded_documents": true,
    "can_skip": false,
    "awaiting_approval": true,
    "can_continue": false
  }
}
```

Before upload, `id` and `document` are null, `status` is `not_submitted`, and
`can_submit` is true for eligible types. Rejected/change-requested submissions
include the admin's feedback in `admin_notes`. `reviewer`, when present, contains
only the reviewer's ID and name. Private filesystem paths are not exposed.

## 3. Store a new certificate

```http
POST {{base_url}}/api/dietarymanagment/certificates
```

Send **multipart/form-data**, not a JSON file path or base64 string:

| Field | Type | Required |
| --- | --- | --- |
| `certification_type_id` | Integer | Yes; active type linked to an active label requiring certification |
| `document` | File | Yes; PDF, JPG, JPEG, or PNG; maximum 10 MB |

```bash
curl --request POST '{{base_url}}/api/dietarymanagment/certificates' \
  --header 'Authorization: Bearer {{restaurant_token}}' \
  --header 'Accept: application/json' \
  --form 'certification_type_id=1' \
  --form 'document=@/path/to/halal-certificate.pdf'
```

Success: **201**, with the saved certificate in `data`, `status: submitted`, and
the current `onboarding` state. Submitting the same type again with POST returns
422; use PATCH for an existing submission.

## 4. Update/re-upload a certificate

Logical endpoint:

```http
PATCH {{base_url}}/api/dietarymanagment/certificates/1
```

Send only the replacement `document`; the type is selected by the URL.
For PHP multipart uploads, use **POST with `_method=PATCH`** so PHP correctly
parses the uploaded file:

```bash
curl --request POST '{{base_url}}/api/dietarymanagment/certificates/1' \
  --header 'Authorization: Bearer {{restaurant_token}}' \
  --header 'Accept: application/json' \
  --form '_method=PATCH' \
  --form 'document=@/path/to/corrected-halal-certificate.pdf'
```

In Postman: choose POST, Body → form-data, add `_method` as Text with value
`PATCH`, and `document` as File. Let the client generate the multipart
Content-Type and boundary.

Success: **200**. The same document record is reused, its status returns to
`submitted`, and the previous review notes/reviewer/review time are cleared.
The old file is removed after the successful replacement commits.

## 5. View/download a document

Use the response's document URLs with the same authentication headers. A bearer
URL alone does not grant browser access; the app must include its bearer token
when fetching it. A browser with the owner's authenticated session can use the
URL directly. `preview=1` returns supported documents inline; otherwise the
response is an attachment.

## Review and onboarding rules

- One document per restaurant and certification type.
- Submitted, rejected, and change-requested documents can be replaced.
- Under-review and approved documents are read only. Approved decisions are final.
- Owners cannot set `status`, `admin_notes`, `reviewed_by`, `reviewed_at`,
  `submitted_at`, or `file_path`. Admin review uses the existing admin screens.
- No uploads: `can_skip: true`, `can_continue: true`.
- Any upload: `can_skip: false`; all uploaded documents must be approved before
  `can_continue` becomes true. Rejected/refill-requested documents must be corrected
  and approved. Labels for which no document was uploaded do not require approval.

## Errors

| Status | Meaning |
| --- | --- |
| 401 | No valid authentication |
| 403 | Inactive account, unverified email, or account is not an admin/restaurant owner |
| 404 | No restaurant, unavailable type, no existing submission for PATCH, or no owned document |
| 415 | Unsupported stored document format requested for inline preview |
| 422 | Validation failure, duplicate POST, prohibited fields, or read-only submission |
| 429 | Write request rate limit reached |

Validation failures include `message` and an `errors` object keyed by field name.
Use the status code to detect failure and display the returned field messages.
No new migrations are required if the existing dietary/onboarding tables are
installed. Labels/types are managed using the current admin screens.

## Certificate notifications

Web and API uploads use the same notification flow. Notifications are dispatched
after the database transaction commits for submitted, resubmitted, under-review,
approved, rejected, and changes-requested certificates. Owners receive each event;
admins also receive submissions and resubmissions by default. Existing notification
mapping choices are preserved when the seeder runs again.

Install or update the current Dawat email templates and push templates:

```bash
php artisan db:seed --class='Modules\DietaryManagment\Database\Seeders\DietaryNotificationSeeder' --force
```

The seeder registers email mappings and, when the webpush channel exists, push
mappings. Delivery uses the existing notification channels. Notification failures
are caught and reported through `ApiExceptionHandler::handle($e)` without failing
the upload or review, or displaying a notification error on a successful web save.
Rolled-back transactions do not dispatch notifications.
