<?php

namespace Modules\Cache\DataView;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Cache\Services\AppCache;
use Modules\DataView\DataGrid;

class CacheProfileKeysGrid extends DataGrid
{
    protected $primaryColumn = 'name';

    protected $itemsPerPage = 50;

    protected $sortOrder = 'asc';

    protected $sortColumn = 'name';

    public function __construct(protected string $profile)
    {
    }

    public function prepareQueryBuilder()
    {
        $unions = [];

        foreach (AppCache::entries($this->profile) as $entry) {
            $name = (string) $entry['name'];
            $meta = is_array($entry['meta'] ?? null) ? $entry['meta'] : [];
            $bytes = (int) ($meta['bytes'] ?? 0);
            $forever = ! empty($meta['forever']);
            $ttl = $forever ? null : ($meta['ttl'] ?? null);
            $ttlLabel = $forever ? 'Forever' : (($ttl !== null && $ttl !== '') ? $ttl . 's' : '—');

            $unions[] = DB::query()->selectRaw(
                '? as name, ? as data_key, ? as bytes, ? as bytes_label, ? as ttl_label, ? as written_at',
                [
                    $name,
                    (string) ($entry['data_key'] ?? AppCache::dataKey($this->profile, $name)),
                    $bytes,
                    AppCache::formatBytes($bytes),
                    $ttlLabel,
                    (string) ($meta['written_at'] ?? '—'),
                ]
            );
        }

        if ($unions === []) {
            return DB::query()
                ->selectRaw('"" as name, "" as data_key, 0 as bytes, "" as bytes_label, "" as ttl_label, "" as written_at')
                ->whereRaw('1 = 0');
        }

        $query = array_shift($unions);
        foreach ($unions as $union) {
            $query->unionAll($union);
        }

        return DB::query()->fromSub($query, 'app_cache_profile_keys');
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'name',
            'label' => 'Entry name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'data_key',
            'label' => 'Redis key',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn ($row) => '<code class="text-xs text-slate-700">' . e($row->data_key) . '</code>',
        ]);

        $this->addColumn([
            'index' => 'bytes_label',
            'label' => 'Size',
            'type' => 'string',
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'ttl_label',
            'label' => 'TTL',
            'type' => 'string',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'written_at',
            'label' => 'Written at',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);
    }

    public function prepareActions()
    {
        $guard = Auth::getDefaultDriver();
        $profile = $this->profile;

        if (bouncer($guard)->hasPermission('cache.forget') && AppCache::isClearable($profile)) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete key',
                'method' => 'DELETE',
                'url' => fn ($row) => route('cache.forget', $profile) . '?name=' . rawurlencode((string) $row->name),
            ]);
        }
    }

    public function prepareMassActions()
    {
        // Bulk key delete needs profile-scoped route params; use row delete + profile flush instead.
    }
}
