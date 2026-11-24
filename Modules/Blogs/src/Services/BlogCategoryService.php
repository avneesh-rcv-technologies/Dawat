<?php

namespace Modules\Blogs\Services;

use Modules\Blogs\Models\BlogCategory;
use Modules\Blogs\Models\Blog;

class BlogCategoryService
{
    /**
     * Get all categories with optional pagination.
     *
     * @param int|null $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getAll($perPage = null)
    {
        $query = BlogCategory::with('parent', 'children')
            ->orderBy('position', 'asc');

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Create a new category.
     */
    public function create(array $data): BlogCategory
    {
        unset($data['banner_path']);

        return BlogCategory::create($data);
    }

    /**
     * Update an existing category.
     */
    public function update(int $id, array $data): BlogCategory
    {
        $category = BlogCategory::findOrFail($id);

        unset($data['banner_path']);

        $category->update($data);

        return $category;
    }

    /**
     * Delete a category and detach it from blogs.
     */
    public function delete(int $id): ?bool
    {
        $category = BlogCategory::findOrFail($id);

        app(\Modules\Filemanager\Services\FileService::class)
            ->deleteFile('blog_category_banner', $category->id);

        Blog::where('category_id', $id)->update(['category_id' => null]);

        return $category->delete();
    }

    /**
     * Bulk delete categories and detach from blogs.
     */
    public function bulkDelete(array $ids): void
    {
        $fileService = app(\Modules\Filemanager\Services\FileService::class);

        foreach ($ids as $id) {
            $fileService->deleteFile('blog_category_banner', $id);
        }

        Blog::whereIn('category_id', $ids)->update(['category_id' => null]);
        BlogCategory::whereIn('id', $ids)->delete();
    }

    /**
     * Toggle status of multiple categories.
     */
    public function toggleStatus(array $ids, int $status): void
    {
        BlogCategory::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Get all blogs for a specific category.
     */
    public function getBlogsForCategory(int $categoryId)
    {
        return Blog::where('category_id', $categoryId)->get();
    }

    /**
     * Assign a category to a blog.
     */
    public function assignCategoryToBlog(int $blogId, int $categoryId): Blog
    {
        $blog = Blog::findOrFail($blogId);
        $blog->category_id = $categoryId;
        $blog->save();

        return $blog;
    }

    /**
     * Remove category from a blog.
     */
    public function removeCategoryFromBlog(int $blogId): Blog
    {
        $blog = Blog::findOrFail($blogId);
        $blog->category_id = null;
        $blog->save();

        return $blog;
    }
}
