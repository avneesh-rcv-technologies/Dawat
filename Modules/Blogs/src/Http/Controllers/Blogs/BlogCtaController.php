<?php

namespace Modules\Blogs\Http\Controllers\Blogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Blogs\DataView\Blogs\BlogCta as BlogCtaGrid;
use Modules\Blogs\Models\BlogCta;
use Modules\Blogs\Services\BlogCtaService;

class BlogCtaController extends Controller
{
    public function __construct(protected BlogCtaService $ctaService)
    {
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(BlogCtaGrid::class)->process();

        return view('blogs::ctas.index', compact('lists'));
    }

    public function create()
    {
        return view('blogs::ctas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $validated['slug'] = $this->generateUniqueSlug(
            filled($validated['slug'] ?? null) ? $validated['slug'] : $validated['name']
        );
        $validated['status'] = (bool) ($validated['status'] ?? true);

        $this->ctaService->create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'CTA created successfully.',
                'redirect_url' => route('admin.blogs.blog_ctas.index'),
            ]);
        }

        return redirect()
            ->route('admin.blogs.blog_ctas.index')
            ->with('success', 'CTA created successfully.');
    }

    public function edit($id)
    {
        $cta = BlogCta::findOrFail($id);

        return view('blogs::ctas.edit', compact('cta'));
    }

    public function update(Request $request, $id)
    {
        $cta = BlogCta::findOrFail($id);
        $validated = $request->validate($this->rules($cta->id));

        $validated['slug'] = $this->generateUniqueSlug(
            filled($validated['slug'] ?? null) ? $validated['slug'] : $validated['name'],
            $cta->id
        );
        $validated['status'] = (bool) ($validated['status'] ?? false);

        $this->ctaService->update($id, $validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'CTA updated successfully.',
                'redirect_url' => route('admin.blogs.blog_ctas.index'),
            ]);
        }

        return redirect()
            ->route('admin.blogs.blog_ctas.index')
            ->with('success', 'CTA updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $this->ctaService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'CTA deleted successfully.',
                'redirect_url' => route('admin.blogs.blog_ctas.index'),
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
            'ids.*' => 'exists:blog_ctas,id',
        ]);

        $this->ctaService->bulkDelete($validated['ids']);

        return redirect()
            ->route('admin.blogs.blog_ctas.index')
            ->with('success', 'Selected CTAs deleted successfully.');
    }

    public function toggleStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:blog_ctas,id',
            'action' => 'required|in:0,1',
        ]);

        $this->ctaService->toggleStatus($validated['ids'], (int) $validated['action']);

        return redirect()
            ->route('admin.blogs.blog_ctas.index')
            ->with('success', 'Selected CTAs status updated successfully.');
    }

    protected function rules(?int $excludeId = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:blog_ctas,slug' . ($excludeId ? ',' . $excludeId : ''),
            ],
            'content' => 'nullable|string',
            'status' => 'required|in:0,1',
        ];
    }

    protected function generateUniqueSlug(string $text, ?int $excludeId = null): string
    {
        $slug = Str::slug($text);
        $originalSlug = $slug;
        $count = 1;

        while (
            BlogCta::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }
}
