<?php

namespace Modules\FoodManagements\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\FoodManagements\Services\ServingOptionConfiguration;

class ServingOptionsApiController
{
    public function index(ServingOptionConfiguration $configuration): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Serving options retrieved successfully.',
            'data' => $configuration->all(),
        ]);
    }
}
