<?php

namespace Modules\Seo\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\Cache\Services\AppCache;
use Modules\Seo\Repositories\SeoPageRepository;
use Modules\Seo\Support\SeoSettings;

class SitemapBuilder
{
    public function build(): string
    {
        if (! SeoSettings::get('sitemap.enabled', config('seo.sitemap.enabled', true))) {
            return $this->emptySitemap();
        }

        $cacheEnabled = (bool) SeoSettings::get('settings.cache_sitemap', config('seo.settings.cache_sitemap', true));
        $cacheTtl = (int) SeoSettings::get('settings.cache_ttl', config('seo.settings.cache_ttl', 3600));

        if ($cacheEnabled) {
            return AppCache::remember('seo', $this->cacheKey(), fn () => $this->generate(), $cacheTtl);
        }

        return $this->generate();
    }

    public function clearCache(): void
    {
        AppCache::forget('seo', $this->cacheKey());
    }

    public function generateFresh(): string
    {
        $this->clearCache();

        return $this->build();
    }

    /**
     * @return array{url_count: int, cached: bool, enabled: bool, last_generated_at: ?string}
     */
    public function stats(): array
    {
        $xml = $this->build();

        return [
            'url_count' => $this->countUrls($xml),
            'cached' => AppCache::has('seo', $this->cacheKey()),
            'enabled' => (bool) SeoSettings::get('sitemap.enabled', config('seo.sitemap.enabled', true)),
            'last_generated_at' => fn_get_seo_setting('sitemap.last_generated_at'),
            'last_url_count' => fn_get_seo_setting('sitemap.last_url_count'),
        ];
    }

    public function countUrls(string $xml): int
    {
        return substr_count($xml, '<loc>');
    }

    protected function cacheKey(): string
    {
        // Do not reuse old snapshots generated with request hosts or previous deployment URLs.
        return 'sitemap.xml:v2:' . sha1(rtrim((string) config('app.url'), '/'));
    }

    protected function generate(): string
    {
        $urls = [];
        $maxUrls = (int) config('seo.sitemap.max_urls', 50000);
        $excludePatterns = config('seo.sitemap.exclude_routes', []);
        if ($maxUrls < 1) {
            return $this->emptySitemap();
        }
        $overrides = app(SeoPageRepository::class)->keyed();

        if (config('seo.sitemap.include_static_pages', true)) {
            foreach (config('seo.pages', []) as $key => $page) {
                $routeName = $page['route'] ?? null;
                if (! $routeName || ! Route::has($routeName)) {
                    continue;
                }

                if (($page['sitemap'] ?? true) === false) {
                    continue;
                }

                if ($this->isExcludedRoute($routeName, $excludePatterns)) {
                    continue;
                }

                $override = $overrides->get($key);
                $robots = $override?->robots ?? $page['robots'] ?? SeoSettings::get('defaults.robots', 'index,follow');
                if (($override && ! $override->is_active) || $this->isNoindex($robots)) {
                    continue;
                }

                $canonical = $override?->canonical ?? $page['canonical'] ?? null;
                $loc = $this->validUrl(trim((string) $canonical) !== '' ? $canonical : $this->routeUrl($routeName));
                if (! $loc) {
                    continue;
                }

                $urls[] = [
                    'loc' => $loc,
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ];

                if (count($urls) >= $maxUrls) {
                    break;
                }
            }
        }

        if (count($urls) < $maxUrls) {
            foreach (config('seo.sitemap.include_sources', []) as $sourceKey) {
                $urls = array_merge($urls, $this->urlsForSource($sourceKey, $maxUrls - count($urls)));

                if (count($urls) >= $maxUrls) {
                    break;
                }
            }
        }

        $unique = [];
        foreach ($urls as $entry) {
            $unique[$entry['loc']] ??= $entry;
        }

        return $this->toXml(array_values($unique));
    }

    protected function urlsForSource(string $sourceKey, int $limit): array
    {
        $config = config("seo.sources.{$sourceKey}");
        if (! $config || empty($config['model']) || ! class_exists($config['model'])
            || ! \Illuminate\Support\Facades\Schema::hasTable((new $config['model'])->getTable())) {
            return [];
        }

        $modelClass = $config['model'];
        $query = $modelClass::query();

        if (! empty($config['scope']) && method_exists($modelClass, 'scope' . Str::studly($config['scope']))) {
            $query->{$config['scope']}();
        }

        if (! empty($config['status_column']) && isset($config['status_value'])) {
            $query->where($config['status_column'], $config['status_value']);
        }

        if (! empty($config['locale_column']) && ! empty($config['default_locale'])) {
            $query->where($config['locale_column'], $config['default_locale']);
        }

        $slugColumn = $config['slug_column'] ?? 'slug';
        $routeName = $config['route'] ?? null;
        $routeParam = $config['route_param'] ?? 'slug';
        $sitemapMeta = $config['sitemap'] ?? [];

        if (! $routeName || ! Route::has($routeName)) {
            return [];
        }
        if (($config['sitemap'] ?? true) === false
            || $this->isExcludedRoute($routeName, config('seo.sitemap.exclude_routes', []))
            || $this->isNoindex($config['robots'] ?? SeoSettings::get('defaults.robots', 'index,follow'))) {
            return [];
        }

        $urls = [];
        $updatedColumn = $config['updated_column'] ?? 'updated_at';

        foreach ($query->limit($limit)->get() as $model) {
            $slug = $model->{$slugColumn} ?? null;
            if (! $slug) {
                continue;
            }
            $loc = $this->routeUrl($routeName, [$routeParam => $slug]);
            if (! $loc) {
                continue;
            }

            $entry = [
                'loc' => $loc,
                'changefreq' => $sitemapMeta['changefreq'] ?? 'weekly',
                'priority' => (string) ($sitemapMeta['priority'] ?? 0.5),
            ];

            if ($model->{$updatedColumn} ?? null) {
                $entry['lastmod'] = $model->{$updatedColumn}->toAtomString();
            }

            $urls[] = $entry;
        }

        return $urls;
    }

    protected function routeUrl(string $routeName, array $parameters = []): ?string
    {
        $route = Route::getRoutes()->getByName($routeName);
        if (! $route || array_diff($route->parameterNames(), array_keys($parameters)) !== []) {
            return null;
        }

        $base = $this->validUrl(rtrim((string) config('app.url'), '/'));
        if (! $base) {
            return null;
        }

        $path = route($routeName, $parameters, false);

        return $this->validUrl($base . ($path === '/' ? '' : '/' . ltrim($path, '/')));
    }

    protected function validUrl(?string $url): ?string
    {
        $url = $url === null ? '' : trim($url);
        if ($url === '' || strlen($url) > 2048 || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        if (! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
            return null;
        }

        return $url;
    }

    protected function isNoindex(?string $robots): bool
    {
        return (bool) preg_match('/(?:^|[\s,])(?:noindex|none)(?:$|[\s,])/i', $robots ?? '');
    }

    protected function isExcludedRoute(string $routeName, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    protected function toXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . e($url['loc']) . '</loc>' . PHP_EOL;

            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . e($url['lastmod']) . '</lastmod>' . PHP_EOL;
            }

            if (! empty($url['changefreq'])) {
                $xml .= '    <changefreq>' . e($url['changefreq']) . '</changefreq>' . PHP_EOL;
            }

            if (isset($url['priority'])) {
                $xml .= '    <priority>' . e($url['priority']) . '</priority>' . PHP_EOL;
            }

            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return $xml;
    }

    protected function emptySitemap(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
    }
}
