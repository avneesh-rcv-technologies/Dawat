<?php

namespace Modules\Website\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->secretKey();

        if (! filled($secret)) {
            $fail('reCAPTCHA is not configured.');

            return;
        }

        if (! is_string($value) || trim($value) === '') {
            $fail('Please complete the reCAPTCHA challenge.');

            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);
        } catch (\Throwable) {
            $fail('Unable to verify reCAPTCHA. Please try again.');

            return;
        }

        if (! $response->ok() || ! data_get($response->json(), 'success')) {
            $fail('reCAPTCHA verification failed. Please try again.');
        }
    }
}
