<?php

namespace Modules\FoodManagements\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\FoodManagements\Services\FoodOptionService;

class FoodOptionController extends Controller
{
    protected FoodOptionService $service;

    public function __construct(FoodOptionService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'food_option_group_id' => 'required|exists:food_option_groups,id',
            'name'                 => 'required|string|max:255',
            'is_active'            => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        return $this->service->store($validator->validated());
    }

    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        return $this->service->update($id, $validator->validated());
    }

    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
