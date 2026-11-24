<?php

namespace Modules\Blogs\Http\Controllers\Blogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Filemanager\Services\FileService;
use Modules\Seo\Support\JsonLd;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Core\Enums\Timezone;
use Modules\Blogs\Models\Blog;
use Modules\Blogs\Models\BlogAuthor;
use Modules\Blogs\Models\BlogCta;
use Modules\Blogs\Services\BlogService;
use Modules\Blogs\DataView\Blogs\Blog as BlogGrid;

class BlogController extends Controller
{
    protected BlogService $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(BlogGrid::class)->process();
        return view('blogs::blogs.index', compact('lists'));
    }

    public function create()
    {
        $timezones = Timezone::grouped();
        $ctas = BlogCta::query()->active()->orderBy('name')->get(['id', 'name', 'slug']);

        return view('blogs::blogs.create', compact('timezones', 'ctas'));
    }

    public function uploadContentImage(Request $request, FileService $fileService)
    {
        $validated = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        try {
            $fileLink = $fileService->uploadFile($validated['file'], 'blog_content_image', 0);
            $image = $fileService->getFileById($fileLink->file_id)['url'];
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'file' => $exception->getMessage(),
            ]);
        }

        return response()->json(['location' => $image]);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                $this->validationRules(),
                array_merge(
                    fn_image_validation_messages('inner_image'),
                    fn_image_validation_messages('outer_image')
                )
            );
            $validator->after(function ($validator) use ($request) {
                $this->validateSchemaJson($validator, $request->input('schema'));
                $this->validateScheduleFields($validator, $request);
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => collect($validator->errors()->all())->first() ?: 'Validation failed',
                ], 422);
            }
            $validated = $validator->validated();
            $validated = $this->prepareScheduleData($validated, $request);

            // Images are handled by Filemanager after create
            unset($validated['inner_image'], $validated['outer_image']);

            $validated['sort_order'] = $validated['sort_order'] ?? 1;
            $validated['author_name'] = $this->resolveAuthorName($validated['author_id'] ?? null);

            $categories = $validated['categories'] ?? [];
            unset($validated['categories']);

            $blog = $this->blogService->create($validated);

            if (! empty($categories)) {
                $blog->categories()->sync($categories);
            }

            fn_upload_request_file($request, 'inner_image', 'blog_inner_image', $blog->id, true);
            if (fn_upload_request_file($request, 'outer_image', 'blog_outer_image', $blog->id, true)) {
                $blog->update(['outer_image' => $blog->fresh()->outer_image_url]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Blog created successfully.',
                'redirect_url' => route('admin.blogs.blogs.index')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => collect($e->errors())->flatten()->first() ?: 'Validation failed',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $blog = Blog::with('categories')->findOrFail($id);
        $timezones = Timezone::grouped();
        $ctas = BlogCta::query()->active()->orderBy('name')->get(['id', 'name', 'slug']);
        $previewUrl = URL::temporarySignedRoute(
            'website.blog-preview',
            now()->addDays(7),
            ['slug' => $blog->post_url]
        );

        return view('blogs::blogs.edit', compact('blog', 'timezones', 'previewUrl', 'ctas'));
    }

    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            $this->validationRules($blog->id),
            array_merge(
                fn_image_validation_messages('inner_image'),
                fn_image_validation_messages('outer_image')
            )
        );
        $validator->after(function ($validator) use ($request) {
            $this->validateSchemaJson($validator, $request->input('schema'));
            $this->validateScheduleFields($validator, $request);
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => collect($validator->errors()->all())->first() ?: 'Validation failed',
            ], 422);
        }

        $validated = $validator->validated();
        $validated = $this->prepareScheduleData($validated, $request, $blog);

        unset($validated['inner_image'], $validated['outer_image']);

        $validated['sort_order'] = $validated['sort_order'] ?? $blog->sort_order ?? 1;
        $validated['author_name'] = $this->resolveAuthorName($validated['author_id'] ?? null);

        $categories = $validated['categories'] ?? [];
        unset($validated['categories']);

        try {
            $blog = $this->blogService->update($id, $validated);

            $blog->categories()->sync($categories ?: []);

            fn_upload_request_file($request, 'inner_image', 'blog_inner_image', $blog->id, true);
            if (fn_upload_request_file($request, 'outer_image', 'blog_outer_image', $blog->id, true)) {
                $blog->update(['outer_image' => $blog->fresh()->outer_image_url]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Blog updated successfully.',
                'redirect_url' => route('admin.blogs.blogs.index')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => collect($e->errors())->flatten()->first() ?: 'Validation failed',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Redirect to a signed frontend preview (works for draft/scheduled/published).
     */
    public function preview($id)
    {
        $blog = Blog::findOrFail($id);

        $url = URL::temporarySignedRoute(
            'website.blog-preview',
            now()->addDays(7),
            ['slug' => $blog->post_url]
        );

        return redirect()->away($url);
    }

    public function destroy($id)
    {
        try {
            $this->blogService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Blog deleted successfully.',
                'redirect_url' => route('admin.blogs.blogs.index')
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
            'ids' => 'required|array',
            'ids.*' => 'exists:blogs,id',
        ]);

        try {
            $this->blogService->bulkDelete($validated['ids']);
            return redirect()->route('admin.blogs.blogs.index')
                ->with('success', 'Selected blogs deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:blogs,id',
            'action' => 'required|in:draft,published,archived,scheduled',
        ]);

        try {
            $this->blogService->toggleStatus($validated['ids'], $validated['action']);
            return redirect()->route('admin.blogs.blogs.index')
                ->with('success', 'Selected blogs status changed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    protected function validationRules(?int $blogId = null): array
    {
        return [
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:200',
            'long_description' => 'nullable|string',
            'inner_image' => fn_image_validation_rules(),
            'outer_image' => fn_image_validation_rules(),
            'author_id' => 'nullable|exists:blog_authors,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'schema' => 'nullable|string',
            'post_url' => 'required|string|max:255|unique:blogs,post_url' . ($blogId ? ',' . $blogId : ''),
            'status' => 'required|in:draft,published,archived,scheduled',
            'published_at' => 'nullable|date',
            'scheduled_at' => 'nullable|date',
            'scheduled_timezone' => ['nullable', Rule::in(array_keys(Timezone::all()))],
            'sort_order' => 'nullable|integer|min:1|max:2147483647',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:blog_categories,id',
        ];
    }

    protected function validateSchemaJson($validator, mixed $schema): void
    {
        if (! filled($schema)) {
            return;
        }

        $decoded = is_string($schema) ? json_decode($schema, true) : null;
        if (! JsonLd::valid($decoded)) {
            $validator->errors()->add('schema', 'Schema must be valid JSON-LD with a type, identifier, or graph.');
        }
    }

    protected function validateScheduleFields($validator, Request $request): void
    {
        if ($request->input('status') === 'scheduled' && ! $request->filled('scheduled_at')) {
            $validator->errors()->add('scheduled_at', 'Scheduled date & time is required when status is Scheduled.');
        }
    }

    protected function prepareScheduleData(array $validated, Request $request, ?Blog $existing = null): array
    {
        $timezone = $validated['scheduled_timezone'] ?? config('app.timezone', 'UTC');
        $validated['scheduled_timezone'] = $timezone;

        if (! empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = Carbon::parse($validated['scheduled_at'], $timezone)->utc();

            if (($validated['status'] ?? '') === 'scheduled' && $validated['scheduled_at']->lte(now('UTC'))) {
                $validated['status'] = 'published';
            }
        } else {
            $validated['scheduled_at'] = null;
            if (($validated['status'] ?? '') === 'scheduled') {
                $validated['status'] = 'draft';
            }
        }

        if (! empty($validated['published_at'])) {
            $validated['published_at'] = Carbon::parse($validated['published_at'], $timezone)->startOfDay();
        } elseif (($validated['status'] ?? '') === 'published') {
            $validated['published_at'] = $existing?->published_at
                ?? ($validated['scheduled_at'] ?? now());
        } else {
            $validated['published_at'] = $existing?->published_at;
        }

        if (array_key_exists('author_id', $validated) && blank($validated['author_id'])) {
            $validated['author_id'] = null;
        }

        return $validated;
    }

    protected function resolveAuthorName(?int $authorId): ?string
    {
        if (! $authorId) {
            return null;
        }

        return BlogAuthor::find($authorId)?->name;
    }
}
