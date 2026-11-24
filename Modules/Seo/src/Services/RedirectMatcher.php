<?php

namespace Modules\Seo\Services;

use Illuminate\Support\Str;

class RedirectMatcher
{
    public function match(string $path, array $redirects): ?array
    {
        $normalizedPath = $this->normalizePath($path);

        foreach ($redirects as $redirect) {
            $from = $redirect['from_path'] ?? $redirect['from'] ?? null;
            $to = $redirect['to_url'] ?? $redirect['to'] ?? null;

            if (! $from || ! $to) {
                continue;
            }

            $matchType = $redirect['match_type'] ?? $redirect['match'] ?? 'exact';
            $status = (int) ($redirect['status_code'] ?? $redirect['status'] ?? 301);

            if ($this->matches($normalizedPath, $from, $matchType)) {
                return [
                    'to_url' => $this->resolveDestination($normalizedPath, $from, $to, $matchType),
                    'status_code' => $status,
                    'id' => $redirect['id'] ?? null,
                    'preserve_query' => $redirect['preserve_query'] ?? null,
                ];
            }
        }

        return null;
    }

    public function matches(string $path, string $from, string $matchType): bool
    {
        $from = $this->normalizePath($from);
        $path = $this->normalizePath($path);

        return match ($matchType) {
            'prefix' => Str::startsWith($path, rtrim($from, '/')),
            'wildcard' => $this->matchWildcard($path, $from),
            'regex' => (bool) @preg_match($from, $path),
            'pattern' => $this->matchPattern($path, $from),
            default => $path === $from,
        };
    }

    protected function matchWildcard(string $path, string $pattern): bool
    {
        $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#';

        return (bool) preg_match($regex, $path);
    }

    protected function matchPattern(string $path, string $pattern): bool
    {
        return (bool) preg_match($this->patternToRegex($pattern), $path);
    }

    protected function patternToRegex(string $pattern): string
    {
        $parts = preg_split('/(\{[a-zA-Z0-9_]+\})/', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE);
        $regex = '';

        foreach ($parts as $part) {
            if (preg_match('/^\{([a-zA-Z0-9_]+)\}$/', $part, $matches)) {
                $regex .= '(?P<' . $matches[1] . '>[^/]+)';
            } else {
                $regex .= preg_quote($part, '#');
            }
        }

        return '#^' . $regex . '$#';
    }

    protected function resolveDestination(string $path, string $from, string $to, string $matchType): string
    {
        if ($matchType === 'pattern') {
            if (preg_match($this->patternToRegex($from), $path, $matches)) {
                return preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($m) use ($matches) {
                    return $matches[$m[1]] ?? $m[0];
                }, $to);
            }
        }

        if ($matchType === 'prefix' && Str::startsWith($path, rtrim($from, '/'))) {
            return $to . Str::after($path, rtrim($from, '/'));
        }

        return $to;
    }

    public function normalizePath(string $path): string
    {
        $caseSensitive = (bool) config('seo.redirects.case_sensitive', false);
        $trailingSlash = config('seo.redirects.trailing_slash', 'ignore');

        $path = '/' . ltrim($path, '/');

        if (! $caseSensitive) {
            $path = Str::lower($path);
        }

        if ($trailingSlash === 'remove') {
            $path = rtrim($path, '/') ?: '/';
        } elseif ($trailingSlash === 'add' && $path !== '/') {
            $path = rtrim($path, '/') . '/';
        }

        return $path;
    }

    public function wouldCreateLoop(string $fromPath, string $toUrl, array $existingRedirects): bool
    {
        $from = $this->normalizePath($fromPath);
        $to = $this->normalizePath(parse_url($toUrl, PHP_URL_PATH) ?: $toUrl);

        if ($from === $to) {
            return true;
        }

        foreach ($existingRedirects as $redirect) {
            $existingFrom = $this->normalizePath($redirect['from_path'] ?? $redirect['from'] ?? '');
            $existingTo = $this->normalizePath(parse_url($redirect['to_url'] ?? $redirect['to'] ?? '', PHP_URL_PATH) ?: '');

            if ($existingFrom === $to && $existingTo === $from) {
                return true;
            }
        }

        return false;
    }
}
