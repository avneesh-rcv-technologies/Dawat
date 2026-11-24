<?php

namespace Modules\Seo\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Seo\Rules\SchemaDocument;
use Modules\Seo\Rules\SeoUrl;
use Modules\Seo\Services\SeoPageService;

class PageController extends Controller
{
    public function __construct(
        protected SeoPageService $pageService,
    ) {}

    public function index(): View
    {
        $pages = $this->pageService->listPages();

        return view('seo::admin.pages.index', compact('pages'));
    }

    public function edit(string $pageKey): View
    {
        $page = $this->pageService->pageForEdit($pageKey);

        return view('seo::admin.pages.form', compact('page'));
    }

    public function update(Request $request, string $pageKey): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'robots' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'string', 'max:2048', new SeoUrl(true)],
            'twitter_image' => ['nullable', 'string', 'max:2048', new SeoUrl(true)],
            'schema' => ['nullable', 'string', 'max:100000', new SchemaDocument],
            'canonical' => ['nullable', 'string', 'max:2048', new SeoUrl(false)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->pageService->save($pageKey, $validated);

        return redirect()
            ->route('admin.seo.pages.index')
            ->with('success', 'Page SEO updated successfully.');
    }
}
