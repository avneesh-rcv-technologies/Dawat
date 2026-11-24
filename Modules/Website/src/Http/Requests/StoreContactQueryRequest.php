<?php

namespace Modules\Website\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Website\Rules\Recaptcha;

class StoreContactQueryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['first_name', 'last_name', 'email'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $this->merge([
                    $field => $field === 'email' ? Str::trim($value) : Str::squish($value),
                ]);
            }
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $recaptchaEnabled = app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled();

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'terms' => ['accepted'],
            'g-recaptcha-response' => [
                Rule::requiredIf($recaptchaEnabled),
                ...($recaptchaEnabled ? [new Recaptcha] : []),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA challenge.',
        ];
    }
}
