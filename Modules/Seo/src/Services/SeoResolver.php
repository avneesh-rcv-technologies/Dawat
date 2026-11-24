<?php

namespace Modules\Seo\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\Seo\DTO\SeoPayload;
use Modules\Seo\Support\SeoSettings;

class SeoResolver
{
    public function __construct(
        protected SchemaBuilder $schemaBuilder,
    ) {}

    public function forKey(string $key, array $overrides = []): SeoPayload
    {
        $page = config("seo.pages.{$key}", []);
        $dbPage = app(\Modules\Seo\Repositories\SeoPageRepository::class)->findByKey($key);

        $inactive = $dbPage && ! $dbPage->is_active;
        if ($inactive) {
            // Disabled overrides still need the configured page metadata.
            $dbPage = null;
            $overrides['robots'] = 'noindex,nofollow';
        }

        $title = $dbPage?->title ?? $page['title'] ?? null;
        $description = $dbPage?->description ?? $page['description'] ?? null;
        $keywords = $dbPage?->keywords ?? null;
        $robots = $dbPage?->robots ?? $page['robots'] ?? null;
        $ogImage = $dbPage?->og_image ?? null;
        $twitterImage = $dbPage?->twitter_image ?? null;

        $routeName = $page['route'] ?? $dbPage?->route_name;
        $canonical = $dbPage?->canonical ?? fn_seo_route_url($routeName);
        if ($key === 'blogs_index' && request()->routeIs('website.blogs') && ! $dbPage?->canonical) {
            $pageNumber = filter_var(request()->query('page', 1), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($pageNumber > 1) {
                $canonical = fn_seo_route_url($routeName, ['page' => $pageNumber]);
            }
        }

        $schema = [];
        if (! empty($page['schema'])) {
            $schema = $this->schemaBuilder->build($page['schema'], [
                'title' => $title ?? '',
                'description' => $description ?? '',
            ]);
        }

        if (! empty($dbPage?->schema)) {
            $schema = $this->mergeSchemas(
                $schema,
                $this->normalizeSchemaValue($dbPage->schema)
            );
        }

        $payload = new SeoPayload(
            title: $title,
            description: $description,
            keywords: $keywords,
            canonical: $canonical,
            robots: $robots,
            ogImage: $ogImage,
            twitterImage: $twitterImage,
            schema: $schema,
        );

        return $this->applyDefaults($this->mergeOverrides($payload, $overrides));
    }

    public function forSource(string $source, Model $model, array $overrides = []): SeoPayload
    {
        $config = config("seo.sources.{$source}", []);
        $meta = $this->resolveModelMeta($model, $config);

        $slug = $meta['slug'] ?? null;
        $routeName = $config['route'] ?? null;
        $routeParam = $config['route_param'] ?? 'slug';

        $canonical = ($routeName && $slug && Route::has($routeName))
            ? route($routeName, [$routeParam => $slug])
            : null;

        $schema = [];
        if (! empty($config['schema'])) {
            $schema = $this->schemaBuilder->build(
                $config['schema'],
                $this->schemaBuilder->contextFromModel($model, $meta)
            );
        }

        // Valid custom source schema replaces the generated document, avoiding duplicate articles.
        $customSchema = ! empty($config['schema_attribute']) ? $model->{$config['schema_attribute']} : null;
        if (is_array($customSchema) && $customSchema !== []) {
            $withContext = fn (array $node) => ['@context' => 'https://schema.org', ...$node];
            $schema = $this->isList($customSchema)
                ? array_map($withContext, $customSchema)
                : $withContext($customSchema);
        }

        $payload = new SeoPayload(
            title: $meta['title'] ?? null,
            description: $meta['description'] ?? null,
            keywords: $meta['keywords'] ?? null,
            canonical: $canonical,
            robots: $meta['robots'] ?? null,
            ogImage: $meta['image'] ?? null,
            twitterImage: $meta['image'] ?? null,
            ogType: $config['og_type'] ?? null,
            schema: $schema,
        );

        return $this->applyDefaults($this->mergeOverrides($payload, $overrides));
    }

    public function forCurrentRequest(array $overrides = []): SeoPayload
    {
        $routeName = Route::currentRouteName();

        if ($routeName) {
            foreach (config('seo.pages', []) as $key => $page) {
                if (($page['route'] ?? null) === $routeName) {
                    return $this->forKey($key, $overrides);
                }
            }

            $source = $this->matchSourceFromRoute($routeName);
            if ($source) {
                $model = $this->resolveModelFromRoute($source);
                if ($model) {
                    return $this->forSource($source, $model, $overrides);
                }
            }
        }

        return $this->applyDefaults($this->mergeOverrides(new SeoPayload(), $overrides));
    }

    protected function matchSourceFromRoute(string $routeName): ?string
    {
        foreach (config('seo.sources', []) as $key => $source) {
            if (($source['route'] ?? null) === $routeName) {
                return $key;
            }
        }

        return null;
    }

    protected function resolveModelFromRoute(string $source): ?Model
    {
        $config = config("seo.sources.{$source}", []);
        $modelClass = $config['model'] ?? null;
        $routeParam = $config['route_param'] ?? 'slug';
        $slugColumn = $config['slug_column'] ?? 'slug';

        if (! $modelClass || ! class_exists($modelClass) || ! \Illuminate\Support\Facades\Schema::hasTable((new $modelClass)->getTable())) {
            return null;
        }

        $slug = request()->route($routeParam);
        if (! $slug) {
            return null;
        }

        $query = $modelClass::query()->where($slugColumn, $slug);

        if (! empty($config['scope']) && method_exists($modelClass, 'scope' . Str::studly($config['scope']))) {
            $query->{$config['scope']}();
        }

        if (! empty($config['locale_column']) && ! empty($config['default_locale'])) {
            $query->where($config['locale_column'], $config['default_locale']);
        }

        if (! empty($config['title_relation'])) {
            $query->with($config['title_relation']);
        }

        return $query->first();
    }

    protected function resolveModelMeta(Model $model, array $config): array
    {
        $data = $model;

        if (! empty($config['title_relation'])) {
            $relation = $config['title_relation'];
            $localeColumn = $config['locale_column'] ?? 'locale';
            $locale = $config['locale'] ?? $config['default_locale'] ?? 'en';

            $descriptions = $model->{$relation} ?? collect();
            $data = $descriptions instanceof \Illuminate\Support\Collection
                ? $descriptions->firstWhere($localeColumn, $locale)
                : ($model->{$relation}()->where($localeColumn, $locale)->first() ?? $model);

            if (! $data) {
                $data = $model;
            }
        }

        $titleColumn = $config['title_column'] ?? 'meta_title';
        $titleFallback = $config['title_fallback'] ?? 'title';
        $descriptionColumn = $config['description_column'] ?? 'meta_description';
        $keywordsColumn = $config['keywords_column'] ?? 'meta_keywords';
        $imageColumn = $config['image_column'] ?? null;
        $slugColumn = $config['slug_column'] ?? 'slug';

        $title = $this->nonBlank($data->{$titleColumn} ?? null)
            ?? $this->nonBlank($data->{$titleFallback} ?? null);
        $description = $this->nonBlank($data->{$descriptionColumn} ?? null);
        if ($description === null) {
            foreach ($config['description_fallback'] ?? [] as $column) {
                $summary = html_entity_decode(strip_tags($data->{$column} ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $summary = $this->nonBlank(preg_replace('/\s+/u', ' ', $summary) ?? $summary);
                if ($summary !== null) {
                    $description = Str::limit($summary, 160);
                    break;
                }
            }
        }

        $image = null;
        $imagePath = $imageColumn ? $data->{$imageColumn} : null;
        if (! empty($imagePath)) {
            $image = $this->absoluteUrl($imagePath);
        }

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $this->nonBlank($data->{$keywordsColumn} ?? null),
            'slug' => $model->{$slugColumn} ?? ($data->{$slugColumn} ?? null),
            'image' => $image,
            'robots' => $config['robots'] ?? null,
        ];
    }

    protected function applyDefaults(SeoPayload $payload): SeoPayload
    {
        $siteName = (string) SeoSettings::get('defaults.site_name', config('seo.defaults.site_name'));
        $titleSuffix = (string) SeoSettings::get('defaults.title_suffix', config('seo.defaults.title_suffix', ''));

        if ($payload->title && $titleSuffix && ! Str::endsWith($payload->title, trim($titleSuffix))) {
            $payload->title .= $titleSuffix;
        }

        $payload->description ??= (string) SeoSettings::get('defaults.description', config('seo.defaults.description'));
        $payload->robots ??= (string) SeoSettings::get('defaults.robots', config('seo.defaults.robots'));
        $payload->ogType ??= (string) data_get(SeoSettings::get('defaults.og', config('seo.defaults.og')), 'type', 'website');
        $payload->ogImage = $this->absoluteUrl($payload->ogImage) ?? $this->absoluteUrl(
            (string) SeoSettings::get('defaults.og_image', data_get(config('seo.defaults.og'), 'image'))
        );
        $payload->twitterImage = $this->absoluteUrl($payload->twitterImage) ?? $payload->ogImage;

        if (empty($payload->canonical)) {
            $payload->canonical = url()->current();
        }

        if (empty($payload->schema)) {
            $orgSchema = config('seo.defaults.organization_schema', []);
            if (! empty($orgSchema)) {
                $payload->schema = $this->schemaBuilder->build($orgSchema, [
                    'title' => $siteName,
                    'description' => $payload->description ?? '',
                ]);
            }
        }

        return $payload;
    }

    protected function normalizeSchemaValue(mixed $schema): array
    {
        if (is_array($schema)) {
            return $schema;
        }

        if (! is_string($schema) || trim($schema) === '') {
            return [];
        }

        $schema = str_replace("\u{00A0}", ' ', $schema);
        $schema = preg_replace('/[[:cntrl:]]/u', '', $schema) ?? $schema;

        $decoded = json_decode($schema, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        if (is_array($decoded)) {
            return $decoded;
        }

        if (is_string($decoded) && trim($decoded) !== '') {
            $decodedAgain = json_decode($decoded, true);

            return json_last_error() === JSON_ERROR_NONE && is_array($decodedAgain) ? $decodedAgain : [];
        }

        return [];
    }

    protected function mergeSchemas(mixed ...$schemas): array
    {
        $merged = [];

        foreach ($schemas as $schema) {
            if (empty($schema)) {
                continue;
            }

            if ($this->isList($schema)) {
                foreach ($schema as $item) {
                    if (! empty($item)) {
                        $merged[] = $item;
                    }
                }

                continue;
            }

            $merged[] = $schema;
        }

        return $merged;
    }

    protected function isList(array $value): bool
    {
        if (function_exists('array_is_list')) {
            return array_is_list($value);
        }

        return array_keys($value) === range(0, count($value) - 1);
    }

    protected function mergeOverrides(SeoPayload $payload, array $overrides): SeoPayload
    {
        foreach ($overrides as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (property_exists($payload, $key)) {
                $payload->{$key} = $value;
            }
        }

        return $payload;
    }

    protected function nonBlank(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }

    protected function absoluteUrl(?string $path): ?string
    {
        $path = $this->nonBlank($path);
        if ($path === null) {
            return null;
        }

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        if (str_starts_with($path, '//')) {
            return request()->getScheme() . ':' . $path;
        }

        return url($path);
    }
}
