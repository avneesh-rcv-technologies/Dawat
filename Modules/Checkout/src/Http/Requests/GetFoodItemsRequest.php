<?php

namespace Modules\Checkout\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetFoodItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'category_id'   => 'nullable|integer|exists:food_categories,id',
            'variant_id'    => 'nullable|integer|exists:food_feature_variants,variant_id',
            'per_page'      => 'nullable|integer|min:1|max:100',
            'page'          => 'nullable|integer|min:1',
        ];
    }
}
