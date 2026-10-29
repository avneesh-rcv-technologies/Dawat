<?php

namespace Modules\Core\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class AdvancedSelectorController extends Controller
{
    public function getData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table' => 'required|string',
            'search_column' => 'required|string',
            'value_column' => 'required|string',
            'display_column' => 'required|string',
            'search' => 'nullable|string',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'order_by' => 'nullable|string',
            'order_direction' => 'nullable|in:asc,desc',
            'additional_columns' => 'nullable|json',
            'filters' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $table = $request->input('table');
            $searchColumn = $request->input('search_column');
            $valueColumn = $request->input('value_column');
            $displayColumn = $request->input('display_column');
            $search = $request->input('search', '');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);
            $orderBy = $request->input('order_by', $displayColumn);
            $orderDirection = $request->input('order_direction', 'asc');
            $additionalColumns = json_decode($request->input('additional_columns', '[]'), true) ?: [];
            $filters = json_decode($request->input('filters', '[]'), true) ?: [];


            $columns = array_unique(array_merge(
                [$valueColumn, $displayColumn, $searchColumn],
                $additionalColumns
            ));

            $query = DB::table($table)->select($columns);

            // Apply search
            if (!empty($search)) {
                $query->where($searchColumn, 'LIKE', '%' . $search . '%');
            }

            // Apply additional filters
            foreach ($filters as $filter) {
                if (isset($filter['column'], $filter['operator'], $filter['value'])) {
                    $query->where($filter['column'], $filter['operator'], $filter['value']);
                }
            }

            // Apply soft deletes check if column exists
            if ($this->hasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            // Apply active status check if column exists
            if ($this->hasColumn($table, 'is_active')) {
                $query->where('is_active', 1);
            }

            // Get total count for pagination
            $total = $query->count();

            // Apply ordering and pagination
            $items = $query
                ->orderBy($orderBy, $orderDirection)
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get()
                ->toArray();

            // Calculate pagination info
            $hasMore = ($page * $perPage) < $total;


            return response()->json([
                'success' => true,
                'items' => $items,
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'has_more' => $hasMore
            ]);

        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data'
            ], 500);
        }
    }

    /**
     * Validate table name against allowed tables
     */
    private function isValidTable($table)
    {
        $allowedTables = [
            'property_features',
            'unit_features',
            'caretakers',
            'owners',
            'properties',
            'tenants',
            'food_categories',
            'food_items',
            'discounts',
            'coupons',
            'users',
            'customers',
            'restaurants',
            // Add more tables as needed
        ];

        return in_array($table, $allowedTables);
    }

    /**
     * Check if table has a specific column
     */
    private function hasColumn($table, $column)
    {
        try {
            return DB::getSchemaBuilder()->hasColumn($table, $column);
        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            return false;
        }
    }

    /**
     * Get selected items details for display
     */
    public function getSelectedItems(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table' => 'required|string',
            'value_column' => 'required|string',
            'display_column' => 'required|string',
            'values' => 'required|array',
            'additional_columns' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $table = $request->input('table');
            $valueColumn = $request->input('value_column');
            $displayColumn = $request->input('display_column');
            $values = $request->input('values');
            $additionalColumns = json_decode($request->input('additional_columns', '[]'), true) ?: [];

            // if (!$this->isValidTable($table)) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Invalid table name'
            //     ], 400);
            // }

            $columns = array_unique(array_merge(
                [$valueColumn, $displayColumn],
                $additionalColumns
            ));

            $items = DB::table($table)
                ->select($columns)
                ->whereIn($valueColumn, $values)
                ->get()
                ->toArray();

            return response()->json([
                'success' => true,
                'items' => $items
            ]);

        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch selected items'
            ], 500);
        }
    }
}