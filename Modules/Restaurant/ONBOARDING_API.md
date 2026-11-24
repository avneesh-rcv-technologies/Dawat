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
  "number": 3,
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
the denominator and `next_step`. The steps are Registration (1), Banking (2),
Tax (3), Dietary Certificates (4, optional), and KYC (5). Approval of Registration,
Banking, Tax and KYC yields 100% when no certificates have been uploaded.
After any upload, Dietary Certificates becomes required (`can_skip: false`);
all uploaded documents must be approved, including resubmissions after rejection
or refill requests. The restaurant's persisted status is returned separately; this read
endpoint does not change it.

The response reports step review details; it does not expose bank account
numbers, SSN/EIN, or uploaded document contents. Existing banking, tax, and KYC
endpoints provide their respective submitted information.

## Dietary certificates during web onboarding

Open `/restaurant/onboarding/kyc?step=dietary_certificates`. Only active dietary
labels marked `is_certification_required` appear. Each label has an independent
document and review decision in `restaurant_certifications`.

Authenticated restaurant owners submit multipart forms to
`POST /restaurant/onboarding/certificates` with `restaurant_id`,
`dietary_label_id`, and `document` (PDF, JPG, or PNG; maximum 10 MB).
`POST /restaurant/onboarding/certificates/skip` with `restaurant_id` continues
to KYC only when nothing is uploaded or every uploaded document is approved.
Pending, under-review, rejected, and refill-requested documents block skipping
and navigation to KYC. Owners can return to the certificate
certificate screen after completing required onboarding.

Documents are stored on the private local disk. Download through
`GET /restaurant/onboarding/certificates/{id}/document`; access is limited to
the owner or a reviewer with `admin.dietarymanagment.certifications.show`.

Admins review individual documents in the restaurant's Dietary Certificates
tab or Dietary Manage → Certifications. Reviewers need the `.show` and
`.review` permissions. They can mark under review, approve, reject, or request
changes. Reject/change requests require feedback. Submitted, rejected, and
change-requested documents can be replaced; approved or under-review documents
are read only. Replacements clear the old decision and require a new review.

Deploy the `2026_11_16_000003_add_dietary_certificates_onboarding_step` migration
to move existing stored KYC step numbers from 4 to 5. No certificate-table
schema change is required.

## API registration approval

`POST /api/restaurant/register` automatically assigns the configured Restaurant
Owner role, activates the account, approves the registration step, and starts
onboarding at Banking Information (step 2). Required onboarding reviews still
need to be completed before the restaurant can accept orders. Signup creates
the same pending KYC requests and uses the existing registration approval
notification as web signup.

The registration body accepts `owner_name` as an alias for `contact_person`.
Existing clients can continue sending `contact_person` and the other required
registration fields. Do not send a role ID or approval status; these are assigned
by the server.

For the web-style email verification flow, send/verify the OTP with
`type: "onboarding_restaurant_email_verification"` using the existing
`/api/restaurant/send-otp` and `/api/restaurant/verify-otp` endpoints. Pass the
returned `verification_token` as `email_verification_token` during registration.
The token must belong to the same email. Signup then returns
`email_verified: true` and `otp_sent: false` and consumes the token after commit.

Without a token, registration is still automatically approved and assigned the
owner role; the existing signup OTP flow verifies the email before login.
The response returns `email_verified: false` and `otp_sent: true`. Approval of
registration does not by itself verify an email address.
