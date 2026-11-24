<?php

namespace Modules\Seo\Contracts;

use Illuminate\Database\Eloquent\Model;

interface SeoSourceInterface
{
    public function key(): string;

    public function sitemapEntries(): iterable;

    public function resolveUrl(Model $model): string;

    public function resolveMeta(Model $model): array;
}
