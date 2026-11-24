<?php

namespace Modules\Acl\Services;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Protects admin / restaurant-partner passwords while they travel from the browser
 * to the server on the web login and reset-password forms.
 *
 * Flow:
 *  1. The browser asks for a challenge: a single-use random nonce (per-submit salt)
 *     bound to the session, plus the server's RSA public key.
 *  2. The browser computes SHA-256(password), packs {nonce, hash, password} as JSON and
 *     encrypts it with RSA-OAEP. Because of the nonce and OAEP's random padding the
 *     value sent is different on every submission.
 *  3. The server decrypts it, checks SHA-256(password) matches the hash, checks the
 *     nonce belongs to this session, is unexpired and unused, then consumes it.
 *
 * Stored hashes stay bcrypt(password), so the mobile/API logins are unaffected.
 * A captured payload cannot be replayed (nonce consumed) or read without the private key.
 */
class PasswordTransportService
{
    public const ENVELOPE_PREFIX = 'v1.';

    private const SESSION_KEY = 'password_transport.nonces';
    private const MAX_ACTIVE_NONCES = 10;
    private const MAX_ENVELOPE_LENGTH = 2048;
    private const KEY_BITS = 3072;

    private ?\OpenSSLAsymmetricKey $privateKey = null;

    /**
     * Issue a new single-use nonce bound to the current session.
     *
     * @return array{nonce: string, public_key: string, algorithm: string, hash: string, expires_in: int}
     */
    public function issueChallenge(Session $session): array
    {
        $ttl = $this->ttl();
        $now = time();

        $nonces = array_filter(
            (array) $session->get(self::SESSION_KEY, []),
            fn ($expiresAt) => is_int($expiresAt) && $expiresAt > $now
        );

        // Keep only the most recent few so the session cannot grow unbounded.
        if (count($nonces) >= self::MAX_ACTIVE_NONCES) {
            asort($nonces);
            $nonces = array_slice($nonces, -(self::MAX_ACTIVE_NONCES - 1), null, true);
        }

        $nonce = $this->base64UrlEncode(random_bytes(32));
        $nonces[$nonce] = $now + $ttl;
        $session->put(self::SESSION_KEY, $nonces);

        return [
            'nonce' => $nonce,
            'public_key' => $this->publicKeyDerBase64(),
            'algorithm' => 'RSA-OAEP',
            'hash' => 'SHA-1', // OAEP padding digest supported natively by PHP's openssl_private_decrypt
            'expires_in' => $ttl,
        ];
    }

    public function isEnvelope(mixed $value): bool
    {
        return is_string($value)
            && strlen($value) <= self::MAX_ENVELOPE_LENGTH
            && str_starts_with($value, self::ENVELOPE_PREFIX);
    }

    /**
     * Decrypt an envelope and return [nonce, plainPassword]. Does not consume the nonce.
     *
     * @return array{0: string, 1: string}
     */
    public function open(string $envelope): array
    {
        if (!$this->isEnvelope($envelope)) {
            throw new RuntimeException('Invalid password envelope.');
        }

        $cipher = $this->base64UrlDecode(substr($envelope, strlen(self::ENVELOPE_PREFIX)));
        if ($cipher === null || $cipher === '') {
            throw new RuntimeException('Invalid password envelope.');
        }

        $plain = null;
        if (!openssl_private_decrypt($cipher, $plain, $this->privateKey(), OPENSSL_PKCS1_OAEP_PADDING)) {
            throw new RuntimeException('Invalid password envelope.');
        }

        $payload = json_decode((string) $plain, true);
        $nonce = $payload['n'] ?? null;
        $hash = $payload['h'] ?? null;
        $encodedPassword = $payload['p'] ?? null;

        if (
            !is_string($nonce) || $nonce === ''
            || !is_string($hash) || !preg_match('/\A[a-f0-9]{64}\z/i', $hash)
            || !is_string($encodedPassword) || $encodedPassword === ''
        ) {
            throw new RuntimeException('Invalid password envelope.');
        }

        // The password travels base64url-encoded (UTF-8 bytes) so JSON escaping cannot
        // change its size; it must match the SHA-256 hash computed in the browser.
        $password = $this->base64UrlDecode($encodedPassword);
        if ($password === null || !hash_equals(strtolower($hash), hash('sha256', $password))) {
            throw new RuntimeException('Invalid password envelope.');
        }

        return [$nonce, $password];
    }

    /**
     * Consume a nonce. Returns false if it is unknown, expired or already used.
     */
    public function consumeNonce(Session $session, string $nonce): bool
    {
        $nonces = (array) $session->get(self::SESSION_KEY, []);
        $expiresAt = $nonces[$nonce] ?? null;

        unset($nonces[$nonce]);
        $session->put(self::SESSION_KEY, $nonces);

        return is_int($expiresAt) && $expiresAt > time();
    }

    public function ttl(): int
    {
        return max(30, (int) config('acl.password_transport.nonce_ttl', 300));
    }

    private function publicKeyDerBase64(): string
    {
        $details = openssl_pkey_get_details($this->privateKey());
        $pem = $details['key'] ?? '';

        $body = preg_replace('/-----(BEGIN|END) PUBLIC KEY-----|\s+/', '', $pem);
        if (!$body) {
            throw new RuntimeException('Unable to derive password transport public key.');
        }

        return $body;
    }

    private function privateKey(): \OpenSSLAsymmetricKey
    {
        if ($this->privateKey) {
            return $this->privateKey;
        }

        $key = openssl_pkey_get_private($this->loadPrivateKeyPem());

        if (!$key) {
            throw new RuntimeException('Password transport private key is invalid.');
        }

        return $this->privateKey = $key;
    }

    /**
     * Key source, in order:
     *  - ADMIN_PASSWORD_TRANSPORT_KEY env (PEM, "\n" escapes allowed) — use this when
     *    running several app servers so they all share the same key.
     *  - A key file (generated automatically on first use).
     */
    private function loadPrivateKeyPem(): string
    {
        $fromEnv = (string) config('acl.password_transport.private_key', '');
        if ($fromEnv !== '') {
            return str_replace('\n', "\n", $fromEnv);
        }

        $path = (string) (config('acl.password_transport.private_key_path')
            ?: storage_path('app/private/password-transport.pem'));

        if (is_file($path)) {
            return (string) file_get_contents($path);
        }

        File::ensureDirectoryExists(dirname($path), 0700);

        $lock = fopen($path . '.lock', 'c');
        try {
            flock($lock, LOCK_EX);

            if (is_file($path)) {
                return (string) file_get_contents($path);
            }

            $key = openssl_pkey_new([
                'private_key_bits' => self::KEY_BITS,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);

            if (!$key || !openssl_pkey_export($key, $pem)) {
                throw new RuntimeException('Unable to generate password transport key.');
            }

            $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
            file_put_contents($tmp, $pem);
            chmod($tmp, 0600);
            rename($tmp, $path);

            return $pem;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function base64UrlEncode(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): ?string
    {
        if (!preg_match('/\A[A-Za-z0-9_-]+\z/', $value)) {
            return null;
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }
}
