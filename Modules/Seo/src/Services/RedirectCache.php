<?php

namespace Modules\Seo\Services;

use Modules\Cache\Services\AppCache;
use Modules\Seo\Repositories\RedirectRepository;

class RedirectCache
{
    public function __construct(
        protected RedirectRepository $repository,
    ) {}

    public function all(): array
    {
        if (! config('seo.redirects.cache.enabled', true)) {
            return $this->repository->activeRedirects();
        }

        $ttl = (int) config('seo.redirects.cache.ttl', 3600);

        return AppCache::remember(
            'seo',
            'redirects.active',
            fn () => $this->repository->activeRedirects(),
            $ttl
        );
    }

    public function flush(): void
    {
        AppCache::forget('seo', 'redirects.active');
    }
}
