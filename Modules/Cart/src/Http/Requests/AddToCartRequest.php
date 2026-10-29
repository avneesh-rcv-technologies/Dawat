<?php

namespace Modules\Cart\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'item_id' => 'required|integer|exists:food_items,id',
            'quantity' => 'required|integer|min:1',
            'selected_options' => 'nullable|array',
            'selected_options.*.option_group_id' => 'required_with:selected_options|integer|exists:food_option_groups,id',
            'selected_options.*.option_id' => [
                'required_with:selected_options',
                'integer',
                'exists:food_options,id',
                function ($attribute, $value, $fail) {
                    // Get the index from the attribute (e.g., selected_options.0.option_id -> 0)
                    preg_match('/selected_options\.(\d+)\.option_id/', $attribute, $matches);
                    if (isset($matches[1])) {
                        $index = $matches[1];
                        $optionGroupId = request()->input("selected_options.{$index}.option_group_id");
                        
                        if ($optionGroupId) {
                            // Check if the option belongs to the specified option group
                            $option = \Modules\FoodManagements\Models\FoodOption::where('id', $value)
                                ->where('food_option_group_id', $optionGroupId)
                                ->first();
                            
                            if (!$option) {
                                $fail('The selected option does not belong to the specified option group.');
                            }
                        }
                    }
                }
            ],
            'special_instructions' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'restaurant_id.required' => 'Restaurant ID is required',
            'restaurant_id.exists' => 'Selected restaurant does not exist',
            'item_id.required' => 'Item is required',
            'item_id.exists' => 'Selected item does not exist',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be at least 1',
            // 'quantity.max' => 'Quantity cannot exceed 100',
            'selected_options.array' => 'Selected options must be an array',
            'selected_options.*.option_group_id.required_with' => 'Option group ID is required',
            'selected_options.*.option_group_id.exists' => 'Selected option group does not exist',
            'selected_options.*.option_id.required_with' => 'Option ID is required',
            'selected_options.*.option_id.exists' => 'Selected option does not exist',
            'special_instructions.max' => 'Special instructions cannot exceed 500 characters',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors' => $validator->errors()->first(),
                'validation_errors' => $validator->errors()
            ], 422)
        );
    }
}