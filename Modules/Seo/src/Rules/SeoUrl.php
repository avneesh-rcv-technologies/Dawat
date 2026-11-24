<?php

namespace Modules\Seo\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SeoUrl implements ValidationRule
{
    public function __construct(private bool $allowRelative = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! $this->valid($value)) {
            $fail($this->allowRelative
                ? 'The :attribute must be an HTTP(S) image URL or a relative image path.'
                : 'The :attribute must be an absolute HTTP(S) URL without credentials or a fragment.');
        }
    }

    private function valid(string $value): bool
    {
        if ($value === '' || preg_match('/[\s\\\\\x00-\x1f\x7f]/u', $value)) {
            return false;
        }
        if ($this->allowRelative && str_starts_with($value, '//')) {
            $value = 'https:'.$value;
        }
        if (! preg_match('/^[a-z][a-z0-9+.-]*:/i', $value)) {
            return $this->allowRelative && ! str_starts_with($value, '//')
                && ! in_array($value[0], ['?', '#'], true) && ! str_contains($value, '#');
        }
        $parts = parse_url($value);

        return filter_var($value, FILTER_VALIDATE_URL) !== false && is_array($parts)
            && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && ! isset($parts['user']) && ! isset($parts['pass']) && ! isset($parts['fragment']);
    }
}
