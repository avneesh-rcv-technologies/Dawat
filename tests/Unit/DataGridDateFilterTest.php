<?php

namespace Tests\Unit;

use Illuminate\Database\Connection;
use Modules\DataView\ColumnTypes\Date;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DataGridDateFilterTest extends TestCase
{
    public static function ranges(): array
    {
        return [
            'only to, missing from key' => [[[1 => '2026-10-22']], ['2026-10-22 23:59:59'], false, true],
            'only to, empty from' => [[['', '2026-10-22']], ['2026-10-22 23:59:59'], false, true],
            'only from' => [[['2026-10-01']], ['2026-10-01 00:00:00'], true, false],
            'only from, empty to' => [[['2026-10-01', '']], ['2026-10-01 00:00:00'], true, false],
            'both dates' => [[['2026-10-01', '2026-10-22']], ['2026-10-01 00:00:00', '2026-10-22 23:59:59'], true, true],
            'same day includes midnight' => [[['2026-10-22', '2026-10-22']], ['2026-10-22 00:00:00', '2026-10-22 23:59:59'], true, true],
            'empty range' => [[[]], [], false, false],
            'null range values' => [[[null, null]], [], false, false],
            'timestamps preserved' => [[['2026-10-01 12:30:00', '2026-10-22 17:00:00']], ['2026-10-01 12:30:00', '2026-10-22 17:00:00'], true, true],
            'single date string' => ['2026-10-22', ['2026-10-22 00:00:00', '2026-10-22 23:59:59'], true, true],
            'preset' => ['today', ['2026-10-22 00:00:00', '2026-10-22 23:59:59'], true, true],
        ];
    }

    #[DataProvider('ranges')]
    public function test_date_filter_handles_optional_boundaries($range, array $bindings, bool $hasFrom, bool $hasTo): void
    {
        $column = new Date([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'date',
            'filterable_options' => [[
                'name' => 'today',
                'from' => '2026-10-22 00:00:00',
                'to' => '2026-10-22 23:59:59',
            ]],
        ]);
        $query = (new Connection(null))->table('customers');

        $this->assertSame($query, $column->processFilter($query, $range));
        $this->assertSame($bindings, $query->getBindings());
        $this->assertSame($hasFrom, str_contains($query->toSql(), '>= ?'));
        $this->assertSame($hasTo, str_contains($query->toSql(), '<= ?'));
    }
}
