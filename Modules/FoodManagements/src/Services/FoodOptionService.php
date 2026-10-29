<?php

namespace Modules\FoodManagements\Services;

use Modules\FoodManagements\Models\FoodOption;

class FoodOptionService
{
    public function store(array $data)
    {
        try {
            $option = FoodOption::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Option created successfully!',
                'redirect_url' => request()->headers->get('referer'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'errors' => 'Something went wrong.'
            ], 500);
        }
    }

    public function update(int $id, array $data)
    {
        try {
            $option = FoodOption::findOrFail($id);
            $option->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Option updated successfully!',
                'redirect_url' => request()->headers->get('referer'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'errors' => 'Something went wrong.'
            ], 500);
        }
    }

    public function delete(int $id)
    {
        try {
            $option = FoodOption::findOrFail($id);
            $option->delete();

            return response()->json([
                'success' => true,
                'message' => 'Option deleted successfully!',
                'redirect_url' => request()->headers->get('referer'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'errors' => 'Something went wrong.'
            ], 500);
        }
    }
}