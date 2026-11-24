<?php

namespace Modules\GeneralSetting\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CaptchaSettings
{
    public function siteKey(): string
    {
        return (string) fn_get_setting('captcha.site_key', config('services.google_recaptcha.site_key', ''));
    }

    public function secretKey(): string
    {
        $encrypted = fn_get_setting('captcha.secret_key');

        return filled($encrypted)
            ? Crypt::decryptString($encrypted)
            : (string) config('services.google_recaptcha.secret', '');
    }

    public function enabled(): bool
    {
        return (bool) fn_get_setting('captcha.enabled', filled($this->siteKey()) && filled($this->secretKey()));
    }

    /**
     * Validate the CAPTCHA fields before any settings are written.
     */
    public function prepareForSave(array $settings): array
    {
        if (! array_intersect(['captcha.enabled', 'captcha.site_key', 'captcha.secret_key'], array_keys($settings))) {
            return $settings;
        }

        $data = [
            'enabled' => $settings['captcha.enabled'] ?? $this->enabled(),
            'site_key' => $settings['captcha.site_key'] ?? $this->siteKey(),
            'secret_key' => $settings['captcha.secret_key'] ?? null,
        ];

        foreach (['site_key', 'secret_key'] as $key) {
            if (is_string($data[$key])) {
                $data[$key] = trim($data[$key]);
            }
        }

        $validator = Validator::make($data, [
            'enabled' => ['required', 'boolean'],
            'site_key' => [Rule::requiredIf((bool) $data['enabled']), 'nullable', 'string', 'max:255'],
            'secret_key' => [Rule::requiredIf((bool) $data['enabled'] && blank($this->secretKey())), 'nullable', 'string', 'max:255'],
        ], [], [
            'site_key' => 'reCAPTCHA site key',
            'secret_key' => 'reCAPTCHA secret key',
        ]);

        if ($validator->fails()) {
            throw \Illuminate\Validation\ValidationException::withMessages(
                collect($validator->errors()->messages())
                    ->mapWithKeys(fn ($messages, $key) => ['settings.captcha.'.$key => $messages])
                    ->all()
            );
        }

        $settings['captcha.enabled'] = (bool) $data['enabled'];
        $settings['captcha.site_key'] = trim((string) $data['site_key']);
        $secret = filled($data['secret_key']) ? trim($data['secret_key']) : $this->secretKey();
        unset($settings['captcha.secret_key']);

        if (filled($secret)) {
            $settings['captcha.secret_key'] = Crypt::encryptString($secret);
        }

        return $settings;
    }
}
