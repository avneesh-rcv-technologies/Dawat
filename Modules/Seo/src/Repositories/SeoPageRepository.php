<?php

namespace Modules\Seo\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Seo\Models\SeoPage;
use Modules\Seo\Support\SeoRequestCache;

class SeoPageRepository
{
    public function findByKey(string $pageKey): ?SeoPage
    {
        if (! $this->tableExists()) {
            return null;
        }

        $read = fn () => SeoPage::query()->where('page_key', $pageKey)->first();

        return DB::connection()->transactionLevel() > 0
            ? $read()
            : SeoRequestCache::remember('page:'.$pageKey, $read);
    }

    /**
     * @return Collection<string, SeoPage>
     */
    public function keyed(): Collection
    {
        if (! $this->tableExists()) {
            return collect();
        }

        $read = fn () => SeoPage::query()->get()->keyBy('page_key');

        return DB::connection()->transactionLevel() > 0
            ? $read()
            : SeoRequestCache::remember('pages:all', $read);
    }

    public function upsert(string $pageKey, array $data): SeoPage
    {
        $page = SeoPage::query()->updateOrCreate(
            ['page_key' => $pageKey],
            $data
        );

        SeoRequestCache::forget('page:'.$pageKey);
        SeoRequestCache::forget('pages:all');
        SeoRequestCache::forget('table:seo_pages');

        return $page;
    }

    protected function tableExists(): bool
    {
        return SeoRequestCache::remember('table:seo_pages', fn () => Schema::hasTable('seo_pages'));
    }
}
