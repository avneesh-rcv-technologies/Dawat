<?php

namespace Modules\Seo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Seo\Repositories\RedirectRepository;
use Modules\Seo\Services\RedirectCache;
use Modules\Seo\Services\RedirectMatcher;
use Modules\Seo\Support\SeoSettings;
use Symfony\Component\HttpFoundation\Response;

class HandleSeoRedirects
{
    public function __construct(
        protected RedirectMatcher $matcher,
        protected RedirectCache $cache,
        protected RedirectRepository $repository,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! SeoSettings::enabled() || ! SeoSettings::get('redirects.enabled', config('seo.redirects.enabled', true))) {
            return $next($request);
        }

        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $path = $request->getPathInfo();
        $redirects = array_merge(
            $this->staticRedirects(),
            $this->dbRedirects()
        );

        $match = $this->matcher->match($path, $redirects);

        if (! $match) {
            return $next($request);
        }

        $destination = $match['to_url'];

        if (! str_starts_with($destination, 'http')) {
            $destination = url($destination);
        }

        $preserveQuery = $match['preserve_query'] ?? config('seo.redirects.preserve_query', true);
        if ($preserveQuery && $request->getQueryString()) {
            $parts = explode('#', $destination, 2);
            $destination = $parts[0].(str_contains($parts[0], '?') ? '&' : '?').$request->getQueryString()
                .(isset($parts[1]) ? '#'.$parts[1] : '');
        }

        if (! empty($match['id'])) {
            $this->repository->recordHit((int) $match['id']);
        }

        return redirect()->to($destination, (int) $match['status_code']);
    }

    protected function staticRedirects(): array
    {
        return collect(config('seo.redirects.static', []))
            ->map(fn (array $item) => [
                'from' => $item['from'],
                'to' => $item['to'],
                'status' => $item['status'] ?? 301,
                'match' => $item['match'] ?? 'exact',
                'preserve_query' => $item['preserve_query'] ?? null,
            ])
            ->all();
    }

    protected function dbRedirects(): array
    {
        try {
            return $this->cache->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
