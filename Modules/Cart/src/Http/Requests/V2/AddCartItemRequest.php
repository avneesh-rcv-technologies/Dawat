<?php

namespace Modules\Cart\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'restaurant_id' => ['required', 'integer', 'min:1'],
            'item_id' => ['required', 'integer', 'min:1'],
            'serving_option_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1', 'max:'.config('cart.max_quantity_per_item', 100)],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
            'event_guest_count' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
            'note' => ['nullable', 'string', 'max:500'],
            'selected_options' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'restaurant_id.required' => 'Please select a restaurant.',
            'restaurant_id.integer' => 'Please select a valid restaurant.',
            'restaurant_id.min' => 'Please select a valid restaurant.',
            'item_id.required' => 'Please select a food item.',
            'item_id.integer' => 'Please select a valid food item.',
            'item_id.min' => 'Please select a valid food item.',
            'serving_option_id.required' => 'Please choose a serving option for this item.',
            'serving_option_id.integer' => 'Please choose a valid serving option.',
            'serving_option_id.min' => 'Please choose a valid serving option.',
            'quantity.required' => 'Please enter how many you would like to order.',
            'quantity.integer' => 'Please enter a whole number for the quantity.',
            'quantity.min' => 'Please order at least 1.',
            'quantity.max' => 'You can order up to :max of the same selection. Please reduce the quantity.',
            'guest_count.integer' => 'Please enter a whole number for the guest count.',
            'guest_count.min' => 'Please enter at least 1 guest.',
            'guest_count.max' => 'The guest count is too large. Please enter a smaller number.',
            'event_guest_count.integer' => 'Please enter a whole number for the event guest count.',
            'event_guest_count.min' => 'Please enter at least 1 guest for the event.',
            'event_guest_count.max' => 'The event guest count is too large. Please enter a smaller number.',
            'note.string' => 'Please enter your special instructions as text.',
            'note.max' => 'Please keep your special instructions within :max characters.',
            'selected_options.prohibited' => 'Add-ons are not supported here yet. Please choose a serving option instead.',
        ];
    }
}
