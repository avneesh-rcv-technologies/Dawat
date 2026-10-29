<?php

namespace Modules\DataView\ColumnTypes;

use Modules\DataView\Column;
use Modules\DataView\Enums\FilterTypeEnum;
use Modules\DataView\Exceptions\InvalidColumnExpressionException;

class Text extends Column
{
    /**
     * Process filter.
     */
    public function processFilter($queryBuilder, $requestedValues)
    {
        if ($this->filterableType === FilterTypeEnum::DROPDOWN->value) {
            return $queryBuilder->where(function ($scopeQueryBuilder) use ($requestedValues) {
                if (is_string($requestedValues)) {
                    $scopeQueryBuilder->orWhere($this->columnName, $requestedValues);
                } elseif (is_array($requestedValues)) {
                    foreach ($requestedValues as $value) {
                        $scopeQueryBuilder->orWhere($this->columnName, $value);
                    }
                } else {
                    throw new InvalidColumnExpressionException('Only string and array are allowed for text column type.');
                }
            });
        }

        return $queryBuilder->where(function ($scopeQueryBuilder) use ($requestedValues) {
            if (is_string($requestedValues)) {
                $scopeQueryBuilder->orWhere($this->columnName, 'LIKE', '%'.$requestedValues.'%');
            } elseif (is_array($requestedValues)) {
                foreach ($requestedValues as $value) {
                    $scopeQueryBuilder->orWhere($this->columnName, 'LIKE', '%'.$value.'%');
                }
            } else {
                throw new InvalidColumnExpressionException('Only string and array are allowed for text column type.');
            }
        });
    }
}
