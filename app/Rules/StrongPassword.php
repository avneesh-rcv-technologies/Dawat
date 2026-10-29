<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        if (!is_string($value)) {
            $fail('The password must be a string.');
            return;
        }

        // Minimum 8 and maximum 16 characters
        if (strlen($value) < 8 || strlen($value) > 16) {
            $fail('The password must be between 8 and 16 characters.');
            return;
        }

        // Must not contain only spaces
        if (!preg_match('/\S/', $value)) {
            $fail('The password cannot contain only spaces.');
            return;
        }

        // Must contain an uppercase letter
        if (!preg_match('/[A-Z]/', $value)) {
            $fail('The password must contain an uppercase letter.');
        }

        // Must contain a lowercase letter
        if (!preg_match('/[a-z]/', $value)) {
            $fail('The password must contain a lowercase letter.');
        }

        // Must contain a number
        if (!preg_match('/[0-9]/', $value)) {
            $fail('The password must contain a number.');
        }

        // Must contain a special character
        if (!preg_match('/[^a-zA-Z0-9\s]/', $value)) {
            $fail('The password must contain a special character.');
        }
    }
}