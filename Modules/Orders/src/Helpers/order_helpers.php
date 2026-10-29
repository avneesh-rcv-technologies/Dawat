<?php

use Modules\Orders\Models\Status;

if (!function_exists('fn_get_order_statuses')) {
    /**
     * Get all order statuses by type
     * 
     * @param string $type The type code (default: 'O' for orders)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function fn_get_order_statuses($type = 'O')
    {
        return Status::where('type_code', $type)->get();
    }
}

if (!function_exists('fn_get_order_status')) {
    /**
     * Get a specific order status by status code and type
     * 
     * @param string $status_code The status code
     * @param string $type The type code (default: 'O' for orders)
     * @return \Modules\Orders\Models\Status|null
     */
    function fn_get_order_status($status_code, $type = 'O')
    {
        return Status::where('type_code', $type)
            ->where('status_code', $status_code)
            ->first();
    }
}

if (!function_exists('fn_get_order_status_code')) {
    /**
     * Get order status code by ID and type
     * 
     * @param int $id The status ID
     * @param string $type The type code (default: 'O' for orders)
     * @return string|null
     */
    function fn_get_order_status_code($id, $type = 'O')
    {
        $status = Status::where('type_code', $type)
            ->where('id', $id)
            ->first(['status_code']);

        return $status ? $status->status_code : null;
    }
}

if (!function_exists('fn_get_order_status_name')) {
    /**
     * Get order status name by status code and type
     * 
     * @param string $status_code The status code
     * @param string $type The type code (default: 'O' for orders)
     * @return string|null
     */
    function fn_get_order_status_name($status_code, $type = 'O')
    {
        $status = fn_get_order_status($status_code, $type);
        return $status ? $status->name : null;
    }
}

if (!function_exists('fn_get_order_status_color')) {
    /**
     * Get order status color by status code and type
     * 
     * @param string $status_code The status code
     * @param string $type The type code (default: 'O' for orders)
     * @return string|null
     */
    function fn_get_order_status_color($status_code, $type = 'O')
    {
        $status = fn_get_order_status($status_code, $type);
        return $status ? $status->color : null;
    }
}

if (!function_exists('fn_get_order_status_by_name')) {
    /**
     * Get order status code by name (for backward compatibility)
     * 
     * @param string $name The status name (e.g., 'COMPLETED', 'PROCESSING')
     * @param string $type The type code (default: 'O' for orders)
     * @return string|null
     */
    function fn_get_order_status_by_name($name, $type = 'O')
    {
        // Map status names to status codes from statuses table
        $statusMap = [
            'COMPLETED' => 'Z',      // Completed
            'PROCESSING' => 'H',     // Processing
            'REFUNDED' => 'R',       // Refunded
            'FAILED' => 'F',         // Failed
            'PENDING' => 'P',        // Pending
            'NEW' => 'O',            // New
            'CANCELLED' => 'C',      // Cancelled
            'INCOMPLETE' => 'N',     // Incomplete
        ];

        return $statusMap[strtoupper($name)] ?? 'P'; // Default to Pending
    }

    if (!function_exists('fn_get_order_status_by_status_code')) {
        /**
         * Get order status name by status code and type
         * 
         * @param string $status_code The status code
         * @param string $type The type code (default: 'O' for orders)
         * @return string|null
         */
        function fn_get_order_status_by_status_code($status_code, $type = 'O')
        {
            $status = Status::where('type_code', $type)
                ->where('status_code', $status_code)
                ->first(['name']);

            return $status ? $status->name : null;
        }
    }
}
