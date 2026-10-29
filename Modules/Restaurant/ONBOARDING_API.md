# Restaurant onboarding details

`GET /api/restaurant/me/onboarding-details`

Headers:

```http
Authorization: Bearer <restaurant-owner-token>
Accept: application/json
```

No request body or restaurant ID is needed. The endpoint selects the authenticated
owner's restaurant and is available before activation, including pending/rejected
applications. It returns 401 without authentication, 403 for other account types,
and 404 when the owner has no restaurant.

The 200 response contains `success`, `message`, and `data`:

- `restaurant`: ID, name, account status, registration approval status, onboarding
  status and label, stored onboarding step number, selected tax type, submission
  and approval times, and restaurant rejection reason.
- `progress`: total steps, required steps, approved required steps, integer
  percentage, and `is_complete`.
- `next_step`: first required step without approval (`key`, `number`, `label`),
  or `null` when all required steps are approved. It can be awaiting review.
- `steps`: every step in enum order, including steps without database records.

Each entry in `steps` has this shape:

```json
{
  "key": "tax",
  "number": 4,
  "label": "Tax Information",
  "status": "submitted",
  "status_label": "Submitted",
  "can_skip": false,
  "is_approved": false,
  "submitted_at": "2026-10-26T12:00:00+00:00",
  "reviewed_at": null,
  "reviewer": null,
  "rejection_reason": null,
  "resubmitted": true,
  "resubmitted_at": "2026-10-26T12:00:00+00:00"
}
```

Reviewer data contains only `id` and `name`. Missing step records have
`status: "not_started"` and nullable dates/review details. Timestamps use ISO 8601
with a timezone offset. Resubmission indicators remain visible after approval.

Progress uses `OnboardingStep::canSkip()` and excludes skippable steps from both
the denominator and `next_step`. Currently Menu is skippable, so approval of
Registration, Banking, Tax and KYC yields 100%. The restaurant's persisted status
is returned separately; this read endpoint does not change it.

The response reports step review details; it does not expose bank account
numbers, SSN/EIN, or uploaded document contents. Existing banking, tax, and KYC
endpoints provide their respective submitted information.
