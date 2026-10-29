<?php

namespace Modules\DataView\ColumnTypes;

use Modules\DataView\Column;
use Modules\DataView\Enums\FilterTypeEnum;
use Modules\DataView\Enums\DateRangeOptionEnum;
use Modules\DataView\Exceptions\InvalidColumnException;
use Modules\DataView\Exceptions\InvalidColumnExpressionException;

class Date extends Column
{
    /**
     * Set filterable type.
     */
    public function setFilterableType(?string $filterableType): void
    {
        if (
            $filterableType
            && ($filterableType !== FilterTypeEnum::DATE_RANGE->value)
        ) {
            throw new InvalidColumnException('Date filters will only work with `date_range` type. Either remove the `filterable_type` or set it to `date_range`.');
        }

        parent::setFilterableType($filterableType);
    }

    /**
     * Set filterable options.
     */
    public function setFilterableOptions(mixed $filterableOptions): void
    {
        if (empty($filterableOptions)) {
            $filterableOptions = DateRangeOptionEnum::options();
        }

        parent::setFilterableOptions($filterableOptions);
    }

    /**
     * Process filter.
     */
    public function processFilter($queryBuilder, $requestedDates)
    {
        return $queryBuilder->where(function ($scopeQueryBuilder) use ($requestedDates) {
            if (is_string($requestedDates)) {
                $rangeOption = collect($this->filterableOptions)->firstWhere('name', $requestedDates);

                $requestedDates = !$rangeOption
                    ? [[$requestedDates, $requestedDates]]
                    : [[$rangeOption['from'], $rangeOption['to']]];

            } elseif (!is_array($requestedDates)) {
                throw new InvalidColumnExpressionException('Only string and array are allowed for date column type.');
            }

            foreach ($requestedDates as $range) {
                if (!is_array($range)) {
                    throw new InvalidColumnExpressionException('Date ranges must contain From and To values.');
                }

                $from = $range[0] ?? null;
                $to = $range[1] ?? null;

                if ($from !== null && $from !== '') {
                    $scopeQueryBuilder->where($this->columnName, '>=',
                        str_contains($from, ' ') ? $from : $from . ' 00:00:00');
                }

                if ($to !== null && $to !== '') {
                    $scopeQueryBuilder->where($this->columnName, '<=',
                        str_contains($to, ' ') ? $to : $to . ' 23:59:59');
                }
            }
        });
    }
}
