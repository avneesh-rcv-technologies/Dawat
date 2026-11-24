# Restaurant dietary certificate status

```http
GET {{base_url}}/api/restaurant/dietary-certificates/status
Authorization: Bearer <restaurant_owner_token>
Accept: application/json
```

No body or restaurant ID is needed. The authenticated active, email-verified
owner's first draft/active restaurant is selected, matching the V2 food-item API.
Existing admin sessions also work. Responses use `Cache-Control: private, no-store`.

The endpoint lists active dietary labels with `is_certification_required=true`
and checks certificates belonging to this restaurant, matched by
`certification_type_id`. `is_uploaded` requires an existing private local document;
a database path pointing at a missing file is not treated as an upload.

Example **200**, with one required label and no certificate submitted:

```json
{
  "success": true,
  "message": "Restaurant dietary certificate status retrieved successfully.",
  "data": {
    "restaurant_id": 126,
    "required_count": 1,
    "uploaded_count": 0,
    "approved_count": 0,
    "missing_upload_count": 1,
    "awaiting_approval_count": 0,
    "all_required_uploaded": false,
    "all_required_approved": false,
    "dietary_labels": [
      {
        "id": "3",
        "name": "Halal",
        "allowed": false,
        "required": true,
        "is_certification_required": true,
        "is_uploaded": false,
        "is_approved": false,
        "certification_type_id": 2,
        "certificate_id": null,
        "status": "not_submitted",
        "message": "Upload certificate for admin approval."
      }
    ]
  }
}
```

| Situation | `is_uploaded` | `is_approved` | `allowed` |
|---|---|---|---|
| No certificate record | false | false | false |
| Submitted / under review, document exists | true | false | false |
| Rejected / changes requested, document exists | true | false | false |
| Approved with existing document and active certification type | true | true | true |
| Approved record but document is missing | false | true | false |
| Approved but certification type is inactive | true if document exists | true | false |

`status` retains the review status in the database; `is_approved` reports that
review decision. Use **`allowed`** to decide whether a label can be selected in a
food item. `approved_count` counts usable approved certificates, not approved rows
with missing documents. The summary is over all returned required labels. With
none, counts are zero, both all-required flags are true, and the list is empty.

Food-item POST/PATCH validates only the labels selected on that item. A restaurant
does not have to upload every label's certificate to use unrelated, non-required
labels. GET item/list responses expose the same eligibility fields and a
`dietary_certificate_check` object without blocking reads of old items.

Upload using the existing endpoint:

```http
POST {{base_url}}/api/dietarymanagment/certificates
```

Multipart fields: `certification_type_id` (from this status response) and
`document` (file). Existing submissions are updated through
`PATCH /api/dietarymanagment/certificates/{certification_type_id}`. Follow the
certificate API's existing file rules. Uploaded certificates remain unavailable
for food-item selection until approved by admin.

Food-item save failure example (**422**):

```json
{
  "success": false,
  "message": "Halal: Upload certificate for admin approval.",
  "errors": {
    "dietary_label_ids": [
      "Halal: Upload certificate for admin approval."
    ]
  }
}
```
