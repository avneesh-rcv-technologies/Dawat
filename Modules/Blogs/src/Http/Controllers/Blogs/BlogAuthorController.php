<?php

namespace Modules\Blogs\Http\Controllers\Blogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Blogs\DataView\Blogs\BlogAuthor as BlogAuthorGrid;
use Modules\Blogs\Models\BlogAuthor;
use Modules\Blogs\Services\BlogAuthorService;

class BlogAuthorController extends Controller
{
    public function __construct(protected BlogAuthorService $authorService)
    {
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(BlogAuthorGrid::class)->process();

        return view('blogs::authors.index', compact('lists'));
    }

    public function create()
    {
        return view('blogs::authors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->rules(),
            fn_image_validation_messages('avatar')
        );

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? $validated['name']
        );
        $validated['status'] = (bool) ($validated['status'] ?? true);
        $validated['bio'] = isset($validated['bio'])
            ? Str::limit(trim(strip_tags($validated['bio'])), 1000, '')
            : null;
        unset($validated['avatar']);

        $author = $this->authorService->create($validated);

        $this->storeAuthorAvatar($request, $author);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Author created successfully.',
                'redirect_url' => route('admin.blogs.blog_authors.index'),
            ]);
        }

        return redirect()
            ->route('admin.blogs.blog_authors.index')
            ->with('success', 'Author created successfully.');
    }

    public function edit($id)
    {
        $author = BlogAuthor::findOrFail($id);

        return view('blogs::authors.edit', compact('author'));
    }

    public function update(Request $request, $id)
    {
        $author = BlogAuthor::findOrFail($id);
        $validated = $request->validate(
            $this->rules($author->id),
            fn_image_validation_messages('avatar')
        );

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? $validated['name'],
            $author->id
        );
        $validated['status'] = (bool) ($validated['status'] ?? false);
        $validated['bio'] = isset($validated['bio'])
            ? Str::limit(trim(strip_tags($validated['bio'])), 1000, '')
            : null;
        unset($validated['avatar']);

        $this->authorService->update($id, $validated);

        $this->storeAuthorAvatar($request, $author);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Author updated successfully.',
                'redirect_url' => route('admin.blogs.blog_authors.index'),
            ]);
        }

        return redirect()
            ->route('admin.blogs.blog_authors.index')
            ->with('success', 'Author updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $this->authorService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Author deleted successfully.',
                'redirect_url' => route('admin.blogs.blog_authors.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:blog_authors,id',
        ]);

        $this->authorService->bulkDelete($validated['ids']);

        return redirect()
            ->route('admin.blogs.blog_authors.index')
            ->with('success', 'Selected authors deleted successfully.');
    }

    public function toggleStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:blog_authors,id',
            'action' => 'required|in:0,1',
        ]);

        $this->authorService->toggleStatus($validated['ids'], (int) $validated['action']);

        return redirect()
            ->route('admin.blogs.blog_authors.index')
            ->with('success', 'Selected authors status updated successfully.');
    }

    protected function rules(?int $excludeId = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'avatar' => fn_image_validation_rules(),
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('blog_authors', 'email')->ignore($excludeId),
            ],
            'twitter_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'status' => 'required|in:0,1',
        ];
    }

    protected function generateUniqueSlug(string $text, ?int $excludeId = null): string
    {
        $slug = Str::slug($text);
        $originalSlug = $slug;
        $count = 1;

        while (
            BlogAuthor::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    /**
     * Persist avatar via Filemanager and clear the legacy avatar column path.
     */
    protected function storeAuthorAvatar(Request $request, BlogAuthor $author): void
    {
        $uploaded = fn_upload_request_file(
            $request,
            'avatar',
            'blog_author_avatar',
            $author->id,
            true
        );

        // Prefer Filemanager URL; stop serving the old storage column path.
        if ($uploaded && $author->avatar) {
            $author->forceFill(['avatar' => null])->save();
        }
    }
}
