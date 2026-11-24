<?php

namespace Modules\Seo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Seo\Repositories\RedirectRepository;
use Modules\Seo\Services\RedirectMatcher;

class StoreRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('from_path')) {
            $matcher = app(RedirectMatcher::class);
            $this->merge([
                'from_path' => $matcher->normalizePath((string) $this->input('from_path')),
            ]);
        }
    }

    public function rules(): array
    {
        $redirectId = $this->route('redirect')?->id;

        return [
            'from_path' => [
                'required',
                'string',
                'max:255',
                'starts_with:/',
                Rule::unique('seo_redirects', 'from_path')->ignore($redirectId),
            ],
            'to_url' => ['required', 'string', 'max:255'],
            'status_code' => ['required', 'integer', Rule::in([301, 302, 307, 308])],
            'match_type' => ['required', 'string', Rule::in(['exact', 'prefix', 'wildcard', 'regex', 'pattern'])],
            'is_active' => ['nullable', 'boolean'],
            'preserve_query' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $from = $this->input('from_path');
            $to = $this->input('to_url');

            if (! $from || ! $to) {
                return;
            }

            $matcher = app(RedirectMatcher::class);
            $normalizedFrom = $matcher->normalizePath($from);
            $normalizedTo = $matcher->normalizePath(parse_url($to, PHP_URL_PATH) ?: $to);

            if ($normalizedFrom === $normalizedTo) {
                $validator->errors()->add('to_url', 'Destination must differ from source path.');
            }

            $repository = app(RedirectRepository::class);
            $existing = $repository->activeRedirects();
            $redirectId = $this->route('redirect')?->id;

            if ($redirectId) {
                $existing = array_values(array_filter($existing, fn ($item) => ($item['id'] ?? null) !== $redirectId));
            }

            if ($matcher->wouldCreateLoop($from, $to, $existing)) {
                $validator->errors()->add('to_url', 'This redirect would create a loop.');
            }
        });
    }
}
