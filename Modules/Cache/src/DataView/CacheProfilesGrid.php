<?php

namespace Modules\Cache\DataView;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Cache\Services\AppCache;
use Modules\DataView\DataGrid;

class CacheProfilesGrid extends DataGrid
{
    protected $primaryColumn = 'code';

    protected $itemsPerPage = 25;

    protected $sortOrder = 'asc';

    protected $sortColumn = 'code';

    public function prepareQueryBuilder()
    {
        $unions = [];

        foreach (AppCache::profiles() as $code => $config) {
            $entries = AppCache::entries((string) $code);
            $bytes = 0;
            foreach ($entries as $entry) {
                $bytes += (int) ($entry['meta']['bytes'] ?? 0);
            }

            $forever = !empty($config['forever']);
            $ttl = $forever ? null : (int) ($config['ttl'] ?? 0);
            $ttlLabel = $forever ? 'Forever' : ($ttl . 's');
            $clearable = !empty($config['admin_clearable']) ? 1 : 0;

            $unions[] = DB::query()->selectRaw(
                '? as code, ? as label, ? as ttl_seconds, ? as ttl_label, ? as keys_count, ? as bytes, ? as bytes_label, ? as clearable, ? as clearable_label',
                [
                    (string) $code,
                    (string) ($config['label'] ?? $code),
                    $ttl,
                    $ttlLabel,
                    count($entries),
                    $bytes,
                    AppCache::formatBytes($bytes),
                    $clearable,
                    $clearable ? 'Yes' : 'No',
                ]
            );
        }

        if ($unions === []) {
            return DB::query()
                ->selectRaw('"" as code, "" as label, 0 as ttl_seconds, "" as ttl_label, 0 as keys_count, 0 as bytes, "" as bytes_label, 0 as clearable, "" as clearable_label')
                ->whereRaw('1 = 0');
        }

        $query = array_shift($unions);
        foreach ($unions as $union) {
            $query->unionAll($union);
        }

        return DB::query()->fromSub($query, 'app_cache_profiles');
    }

    public function prepareColumns()
    {
        // Keep primary column `code` as plain text — DataView uses it for
        // checkbox values and dropdown IDs (HTML here breaks querySelector).
        $this->addColumn([
            'index' => 'code',
            'label' => 'Profile',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'label',
            'label' => 'Label',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $url = route('cache.show', $row->code);

                return '<a href="' . e($url) . '" class="font-medium text-slate-800 hover:text-violet-700">' . e($row->label) . '</a>';
            },
        ]);

        $this->addColumn([
            'index' => 'ttl_label',
            'label' => 'TTL',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $cls = ($row->ttl_label === 'Forever')
                    ? 'bg-indigo-100 text-indigo-800'
                    : 'bg-slate-100 text-slate-700';

                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $cls . '">' . e($row->ttl_label) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'keys_count',
            'label' => 'Keys',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'bytes_label',
            'label' => 'Size',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'clearable_label',
            'label' => 'Clearable',
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Yes', 'value' => 'Yes'],
                ['label' => 'No', 'value' => 'No'],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $yes = ($row->clearable_label ?? '') === 'Yes';
                $cls = $yes ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800';

                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $cls . '">' . e($row->clearable_label) . '</span>';
            },
        ]);
    }

    public function prepareActions()
    {
        $guard = Auth::getDefaultDriver();

        if (bouncer($guard)->hasPermission('cache.show')) {
            $this->addAction([
                'icon' => 'visibility',
                'title' => 'View keys',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('cache.show', $row->code);
                }

            ]);
        }

        if (bouncer($guard)->hasPermission('cache.flush')) {
            $this->addAction([
                'icon' => 'delete_sweep',
                'title' => 'Flush profile',
                'method' => 'POST',
                'url' => function ($row) {
                    return route('cache.flush', $row->code);
                },

                'visible' => fn($row) => (int) ($row->clearable ?? 0) === 1,
            ]);
        }
    }

    public function prepareMassActions()
    {
        $guard = Auth::getDefaultDriver();

        if (bouncer($guard)->hasPermission('cache.bulk-flush')) {
            $this->addMassAction([
                'icon' => 'delete_sweep',
                'title' => 'Flush selected',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'cache.bulk-flush',
            ]);
        }
    }
}
