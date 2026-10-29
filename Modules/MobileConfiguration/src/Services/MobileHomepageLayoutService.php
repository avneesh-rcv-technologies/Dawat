<?php

namespace Modules\MobileConfiguration\Services;

use Illuminate\Http\Request;
use Modules\MobileConfiguration\Models\MobileHomepageLayout;
use Modules\MobileConfiguration\Repositories\Interfaces\MobileHomepageLayoutRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class MobileHomepageLayoutService
{
    public function __construct(
        protected MobileHomepageLayoutRepositoryInterface $repository
    ) {
    }

    // --- caching helpers, similar to GeneralSetting ---
    protected function cacheKey(string $suffix = ''): string
    {
        return 'mobileconfiguration.homepage_layouts' . ($suffix ? ".{$suffix}" : '');
    }

    protected function cacheEnabled(): bool
    {
        return (bool) config('mobileconfiguration.homepage_layouts.cache', true);
    }

    protected function cacheTtl(): int
    {
        return (int) config('mobileconfiguration.homepage_layouts.cache_ttl', 3600);
    }

    /**
     * Clear cached layout entries.
     *
     * @param string[] $extraSlugs additional slugs to forget (e.g. previous value)
     */
    protected function clearCache(array $extraSlugs = []): void
    {
        if (!$this->cacheEnabled()) {
            return;
        }

        // forget the default key first
        Cache::forget($this->cacheKey('default'));

        // forget any slug-specific entries currently in the database
        try {
            $slugs = $this->repository->getAllSlugs();
        } catch (\Exception $e) {
            // if repository fails for some reason, just continue with extras
            $slugs = [];
        }

        foreach ($slugs as $slug) {
            Cache::forget($this->cacheKey("slug.{$slug}"));
        }

        // also clear any manually supplied slugs (e.g. before rename)
        foreach ($extraSlugs as $slug) {
            Cache::forget($this->cacheKey("slug.{$slug}"));
        }
    }

    public function create(Request $request): MobileHomepageLayout
    {
        $data = $this->prepareData($request);
        $layout = $this->repository->create($data);
        $this->clearCache();
        return $layout;
    }

    public function update(Request $request, MobileHomepageLayout $layout): MobileHomepageLayout
    {
        // remember old slug in case it changes
        $oldSlug = $layout->slug;

        $data = $this->prepareData($request, $layout->id);
        $layout = $this->repository->update($layout, $data);

        // clear cache for all keys; include old slug to remove stale key
        $this->clearCache([$oldSlug]);
        return $layout;
    }

    public function delete(MobileHomepageLayout $layout): void
    {
        $slug = $layout->slug;
        $this->repository->delete($layout);
        $this->clearCache([$slug]);
    }

    public function bulkDelete(array $ids): void
    {
        $this->repository->bulkDelete($ids);
        $this->clearCache();
    }

    public function updateStatus(array $ids, bool $status): void
    {
        $this->repository->updateStatus($ids, $status);
        $this->clearCache();
    }

    public function getActiveLayouts()
    {
        return $this->repository->getActiveLayouts();
    }

    public function getPaginatedLayouts(int $perPage = 15)
    {
        return $this->repository->getPaginatedLayouts($perPage);
    }

    public function getBySlug(string $slug): ?MobileHomepageLayout
    {
        return $this->repository->getBySlug($slug);
    }

    public function getDefaultLayout(): ?MobileHomepageLayout
    {
        return $this->repository->getDefaultLayout();
    }

    public function setAsDefault(int $id): void
    {
        $this->repository->setAsDefault($id);
    }

    public function getLayoutWithData(string $slug = null): ?array
    {
        if ($this->cacheEnabled()) {
            $key = $this->cacheKey($slug ? "slug.{$slug}" : 'default');

            return Cache::remember($key, $this->cacheTtl(), fn() => $this->getLayoutWithDataNoCache($slug));
        }

        return $this->getLayoutWithDataNoCache($slug);
    }

    protected function getLayoutWithDataNoCache(string $slug = null): ?array
    {
        $layout = $slug ? $this->getBySlug($slug) : $this->getDefaultLayout();

        if (!$layout) {
            return null;
        }

        return [
            'layout' => [
                'id' => $layout->id,
                'name' => $layout->name,
                'slug' => $layout->slug,
                'description' => $layout->description,
                'is_default' => $layout->is_default,
                'status' => $layout->status,
                'created_at' => $layout->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $layout->updated_at?->format('Y-m-d H:i:s'),
            ],
            'sections' => $layout->getLayoutWithData()
        ];
    }

    protected function prepareData(Request $request, ?int $excludeId = null): array
    {
        $data = $request->only([
            'name',
            'slug',
            'description',
            'is_default',
            'status',
            'position',
        ]);

        // Convert boolean fields
        $data['is_default'] = $request->boolean('is_default');
        $data['status'] = $request->boolean('status');

        // Prepare layout configuration
        $layoutConfig = [];
        $sections = $request->input('sections', []);

        // Debug: Log received sections
        \Log::info('Service received sections:', $sections);

        // Sort sections by index to maintain order
        ksort($sections);

        foreach ($sections as $index => $section) {
            $sectionConfig = [
                'type' => $section['type'],
                'position' => $index,
                'config' => []
            ];

            if ($section['type'] === 'banners') {
                $bannerIds = array_filter($section['banner_ids'] ?? []);
                $sectionConfig['config'] = [
                    'banner_ids' => array_values($bannerIds), // Ensure sequential array
                    'display_type' => 'carousel', // Always carousel for banners
                    'auto_scroll' => true,
                    'show_indicators' => true,
                ];
                \Log::info("Banner section {$index}:", $sectionConfig['config']);
            } elseif ($section['type'] === 'features') {
                $featureIds = array_filter($section['feature_ids'] ?? []);
                $sectionConfig['config'] = [
                    'feature_ids' => array_values($featureIds), // Ensure sequential array
                    // Features use their existing used_for setting (carousel/general)
                    // No additional configuration needed
                ];
                \Log::info("Feature section {$index}:", $sectionConfig['config']);
            }

            // Only add section if it has items
            if (!empty($sectionConfig['config']['banner_ids'] ?? []) || !empty($sectionConfig['config']['feature_ids'] ?? [])) {
                $layoutConfig[] = $sectionConfig;
            }
        }

        $data['layout_config'] = $layoutConfig;
        \Log::info('Final layout config:', $layoutConfig);

        return $data;
    }
}