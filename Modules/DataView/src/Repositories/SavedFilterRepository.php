<?php

namespace Modules\DataView\Repositories;

use Webkul\Core\Eloquent\Repository;
use Modules\DataView\Contracts\SavedFilter;

class SavedFilterRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return SavedFilter::class;
    }
}
