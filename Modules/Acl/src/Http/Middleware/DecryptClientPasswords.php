<?php

namespace Modules\Acl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Acl\Services\PasswordTransportService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Unwraps password fields sent as one-time encrypted envelopes (see PasswordTransportService)
 * and puts the plain password back into the request, so controllers/validation stay unchanged.
 *
 * Any non-empty password field on a route using this middleware MUST be an envelope;
 * plain values and replayed envelopes are rejected.
 */
class DecryptClientPasswords
{
    public function __construct(
        private PasswordTransportService $transport,
        private SanitizeRequest $sanitizer,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $fields = (array) config('acl.password_transport.fields', ['password', 'password_confirmation', 'current_password']);

        $present = [];
        foreach ($fields as $field) {
            $value = $request->input($field);
            if ($value !== null && $value !== '') {
                $present[$field] = $value;
            }
        }

        if ($present === []) {
            return $next($request);
        }

        $decrypted = [];
        $nonce = null;

        try {
            foreach ($present as $field => $value) {
                if (!$this->transport->isEnvelope($value)) {
                    return $this->reject($field);
                }

                [$fieldNonce, $password] = $this->transport->open($value);

                // Every password field in one submission must share the same nonce.
                if ($nonce !== null && !hash_equals($nonce, $fieldNonce)) {
                    return $this->reject($field);
                }

                $nonce = $fieldNonce;
                // The web group's SanitizeRequest ran before this middleware (on the envelope),
                // so apply the same field exclusions to the decrypted value. Password fields
                // are excluded so spaces and special characters reach authentication unchanged.
                $decrypted[$field] = $this->sanitizer->sanitizeValueFor($request, $field, $password);
            }
        } catch (Throwable) {
            return $this->reject(array_key_first($present));
        }

        if (!$this->transport->consumeNonce($request->session(), $nonce)) {
            return $this->reject(array_key_first($present));
        }

        $request->merge($decrypted);

        return $next($request);
    }

    private function reject(string $field): Response
    {
        $message = 'Your secure session has expired. Please submit the form again.';

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => [$field => [$message]],
        ], 422);
    }
}
