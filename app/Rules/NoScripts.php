<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoScripts implements ValidationRule
{
    protected bool $allowHtml;

    public function __construct(bool $allowHtml = false)
    {
        $this->allowHtml = $allowHtml;
    }

    /**
     * Validate that the value does not contain scripts or dangerous HTML attributes.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $string = (string) $value;

        if ($this->allowHtml) {
            $sanitized = preg_replace('/<\s*(script|iframe|object|embed|svg|math)\b[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $string);
            $sanitized = preg_replace('/<\s*(script|iframe|object|embed|svg|math)\b[^>]*\/?>/is', '', $sanitized ?? $string);
        } else {
            $sanitized = $string;
        }

        if (preg_match('/<\s*script\b|javascript\s*:|on\w+\s*=/is', $sanitized)) {
            $fail('The :attribute contains invalid script content.');
        }
    }
}
