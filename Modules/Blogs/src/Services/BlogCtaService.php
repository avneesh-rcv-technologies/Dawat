<?php

namespace Modules\Blogs\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Blogs\Models\BlogCta;

class BlogCtaService
{
    public function getAll(): Collection
    {
        return BlogCta::orderBy('name')->get();
    }

    public function getActive(): Collection
    {
        return BlogCta::active()->orderBy('name')->get();
    }

    public function getById(int $id): ?BlogCta
    {
        return BlogCta::find($id);
    }

    public function create(array $data): BlogCta
    {
        return DB::transaction(function () use ($data) {
            $cta = BlogCta::create($data);
            BlogService::clearFrontendBlogDetailCache();

            return $cta;
        });
    }

    public function update(int|string $id, array $data): BlogCta
    {
        return DB::transaction(function () use ($id, $data) {
            $cta = BlogCta::findOrFail($id);
            $cta->update($data);
            BlogService::clearFrontendBlogDetailCache();

            return $cta;
        });
    }

    public function delete(int|string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $cta = BlogCta::findOrFail($id);
            $deleted = (bool) $cta->delete();
            BlogService::clearFrontendBlogDetailCache();

            return $deleted;
        });
    }

    public function bulkDelete(array $ids): bool
    {
        return DB::transaction(function () use ($ids) {
            BlogCta::whereIn('id', $ids)->delete();
            BlogService::clearFrontendBlogDetailCache();

            return true;
        });
    }

    public function toggleStatus(array $ids, int $status): bool
    {
        return DB::transaction(function () use ($ids, $status) {
            BlogCta::whereIn('id', $ids)->update(['status' => (bool) $status]);
            BlogService::clearFrontendBlogDetailCache();

            return true;
        });
    }

    /**
     * Replace {{cta-slug}} shortcodes with active CTA HTML content.
     */
    public static function resolveShortcodes(?string $html): string
    {
        $html = (string) $html;

        if ($html === '' || ! str_contains($html, '{{')) {
            return $html;
        }

        $ctas = BlogCta::query()
            ->active()
            ->get()
            ->keyBy(fn (BlogCta $cta) => strtolower($cta->slug));

        if ($ctas->isEmpty()) {
            return $html;
        }

        return (string) preg_replace_callback(
            '/\{\{\s*([a-z0-9][a-z0-9\-_]*)\s*\}\}/i',
            function (array $matches) use ($ctas) {
                $slug = strtolower($matches[1]);
                $cta = $ctas->get($slug);

                if (! $cta || blank($cta->content)) {
                    return $matches[0];
                }

                return $cta->content;
            },
            // Also unwrap any leftover editor chips saved by mistake
            preg_replace(
                '/<span[^>]*class="[^"]*blog-cta-chip[^"]*"[^>]*data-token="\{\{\s*([^"{}]+)\s*\}\}"[^>]*>.*?<\/span>/is',
                '{{$1}}',
                $html
            ) ?? $html
        );
    }
}
