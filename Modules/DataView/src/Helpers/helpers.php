<?php
use Modules\DataView\DataGrid;
use Modules\DataView\Exceptions\InvalidDataGridException;

if (! function_exists('fn_datagrid')) {
    /**
     * Datagrid helper.
     */
    function fn_datagrid(string $datagridClass): DataGrid
    {
        
        if (! is_subclass_of($datagridClass, DataGrid::class)) {
            throw new InvalidDataGridException("'{$datagridClass}' must extend the '".DataGrid::class."' class.");
        }

        return app($datagridClass);
    }
}

if (! function_exists('fn_print_r')) {
    /**
     * Datagrid helper.
     */
    function fn_print_r(...$data)
    {

        echo '<pre>';
        foreach ($data as $key => $value) {
            print_r($value);
        }
        echo '</pre>';
    }
}

if (! function_exists('fn_print_die')) {
    /**
     * Datagrid helper.
     */
    function fn_print_die(...$data)
    {

        echo '<pre>';
        foreach ($data as $key => $value) {
            print_r($value);
        }
        echo '</pre>';
        die;
    }
}
