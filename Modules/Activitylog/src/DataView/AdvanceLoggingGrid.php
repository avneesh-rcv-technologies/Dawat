<?php

namespace Modules\Activitylog\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class AdvanceLoggingGrid extends DataGrid
{
    protected $primaryColumn = 'ip_address';

    protected $itemsPerPage = 25;

    protected $sortOrder = 'desc';

    protected bool $exportable = false;

    public static function defaultDateFrom(): string
    {
        return now()->subDays(7)->toDateString();
    }

    /**
     * Prepare query builder.
     *
     * Grouped by ip_address + causer_type + causer_id, showing the latest
     * record per group. Causer name is resolved from admins / users tables.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $dateFrom = request('date_from', self::defaultDateFrom());

        // Subquery: latest id per group, scoped to recent rows to avoid full-table GROUP BY.
        $latestIds = DB::table('request_activity_logs')
            ->where('created_at', '>=', $dateFrom . ' 00:00:00')
            ->selectRaw('MAX(id) as max_id, COUNT(*) as request_count')
            ->groupBy('ip_address', 'causer_type', 'causer_id');

        return DB::table('request_activity_logs as ral')
            ->joinSub($latestIds, 'grp', 'ral.id', '=', 'grp.max_id')
            ->where('ral.created_at', '>=', $dateFrom . ' 00:00:00')
            ->leftJoin('admins', function ($join) {
                $join->on('ral.causer_id', '=', 'admins.id')
                    ->where('ral.causer_type', 'LIKE', '%Admin%');
            })
            ->leftJoin('blocked_ips as bi', function ($join) {
                $join->on('ral.ip_address', '=', 'bi.ip_address');
            })
            
            ->select(
                'ral.id',
                'ral.ip_address',
                'grp.request_count',
                'ral.guard',
                'ral.causer_type',
                'ral.causer_id',
                DB::raw('COALESCE(admins.name) as causer_name'),
                'ral.method',
                'ral.path',
                'ral.route_name',
                'ral.user_agent',
                'ral.referer',
                'ral.response_status',
                'ral.duration_ms',
                'ral.is_blocked',
                'ral.blocked_reason',
                'ral.created_at',
                'bi.expires_at as expires_at',
                'bi.is_active as block_is_active'
            )
            ->orderBy('ral.created_at', 'desc');
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addCoreColumns();
        $this->addBlockColumns();
        $this->addRequestColumns();
        $this->addResponseColumns();

        // Map grid column indexes to the qualified / computed column names
        $this->addFilter('ip_address',       'ral.ip_address');
        $this->addFilter('request_count',    'grp.request_count');
        $this->addFilter('causer_type',      'ral.causer_type');
        $this->addFilter('causer_id',        'ral.causer_id');
        $this->addFilter('causer_name',      DB::raw('COALESCE(admins.name)'));
        $this->addFilter('guard',            'ral.guard');
        $this->addFilter('method',           'ral.method');
        $this->addFilter('path',             'ral.path');
        $this->addFilter('route_name',       'ral.route_name');
        $this->addFilter('user_agent',       'ral.user_agent');
        $this->addFilter('referer',          'ral.referer');
        $this->addFilter('response_status',  'ral.response_status');
        $this->addFilter('duration_ms',      'ral.duration_ms');
        $this->addFilter('is_blocked',       'ral.is_blocked');
        $this->addFilter('blocked_reason',   'ral.blocked_reason');
        $this->addFilter('created_at',       'ral.created_at');
        $this->addFilter('expires_at',       'bi.expires_at');
        $this->addFilter('block_is_active',  'bi.is_active');
    }

    private function addCoreColumns(): void
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => 'ID',
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
            'visibility' => false,
        ]);

        $this->addColumn([
            'index'      => 'ip_address',
            'label'      => 'IP Address',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'request_count',
            'label'      => 'Request Count',
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->request_count > 1
                    ? '<span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-800">' . $row->request_count . '</span>'
                    : '';
            },
        ]);

        $this->addColumn([
            'index'      => 'causer_name',
            'label'      => 'User',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->causer_name) {
                    return '<div>
                        <div class="font-medium">' . $row->causer_name . '</div>
                        <div class="text-xs text-gray-500">' . $row->causer_type . ' (ID: ' . $row->causer_id . ')</div>
                        
                    </div>';
                }
                return 'System';
            },
        ]);

        $this->addColumn([
            'index'                 => 'causer_type',
            'label'                 => 'User Type',
            'type'                  => 'string',
            'searchable'            => false,
            'visibility'            => false,
            'filterable'            => true,
            'filterable_type'       => 'dropdown',
            'allow_multiple_values' => true,
            'filterable_options'    => [
                ['label' => 'Admin',    'value' => 'Modules\Acl\Models\Admin'],
                ['label' => 'Customer', 'value' => 'Modules\Acl\Models\Client'],
                ['label' => 'Member', 'value' => 'Modules\Acl\Models\Member'],
                ['label' => 'Guest',    'value' => ''],
            ],
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'      => 'causer_id',
            'label'      => 'User ID',
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => true,
            'visibility' => false,
        ]);

        $this->addColumn([
            'index'                 => 'guard',
            'label'                 => 'Guard',
            'type'                  => 'string',
            'searchable'            => false,
            'filterable'            => true,
            'visibility'            => false,
            'filterable_type'       => 'dropdown',
            'allow_multiple_values' => true,
            'filterable_options'    => [
                ['label' => 'Admin',    'value' => 'admin'],
                ['label' => 'Customer', 'value' => 'customer'],
                ['label' => 'Web',      'value' => 'web'],
            ],
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => 'Last Seen',
            'type'            => 'datetime',
            'searchable'      => false,
            'filterable'      => true,
            'filterable_type' => 'datetime_range',
            'sortable'        => true,
        ]);
    }

    private function addRequestColumns(): void
    {
        $this->addColumn([
            'index'                => 'method',
            'label'                => 'Request',
            'type'                 => 'string',
            'searchable'           => false,
            'filterable'           => true,
            'filterable_type'      => 'dropdown',
            'visibility'            => false,
            'allow_multiple_values' => true,
            'filterable_options'   => [
                ['label' => 'GET',    'value' => 'GET'],
                ['label' => 'POST',   'value' => 'POST'],
                ['label' => 'PUT',    'value' => 'PUT'],
                ['label' => 'PATCH',  'value' => 'PATCH'],
                ['label' => 'DELETE', 'value' => 'DELETE'],
            ],
            'sortable'             => true,
            'closure'              => function ($row) {
                $method = $row->method ?: 'N/A';
                $path = $row->path ?: '-';
                $route = $row->route_name ?: '-';
                $agent = $row->user_agent ?: '-';
                $referer = $row->referer ?: '-';

                $methodClass = $this->methodBadgeClass($method);
                $deviceName = $this->detectDeviceName($agent);

                return '<div class="space-y-1">'
                    . '<div class="flex items-center gap-2">'
                    . '<span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ' . $methodClass . '">' . e($method) . '</span>'
                    . '<span class="text-sm font-medium text-gray-700">' . e($path) . '</span>'
                    . '</div>'
                    . '<div class="text-xs text-blue-700">Route: <span class="font-medium">' . e($route) . '</span></div>'
                    . '<div class="text-xs text-emerald-700">Device: <span class="font-medium">' . e($deviceName) . '</span></div>'
                    . '<div class="text-xs text-gray-500 truncate max-w-[420px]">Agent: ' . e($agent) . '</div>'
                    . '<div class="text-xs text-gray-400 truncate max-w-[420px]">Referer: ' . e($referer) . '</div>'
                    . '</div>';
            },
        ]);

        $this->addColumn([
            'index'      => 'path',
            'label'      => 'Path',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => false,
            'visibility' => false,
        ]);

        $this->addColumn([
            'index'      => 'route_name',
            'label'      => 'Route',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'visibility' => false,
        ]);

        $this->addColumn([
            'index'      => 'user_agent',
            'label'      => 'User Agent',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => false,
            'visibility' => false,
        ]);

        $this->addColumn([
            'index'      => 'referer',
            'label'      => 'Referer',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => false,
            'visibility' => false,
        ]);
    }

    private function addResponseColumns(): void
    {
        $this->addColumn([
            'index'                => 'response_status',
            'label'                => 'Status',
            'type'                 => 'integer',
            'searchable'           => false,
            'filterable'           => true,
            'filterable_type'      => 'dropdown',
            'allow_multiple_values' => true,
            'filterable_options'   => [
                ['label' => '200 OK',                   'value' => 200],
                ['label' => '201 Created',              'value' => 201],
                ['label' => '301 Moved Permanently',    'value' => 301],
                ['label' => '302 Found (Redirect)',     'value' => 302],
                ['label' => '400 Bad Request',          'value' => 400],
                ['label' => '401 Unauthorized',         'value' => 401],
                ['label' => '403 Forbidden',            'value' => 403],
                ['label' => '404 Not Found',            'value' => 404],
                ['label' => '419 CSRF Token Mismatch',  'value' => 419],
                ['label' => '422 Unprocessable Entity', 'value' => 422],
                ['label' => '429 Too Many Requests',    'value' => 429],
                ['label' => '500 Server Error',         'value' => 500],
            ],
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'      => 'duration_ms',
            'label'      => 'Duration (ms)',
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
            'visibility' => false,
        ]);
    }

    private function addBlockColumns(): void
    {
        $this->addColumn([
            'index'                => 'block_is_active',
            'label'                => 'Blocked',
            'type'                 => 'boolean',
            'searchable'           => false,
            'filterable'           => false,
            'filterable_type'      => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options'   => [
                ['label' => 'Block', 'value' => 1],
                ['label' => 'Unblock',  'value' => 0],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->block_is_active) {
                    return '<div class="inline-flex items-center rounded-md bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">Blocked</div>';
                }
                return '<div class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-800">Allowed</div>';
            },
        ]);

        $this->addColumn([
            'index'      => 'blocked_reason',
            'label'      => 'Block Reason',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => false,
            'visibility' => false,
        ]);
    }

    private function methodBadgeClass(string $method): string
    {
        return match (strtoupper($method)) {
            'GET' => 'bg-blue-100 text-blue-800',
            'POST' => 'bg-emerald-100 text-emerald-800',
            'PUT', 'PATCH' => 'bg-amber-100 text-amber-800',
            'DELETE' => 'bg-rose-100 text-rose-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    private function detectDeviceName(string $userAgent): string
    {
        if ($userAgent === '' || $userAgent === '-') {
            return 'Unknown Device';
        }

        $ua = strtolower($userAgent);

        if (str_contains($ua, 'android')) {
            return str_contains($ua, 'mobile') ? 'Android Phone' : 'Android Tablet';
        }

        return $this->resolveDeviceLabel($ua) ?? 'Desktop Browser';
    }

    private function resolveDeviceLabel(string $ua): ?string
    {
        $deviceMap = [
            'Bot / Crawler' => ['bot', 'crawler', 'spider'],
            'iPhone' => ['iphone'],
            'iPad' => ['ipad'],
            'Windows PC' => ['windows'],
            'Mac' => ['mac os', 'macintosh'],
            'Linux' => ['linux'],
            'Mobile Device' => ['mobile'],
        ];

        $device = null;

        foreach ($deviceMap as $label => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($ua, $pattern)) {
                    $device = $label;
                    break 2;
                }
            }
        }

        return $device;
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('activity-log.advance-logging.show')) {
            $this->addAction([
                'icon'   => 'visibility',
                'title'  => 'View Detail',
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('activity-log.advance-logging.show', $row->id);
                },
            ]);
        }

        $this->addAction([
            'icon' => 'fitbit',
            'title' => 'Candlestick',
            'method' => 'GET',
            'action' => 'text-red-600 bg-red-100',
            'url' => function ($row) {
                return route('activity-log.advance-logging.candlestick-chart', ['ip' => $row->ip_address]);
            },
        ]);
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('activity-log.access-requests')) {
            $this->addMassAction([
                'icon'   => 'fitbit',
                'title'  => 'Access Requests',
                'method' => 'GET',
                'action' => 'text-red-600 bg-red-100',
                'url'    => 'activity-log.access-requests',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('activity-log.is_blocked')) {
            $this->addMassAction([
                'icon' => '',
                'title' => 'Status',
                'method' => 'POST',
                'url' => 'activity-log.is_blocked',
                'action' => 'bg-gray-500',
                'options' => [
                    [
                        'label' => 'Block',
                        'value' => 1,
                    ],
                    [
                        'label' => 'Unblock',
                        'value' => 0,
                    ],
                ],
            ]);
        }
    }
}
