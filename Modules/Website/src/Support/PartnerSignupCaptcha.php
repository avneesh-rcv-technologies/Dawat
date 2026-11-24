<?php

namespace Modules\Website\Support;

use Illuminate\Support\Facades\Crypt;

class PartnerSignupCaptcha
{
    public const LIFETIME = 1800;

    public function issue(string $email): string
    {
        return Crypt::encryptString(json_encode([
            'purpose' => 'partner-signup-captcha',
            'email' => strtolower(trim($email)),
            'expires_at' => now()->timestamp + self::LIFETIME,
        ], JSON_THROW_ON_ERROR));
    }

    public function valid(mixed $token, mixed $email): bool
    {
        if (! is_string($token) || ! is_string($email)) {
            return false;
        }

        try {
            $data = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);

            return ($data['purpose'] ?? null) === 'partner-signup-captcha'
                && ($data['email'] ?? null) === strtolower(trim($email))
                && ($data['expires_at'] ?? 0) > now()->timestamp;
        } catch (\Throwable) {
            return false;
        }
    }
}
