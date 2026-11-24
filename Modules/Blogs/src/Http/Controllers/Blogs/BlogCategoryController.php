<?php

namespace Modules\Blogs\Http\Controllers\Blogs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Blogs\Models\BlogCategory;
use Modules\Blogs\Services\BlogCategoryService;
use Modules\Blogs\DataView\Blogs\BlogCategory as BlogCategoryGrid;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    protected BlogCategoryService $categoryService;

    public function __construct(BlogCategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(BlogCategoryGrid::class)->process();
        return view('blogs::categories.index', compact('lists'));
    }

    public function create()
    {
        $categories = BlogCategory::all();
        return view('blogs::categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'position'    => 'nullable|integer',
            'status'      => 'required|in:0,1',
            'parent_id'   => 'nullable|exists:blog_categories,id',
            'banner_path' => fn_image_validation_rules(),
            'slug'        => 'nullable|string|max:255',
        ];

        $validated = $request->validate($rules, fn_image_validation_messages('banner_path'));

        // Generate unique slug
        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? $validated['name']
        );

        if (array_key_exists('parent_id', $validated) && blank($validated['parent_id'])) {
            $validated['parent_id'] = null;
        }

        unset($validated['banner_path']);

        $category = $this->categoryService->create($validated);

        if (fn_upload_request_file($request, 'banner_path', 'blog_category_banner', $category->id, true) && $category->banner_path) {
            $category->forceFill(['banner_path' => null])->save();
        }

        // Handle AJAX request
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'redirect_url' => route('admin.blogs.blog_categories.index'),
            ]);
        }

        return redirect()
            ->route('admin.blogs.blog_categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit($id)
    {
        $category = BlogCategory::findOrFail($id);
        return view('blogs::categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = BlogCategory::findOrFail($id);

        $rules = [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'position'    => 'nullable|integer',
            'status'      => 'required|in:0,1',
            'parent_id'   => 'nullable|exists:blog_categories,id',
            'banner_path' => fn_image_validation_rules(),
            'slug'        => 'nullable|string|max:255',
        ];

        $validated = $request->validate($rules, fn_image_validation_messages('banner_path'));

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? $validated['name'],
            $category->id
        );

        if (array_key_exists('parent_id', $validated) && blank($validated['parent_id'])) {
            $validated['parent_id'] = null;
        }

        unset($validated['banner_path']);

        $this->categoryService->update($id, $validated);

        if (fn_upload_request_file($request, 'banner_path', 'blog_category_banner', $category->id, true) && $category->banner_path) {
            $category->forceFill(['banner_path' => null])->save();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'redirect_url' => route('admin.blogs.blog_categories.index'),
            ]);
        }

        return redirect()
            ->route('admin.blogs.blog_categories.index')
            ->with('success', 'Category updated successfully.');
    }

     public function destroy($id)
    {
        try {
            $this->categoryService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
                'redirect_url' => route('admin.blogs.blog_categories.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:blog_categories,id',
        ]);

        $this->categoryService->bulkDelete($validated['ids']);

        return redirect()
            ->route('admin.blogs.blog_categories.index')
            ->with('success', 'Selected categories deleted successfully.');
    }

    public function toggleStatus(Request $request)
    {
        $validated = $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'exists:blog_categories,id',
            'action' => 'required|in:0,1',
        ]);

        foreach ($validated['ids'] as $id) {
            $category = BlogCategory::findOrFail($id);
            $category->status = $validated['action'];
            $category->save();
        }

        return redirect()
            ->route('admin.blogs.blog_categories.index')
            ->with('success', 'Selected categories status updated successfully.');
    }

    private function generateUniqueSlug($text, $excludeId = null)
    {
        $slug = Str::slug($text);
        $originalSlug = $slug;
        $count = 1;

        while (
            BlogCategory::where('slug', $slug)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }
}
