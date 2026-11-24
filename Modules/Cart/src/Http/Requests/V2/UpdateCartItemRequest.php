<?php

namespace Modules\Cart\Http\Requests\V2;

use Illuminate\Validation\Validator;

class UpdateCartItemRequest extends AddCartItemRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['restaurant_id'] = ['prohibited'];
        $rules['item_id'] = ['prohibited'];
        $rules['quantity'] = ['sometimes', ...$rules['quantity']];
        $rules['serving_option_id'] = ['sometimes', ...$rules['serving_option_id']];

        return $rules;
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'quantity.min' => 'Please enter a quantity of at least 1, or remove this item from your cart.',
            'restaurant_id.prohibited' => 'The restaurant cannot be changed here. Please add an item from the desired restaurant instead.',
            'item_id.prohibited' => 'The food item cannot be changed here. Please remove this item and add the food you want.',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isEmpty() && ! array_intersect(
                ['quantity', 'guest_count', 'serving_option_id', 'note', 'event_guest_count'],
                array_keys($this->all())
            )) {
                $validator->errors()->add('body', 'Please provide a quantity, guest count, serving option, note, or event guest count to update.');
            }
        }];
    }
}
