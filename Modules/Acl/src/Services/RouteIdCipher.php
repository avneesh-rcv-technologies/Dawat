<?php

namespace Modules\Acl\Services;

use Illuminate\Support\Str;
use RuntimeException;

/**
 * Turns numeric record IDs into opaque, tamper-proof URL tokens and back.
 *
 * Uses PHP's openssl_encrypt / openssl_decrypt (AES-256-GCM) with a key derived from
 * APP_KEY. GCM authenticates the token, so an edited or guessed token fails to decrypt
 * instead of resolving to another record. Each token uses a random IV, so the same ID
 * gives a different URL every time. Tokens are base64url (safe in a path segment).
 *
 * Encrypting IDs hides them and stops users from walking IDs in the URL (30 -> 31), which
 * makes IDOR harder. It does not replace authorization: controllers must still check that
 * the current user may access the record.
 */
class RouteIdCipher
{
    private const CIPHER = 'aes-256-gcm';

    private const IV_LENGTH = 12;

    private const TAG_LENGTH = 16;

    private const AAD = 'dawat.route-id.v1';

    private string $key;

    public function __construct(string $appKey)
    {
        if ($appKey === '') {
            throw new RuntimeException('APP_KEY is required to encrypt route IDs.');
        }
        if (Str::startsWith($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7));
        }

        // Separate key for this purpose, so route tokens never share a key with sessions/cookies.
        $this->key = hash_hmac('sha256', self::AAD, $appKey, true);
    }

    public function encrypt(int|string $id): string
    {
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        $cipherText = openssl_encrypt((string) $id, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv, $tag, self::AAD, self::TAG_LENGTH);

        if ($cipherText === false) {
            throw new RuntimeException('Unable to encrypt route ID.');
        }

        return rtrim(strtr(base64_encode($iv . $tag . $cipherText), '+/', '-_'), '=');
    }

    /** Returns the original ID, or null when the token is invalid, edited or not one of ours. */
    public function decrypt(?string $token): ?int
    {
        if ($token === null || $token === '' || strlen($token) > 128 || !preg_match('/^[A-Za-z0-9_-]+$/', $token)) {
            return null;
        }

        $raw = base64_decode(strtr($token, '-_', '+/'), true);
        if ($raw === false || strlen($raw) <= self::IV_LENGTH + self::TAG_LENGTH) {
            return null;
        }

        $iv = substr($raw, 0, self::IV_LENGTH);
        $tag = substr($raw, self::IV_LENGTH, self::TAG_LENGTH);
        $cipherText = substr($raw, self::IV_LENGTH + self::TAG_LENGTH);

        $plain = openssl_decrypt($cipherText, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv, $tag, self::AAD);

        if ($plain === false || !ctype_digit($plain)) {
            return null;
        }

        return (int) $plain;
    }
}
