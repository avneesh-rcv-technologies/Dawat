<?php

namespace Modules\Blogs\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Blogs\Models\BlogAuthor;

class BlogAuthorService
{
    public function getAll(): Collection
    {
        return BlogAuthor::orderBy('name')->get();
    }

    public function getActive(): Collection
    {
        return BlogAuthor::active()->orderBy('name')->get();
    }

    public function getById(int $id): ?BlogAuthor
    {
        return BlogAuthor::find($id);
    }

    public function create(array $data): BlogAuthor
    {
        return DB::transaction(function () use ($data) {
            return BlogAuthor::create($data);
        });
    }

    public function update(int|string $id, array $data): BlogAuthor
    {
        return DB::transaction(function () use ($id, $data) {
            $author = BlogAuthor::findOrFail($id);
            $author->update($data);

            if (array_key_exists('status', $data)) {
                BlogService::forgetAuthorCaches($author->slug);
                BlogService::clearFrontendBlogListCache();
                BlogService::clearFrontendBlogDetailCache();
            }

            return $author;
        });
    }

    public function delete(int|string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $author = BlogAuthor::findOrFail($id);

            app(\Modules\Filemanager\Services\FileService::class)
                ->deleteFile('blog_author_avatar', $author->id);

            if ($author->avatar && Storage::disk('public')->exists($author->avatar)) {
                Storage::disk('public')->delete($author->avatar);
            }

            return (bool) $author->delete();
        });
    }

    public function bulkDelete(array $ids): bool
    {
        return DB::transaction(function () use ($ids) {
            $authors = BlogAuthor::whereIn('id', $ids)->get();
            $fileService = app(\Modules\Filemanager\Services\FileService::class);

            foreach ($authors as $author) {
                $fileService->deleteFile('blog_author_avatar', $author->id);

                if ($author->avatar && Storage::disk('public')->exists($author->avatar)) {
                    Storage::disk('public')->delete($author->avatar);
                }
            }

            BlogAuthor::whereIn('id', $ids)->delete();

            return true;
        });
    }

    public function toggleStatus(array $ids, int $status): bool
    {
        return DB::transaction(function () use ($ids, $status) {
            $authors = BlogAuthor::query()->whereIn('id', $ids)->get(['id', 'slug']);
            BlogAuthor::whereIn('id', $ids)->update(['status' => (bool) $status]);

            foreach ($authors as $author) {
                BlogService::forgetAuthorCaches($author->slug);
            }

            BlogService::clearFrontendBlogListCache();
            BlogService::clearFrontendBlogDetailCache();

            return true;
        });
    }
}
