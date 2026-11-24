<?php

namespace Modules\Cache\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cache\DataView\CacheProfileKeysGrid;
use Modules\Cache\DataView\CacheProfilesGrid;
use Modules\Cache\Services\AppCache;

class CacheController extends Controller
{
    public function index()
    {
        $stats = AppCache::stats();
        $redis = AppCache::redisMemory();
        $lists = fn_datagrid(CacheProfilesGrid::class)->process();

        return view('cache::admin.index', compact('lists', 'stats', 'redis'));
    }

    public function show(string $profile)
    {
        $this->ensureProfile($profile);

        $config = AppCache::profiles()[$profile] ?? [];
        $entries = AppCache::entries($profile);
        $bytes = 0;
        foreach ($entries as $entry) {
            $bytes += (int) ($entry['meta']['bytes'] ?? 0);
        }

        $lists = (new CacheProfileKeysGrid($profile))->process();

        return view('cache::admin.show', [
            'profile' => $profile,
            'config' => $config,
            'entriesCount' => count($entries),
            'bytes' => $bytes,
            'bytesLabel' => AppCache::formatBytes($bytes),
            'lists' => $lists,
            'clearable' => AppCache::isClearable($profile),
        ]);
    }

    public function flush(string $profile)
    {
        $this->ensureProfile($profile);
        $this->ensureClearable($profile);

        AppCache::flush($profile);

        return redirect()
            ->back()
            ->with('success', "Cache profile [{$profile}] flushed successfully.");
    }

    public function flushAll()
    {
        AppCache::flushAll(onlyClearable: true);

        return redirect()
            ->route('cache.index')
            ->with('success', 'All clearable cache profiles were flushed.');
    }

    public function bulkFlush(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string'],
        ]);

        $flushed = [];
        $skipped = [];

        foreach ($validated['ids'] as $code) {
            $code = (string) $code;
            if (! AppCache::profileExists($code)) {
                $skipped[] = $code;
                continue;
            }
            if (! AppCache::isClearable($code)) {
                $skipped[] = $code;
                continue;
            }
            AppCache::flush($code);
            $flushed[] = $code;
        }

        $message = count($flushed)
            ? 'Flushed: ' . implode(', ', $flushed) . '.'
            : 'No profiles were flushed.';

        if ($skipped !== []) {
            $message .= ' Skipped: ' . implode(', ', $skipped) . '.';
        }

        return redirect()->route('cache.index')->with('success', $message);
    }

    public function forget(Request $request, string $profile)
    {
        $this->ensureProfile($profile);
        $this->ensureClearable($profile);

        $name = trim((string) $request->query('name', $request->input('name', '')));
        if ($name === '') {
            return redirect()->back()->with('error', 'Cache key name is required.');
        }

        AppCache::forget($profile, $name);

        return redirect()
            ->back()
            ->with('success', "Cache key [{$name}] removed from profile [{$profile}].");
    }

    protected function ensureProfile(string $profile): void
    {
        if (! AppCache::profileExists($profile)) {
            abort(404, "Unknown cache profile [{$profile}].");
        }
    }

    protected function ensureClearable(string $profile): void
    {
        if (! AppCache::isClearable($profile)) {
            abort(403, "Cache profile [{$profile}] is not clearable from admin.");
        }
    }
}
