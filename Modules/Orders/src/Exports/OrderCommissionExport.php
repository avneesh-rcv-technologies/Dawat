<?php

namespace Modules\Orders\Exports;

use Modules\DataView\Exports\DataGridExport;

class OrderCommissionExport extends DataGridExport
{
    public function map(mixed $record): array
    {
        return array_map(function ($value) {
            // Keep user-supplied names/order references as text in spreadsheets.
            if (is_string($value) && preg_match('/^[\s]*[=+@-]/', $value) && ! is_numeric($value)) {
                return "'".$value;
            }

            return $value;
        }, parent::map($record));
    }
}
