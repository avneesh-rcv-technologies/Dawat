<?php

namespace Modules\Seo\Support;

class SeoRequestCache
{
    public static function remember(string $key, callable $callback): mixed
    {
        // Console commands/queue workers have no HTTP request boundary for this memoization.
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return $callback();
        }

        // Request attributes are discarded between HTTP requests, including in persistent workers.
        $attributes = request()->attributes;
        $values = $attributes->get('seo.request_cache', []);
        if (! array_key_exists($key, $values)) {
            $value = $callback();
            $values = $attributes->get('seo.request_cache', []);
            $values[$key] = $value;
            $attributes->set('seo.request_cache', $values);
        }

        return $values[$key];
    }

    public static function forget(string $key): void
    {
        $values = request()->attributes->get('seo.request_cache', []);
        unset($values[$key]);
        request()->attributes->set('seo.request_cache', $values);
    }
}
