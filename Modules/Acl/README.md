# Customer OTP API Endpoints

Base URL: `/api/customer`

This module supports OTP verification for customer email and phone (SMS) flows.

## Common request fields

- `channel`: `email` or `sms`
- `type`:
  - `login_email_verification`
  - `register_email_verification`
  - `verify_phone`
  - `login`
  - `register`
- `email`: customer email address
- `phone`: customer phone number
- `otp`: 4-digit OTP code

---

## 1) Send OTP

Endpoint:
`POST /api/customer/auth/send-otp`

### Email flow

Request body:
```json
{
  "email": "customer@example.com",
  "type": "login_email_verification",
  "channel": "email"
}
```

Example response:
```json
{
  "success": true,
  "message": "Verification code sent successfully",
  "channel": "email",
  "type": "login_email_verification",
  "expires_in": 300,
  "identifier": "customer@example.com",
  "is_existing_user": true
}
```

### SMS flow

Request body:
```json
{
  "phone": "+1234567890",
  "type": "verify_phone",
  "channel": "sms"
}
```

Example response:
```json
{
  "success": true,
  "message": "Verification code sent successfully",
  "channel": "sms",
  "type": "verify_phone",
  "expires_in": 300,
  "identifier": "+1234567890",
  "is_existing_user": true
}
```

---

## 2) Resend OTP

Endpoint:
`POST /api/customer/auth/resend-otp`

### Resend email OTP

Request body:
```json
{
  "email": "customer@example.com",
  "type": "register_email_verification",
  "channel": "email"
}
```

### Resend SMS OTP

Request body:
```json
{
  "phone": "+1234567890",
  "type": "verify_phone",
  "channel": "sms"
}
```

Example response:
```json
{
  "success": true,
  "message": "Verification code sent successfully",
  "channel": "sms",
  "type": "verify_phone",
  "expires_in": 300,
  "identifier": "+1234567890",
  "is_existing_user": true
}
```

---

## 3) Verify OTP

Endpoint:
`POST /api/customer/auth/verify-otp`

### Verify email OTP

Request body:
```json
{
  "email": "customer@example.com",
  "otp": "1234",
  "type": "login_email_verification",
  "channel": "email"
}
```

Example response:
```json
{
  "success": true,
  "message": "Email verified successfully.",
  "verified": true,
  "channel": "email",
  "type": "login_email_verification"
}
```

### Verify SMS OTP

Request body:
```json
{
  "phone": "+1234567890",
  "otp": "1234",
  "type": "verify_phone",
  "channel": "sms"
}
```

Example response:
```json
{
  "success": true,
  "message": "Phone verified successfully.",
  "verified": true,
  "channel": "phone",
  "type": "verify_phone"
}
```

---

## Error response examples

### Missing required value
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

### Invalid OTP
```json
{
  "success": false,
  "message": "Invalid verification code. 2 attempts remaining."
}
```

### Expired OTP
```json
{
  "success": false,
  "message": "Verification code has expired. Please request a new one."
}
```

---

## Notes

- Email OTP is used for login/register email verification.
- SMS OTP is used for phone verification.
- `channel` controls which notification backend is used.
- OTP records are stored with `channel_type`, `type`, `email`, and `phone` to support separate validation flows.
