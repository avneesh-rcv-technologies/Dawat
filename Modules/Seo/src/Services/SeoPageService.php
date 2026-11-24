<?php

namespace Modules\Seo\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Modules\Seo\Repositories\SeoPageRepository;
use Modules\Seo\Support\JsonLd;

class SeoPageService
{
    public function __construct(
        protected SeoPageRepository $repository,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listPages(): array
    {
        $configPages = config('seo.pages', []);
        $overrides = $this->repository->keyed();

        $pages = [];
        foreach ($configPages as $key => $config) {
            $override = $overrides->get($key);
            $routeName = $config['route'] ?? null;

            $pages[] = [
                'key' => $key,
                'label' => $config['title'] ?? ucfirst(str_replace('_', ' ', $key)),
                'route_name' => $routeName,
                'route_url' => fn_seo_route_url($routeName),
                'has_override' => $override !== null,
                'title' => $override?->title ?? $config['title'] ?? null,
                'description' => $override?->description ?? $config['description'] ?? null,
                'robots' => $override?->robots ?? $config['robots'] ?? null,
                'og_image' => $override?->og_image ?? null,
                'twitter_image' => $override?->twitter_image ?? null,
                'schema' => $override?->schema ?? [],
                'is_active' => $override?->is_active ?? true,
            ];
        }

        return $pages;
    }

    /**
     * @return array<string, mixed>
     */
    public function pageForEdit(string $pageKey): array
    {
        $config = config("seo.pages.{$pageKey}", []);
        if ($config === []) {
            abort(404);
        }

        $override = $this->repository->findByKey($pageKey);
        $routeName = $config['route'] ?? null;

        return [
            'key' => $pageKey,
            'label' => $config['title'] ?? ucfirst(str_replace('_', ' ', $pageKey)),
            'route_name' => $routeName,
            'route_url' => fn_seo_route_url($routeName),
            'title' => $override?->title ?? $config['title'] ?? '',
            'description' => $override?->description ?? $config['description'] ?? '',
            'keywords' => $override?->keywords ?? '',
            'robots' => $override?->robots ?? $config['robots'] ?? 'index,follow',
            'og_image' => $override?->og_image ?? '',
            'twitter_image' => $override?->twitter_image ?? '',
            'schema' => $this->formatSchemaForEdit($override?->schema),
            'canonical' => $override?->canonical ?? '',
            'is_active' => $override?->is_active ?? true,
            'config_defaults' => $config,
        ];
    }

    public function save(string $pageKey, array $data): void
    {
        $config = config("seo.pages.{$pageKey}");
        if (!$config) {
            abort(404);
        }

        $this->repository->upsert($pageKey, [
            'route_name' => $config['route'] ?? null,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'keywords' => $data['keywords'] ?? null,
            'robots' => $data['robots'] ?? null,
            'og_image' => $data['og_image'] ?? null,
            'twitter_image' => $data['twitter_image'] ?? null,
            'schema' => $this->normalizeSchema($data['schema'] ?? null),
            'canonical' => $data['canonical'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        DB::afterCommit(fn () => app(SitemapBuilder::class)->clearCache());
    }

    protected function normalizeSchema(mixed $schema): ?array
    {
        if ($schema === null || (is_string($schema) && trim($schema) === '')) {
            return null;
        }

        $decoded = is_string($schema) ? json_decode($schema, true) : $schema;
        if (! JsonLd::valid($decoded)) {
            throw ValidationException::withMessages([
                'schema' => 'Schema must be valid JSON-LD with a type, identifier, or graph.',
            ]);
        }

        return $decoded;
    }

    protected function formatSchemaForEdit(mixed $schema): string
    {
        if (is_string($schema)) {
            return $schema;
        }

        if (is_array($schema) && $schema !== []) {
            return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
        }

        return '';
    }
}
