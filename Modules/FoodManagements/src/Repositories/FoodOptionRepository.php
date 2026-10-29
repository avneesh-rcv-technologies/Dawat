<?php

namespace Modules\FoodManagements\Repositories;

use Modules\FoodManagements\Models\FoodOption;

class FoodOptionRepository
{
    public function create(array $data): FoodOption
    {
        return FoodOption::create($data);
    }

    public function update(int $id, array $data): FoodOption
    {
        $option = FoodOption::findOrFail($id);
        $option->update($data);

        return $option;
    }

    public function delete(int $id): void
    {
        $option = FoodOption::findOrFail($id);
        $option->delete();
    }
}
