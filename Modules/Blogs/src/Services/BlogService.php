<?php

namespace Modules\Blogs\Services;

use Modules\Blogs\Models\Blog;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Modules\Cache\Services\AppCache;
use Modules\Seo\Services\SitemapBuilder;

class BlogService
{
    /**
     * Get all blogs ordered by sort_order.
     */
    public function getAll(): Collection
    {
        return Blog::orderBy('sort_order')->get();
    }

    /**
     * Get a single blog by ID.
     */
    public function getById(int $id): ?Blog
    {
        return Blog::find($id);
    }

    /**
     * Create a new blog.
     */
    public function create(array $data): Blog
    {
        return DB::transaction(function () use ($data) {
            $blog = Blog::create($data);
            self::invalidateBlogCaches(
                (string) ($blog->post_url ?? ''),
                self::authorSlugFromBlog($blog)
            );

            return $blog;
        });
    }

    /**
     * Update an existing blog by ID.
     */
    public function update(int|string $id, array $data): Blog
    {
        return DB::transaction(function () use ($id, $data) {
            $blog = Blog::findOrFail($id);
            $oldSlug = (string) $blog->post_url;
            $oldAuthorSlug = self::authorSlugFromBlog($blog);

            $blog->update($data);
            $blog->refresh();

            self::invalidateBlogCaches(
                (string) $blog->post_url,
                self::authorSlugFromBlog($blog),
                $oldSlug !== (string) $blog->post_url ? $oldSlug : null,
                $oldAuthorSlug
            );

            return $blog;
        });
    }

    /**
     * Delete a single blog.
     */
    public function delete(int|string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $blog = Blog::findOrFail($id);
            $slug = (string) $blog->post_url;
            $authorSlug = self::authorSlugFromBlog($blog);
            $deleted = $blog->delete();

            self::invalidateBlogCaches($slug, $authorSlug);

            return $deleted;
        });
    }

    /**
     * Bulk delete blogs.
     */
    public function bulkDelete(array $ids): bool
    {
        return DB::transaction(function () use ($ids) {
            $blogs = Blog::query()->whereIn('id', $ids)->get(['id', 'post_url', 'author_id']);
            Blog::whereIn('id', $ids)->delete();

            foreach ($blogs as $blog) {
                self::forgetFrontendBlogDetailCache((string) $blog->post_url);
                self::forgetAuthorCaches(self::authorSlugFromBlog($blog));
            }
            self::clearFrontendBlogListCache();

            return true;
        });
    }

    /**
     * Toggle status of multiple blogs.
     */
    public function toggleStatus(array $ids, string $status): bool
    {
        return DB::transaction(function () use ($ids, $status) {
            $blogs = Blog::query()->whereIn('id', $ids)->get(['id', 'post_url', 'author_id']);
            Blog::whereIn('id', $ids)->update(['status' => $status]);

            foreach ($blogs as $blog) {
                self::forgetFrontendBlogDetailCache((string) $blog->post_url);
                self::forgetAuthorCaches(self::authorSlugFromBlog($blog));
            }
            self::clearFrontendBlogListCache();

            return true;
        });
    }

    /**
     * Invalidate one blog's detail + shared list/sidebar/category keys.
     * Does not flush the whole frontend profile (CMS pages stay cached).
     */
    public static function invalidateBlogCaches(
        string $slug,
        ?string $authorSlug = null,
        ?string $oldSlug = null,
        ?string $oldAuthorSlug = null
    ): void {
        self::forgetFrontendBlogDetailCache($slug);
        if ($oldSlug !== null && $oldSlug !== '' && $oldSlug !== $slug) {
            self::forgetFrontendBlogDetailCache($oldSlug);
        }

        self::forgetAuthorCaches($authorSlug);
        if ($oldAuthorSlug !== null && $oldAuthorSlug !== '' && $oldAuthorSlug !== $authorSlug) {
            self::forgetAuthorCaches($oldAuthorSlug);
        }

        self::clearFrontendBlogListCache();
    }

    /**
     * Clear one blog detail cache entry only.
     */
    public static function forgetFrontendBlogDetailCache(string $slug): void
    {
        if (trim($slug) === '') {
            return;
        }

        AppCache::forget('frontend', 'blogs:detail:' . $slug);
    }

    /**
     * Clear all blog detail entries only (e.g. CTA HTML changed).
     * Does not touch CMS pages or blog list keys.
     */
    public static function clearFrontendBlogDetailCache(): void
    {
        AppCache::forgetMatching('frontend', 'blogs:detail:');
    }

    /**
     * Clear blog listing / sidebar / categories only.
     * Does not flush CMS pages or other blog detail pages.
     */
    public static function clearFrontendBlogListCache(): void
    {
        AppCache::forgetMatching('frontend', 'blogs:list:');
        AppCache::forgetMatching('frontend', 'blogs:sidebar:');
        AppCache::forget('frontend', 'blogs:categories');
        DB::afterCommit(fn () => app(SitemapBuilder::class)->clearCache());
    }

    public static function forgetAuthorCaches(?string $authorSlug): void
    {
        if ($authorSlug === null || trim($authorSlug) === '') {
            return;
        }

        AppCache::forget('frontend', 'blogs:author:' . $authorSlug);
        AppCache::forget('frontend', 'blogs:author-posts:' . $authorSlug);
    }

    protected static function authorSlugFromBlog(Blog $blog): ?string
    {
        if (! $blog->author_id) {
            return null;
        }

        $author = $blog->relationLoaded('author')
            ? $blog->author
            : $blog->author()->first();

        $slug = $author?->slug ?? null;

        return is_string($slug) && $slug !== '' ? $slug : null;
    }
}
