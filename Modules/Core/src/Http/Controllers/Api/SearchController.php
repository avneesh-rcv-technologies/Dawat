<?php

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Core\Events\RegisterGlobalSearch;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        $module = $request->input('module', 'all');

        // First, dispatch a global search event so modules can append results and register preferences
        $event = new RegisterGlobalSearch((string) $query, (string) $module);
        event($event);

        // If the query is too short, return empty results but include available search preferences
        if (strlen((string) $query) < 2) {
            return response()->json([
                'results' => [],
                'preferences' => $event->getSearchPreferences(),
            ]);
        }

        // Collect results from event listenersa
        $results = collect($event->getResults());

        $modulesToSearch = $module === 'all' ? array_keys($event->getSearchPreferences()) : [$module];

        foreach ($modulesToSearch as $mod) {
            // $moduleResults = $this->searchInModule($mod, $query);
            // $results = $results->concat($moduleResults);
        }

        return response()->json([
            'results' => $results->take(10)->values(),
            'preferences' => $event->getSearchPreferences(),
        ]);
    }

    protected function searchInModule($module, $query)
    {
        $searchConfig = $this->getSearchConfig($module);

        if (!$searchConfig) {
            return collect();
        }

        $searchQuery = DB::table($searchConfig['table']);

        if (isset($searchConfig['join'])) {
            $searchQuery->join(
                $searchConfig['join']['table'],
                $searchConfig['join']['first'],
                $searchConfig['join']['operator'],
                $searchConfig['join']['second']
            );
        }

        $searchQuery->where(function ($q) use ($searchConfig, $query) {
            foreach ($searchConfig['search_columns'] as $column) {
                $q->orWhere($column, 'LIKE', "%{$query}%");
            }
        });

        return $searchQuery
            ->select($searchConfig['select'])
            ->limit(5)
            ->get()
            ->map(function ($item) use ($module) {
                $item->module = $module;
                $item->module_name = $this->getModuleName($module);
                return $item;
            });
    }

    protected function getSearchConfig($module)
    {
        $configs = [
            'users' => [
                'table' => 'admins',
                'join' => [
                    'table' => 'roles',
                    'first' => 'roles.id',
                    'operator' => '=',
                    'second' => 'admins.role_id'
                ],
                'select' => [
                    'admins.id',
                    'admins.name',
                    'roles.name',
                    // concat name ( developer )
                    DB::raw('CONCAT(admins.name, " (", roles.name, ")") AS title'),
                    DB::raw("CONCAT('/admin/users?filters[email]=', admins.email) as url")
                ],
                'search_columns' => ['admins.name', 'admins.email']
            ],

            'roles' => [
                'table' => 'roles',
                'select' => [
                    'roles.id',
                    'roles.name',
                    DB::raw("CONCAT('/admin/settings/roles?filters[name]=', roles.name) as url")
                ],
                'search_columns' => ['roles.name']
            ]
        ];

        return $configs[$module] ?? null;
    }

    protected function getModuleName($module)
    {
        $names = [
            'admins' => 'Admins',
        ];

        return $names[$module] ?? ucfirst($module);
    }
}
