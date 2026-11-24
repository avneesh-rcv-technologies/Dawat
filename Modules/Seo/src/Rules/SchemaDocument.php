<?php

namespace Modules\Seo\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Modules\Seo\Support\JsonLd;

class SchemaDocument implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! JsonLd::valid(json_decode($value, true))) {
            $fail('The :attribute must be valid JSON-LD with a type, identifier, or graph.');
        }
    }
}
