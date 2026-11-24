<?php

namespace Modules\Seo\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Seo\Models\SeoRedirect;

class RedirectRepository
{
    public function all()
    {
        return SeoRedirect::query()->orderByDesc('id')->get();
    }

    public function paginate(int $perPage = 20)
    {
        return SeoRedirect::query()->orderByDesc('id')->paginate($perPage);
    }

    public function find(int $id): ?SeoRedirect
    {
        return SeoRedirect::query()->find($id);
    }

    public function create(array $data): SeoRedirect
    {
        return SeoRedirect::query()->create($data);
    }

    public function update(SeoRedirect $redirect, array $data): SeoRedirect
    {
        $redirect->update($data);

        return $redirect->fresh();
    }

    public function delete(SeoRedirect $redirect): void
    {
        $redirect->delete();
    }

    public function activeRedirects(): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('seo_redirects')) {
            return [];
        }

        return SeoRedirect::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn (SeoRedirect $redirect) => [
                'id' => $redirect->id,
                'from_path' => $redirect->from_path,
                'to_url' => $redirect->to_url,
                'status_code' => $redirect->status_code,
                'match_type' => $redirect->match_type,
                'preserve_query' => $redirect->preserve_query,
            ])
            ->all();
    }

    public function recordHit(int $id): void
    {
        SeoRedirect::query()
            ->whereKey($id)
            ->update([
                'hits' => DB::raw('hits + 1'),
                'last_hit_at' => now(),
            ]);
    }
}
