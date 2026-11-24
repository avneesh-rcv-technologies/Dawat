<?php

namespace Modules\Seo\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Seo\Services\SitemapBuilder;

class SitemapController extends Controller
{
    public function __construct(
        protected SitemapBuilder $builder,
    ) {}

    public function index(): View
    {
        $stats = $this->builder->stats();

        return view('seo::admin.sitemap.index', [
            'stats' => $stats,
            'publicUrl' => url(config('seo.sitemap.path', '/sitemap.xml')),
        ]);
    }

    public function regenerate(): RedirectResponse
    {
        $xml = $this->builder->generateFresh();

        fn_set_seo_setting('sitemap.last_generated_at', now()->toIso8601String());
        fn_set_seo_setting('sitemap.last_url_count', $this->builder->countUrls($xml));

        return redirect()
            ->route('admin.seo.sitemap.index')
            ->with('success', 'Sitemap cache cleared and regenerated.');
    }
}
