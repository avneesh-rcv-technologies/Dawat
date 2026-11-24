<?php

namespace Modules\FoodManagements\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\FoodManagements\Services\FoodImageUploadPolicy;
use Modules\FoodManagements\Services\ServingOptionConfiguration;

class SaveFoodItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $ids = $this->input('dietary_label_ids');
        if (is_array($ids)) {
            // Form values and legacy numeric IDs use the same JSON string representation.
            $this->merge(['dietary_label_ids' => array_map(fn ($id) => is_int($id) ? (string) $id : $id, $ids)]);
        }
    }

    public function rules(): array
    {
        return [
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:500'],
            'category_id' => ['required', 'integer', 'exists:food_categories,id'],
            'tax_percentage' => ['required', 'numeric', 'decimal:0,2', 'between:0,100'],
            'dietary_label_ids' => ['required', 'array', 'list', 'min:1', 'max:50'],
            'dietary_label_ids.*' => ['required', 'string', 'max:36', 'distinct:strict', Rule::exists('dietary_labels', 'id')->where('is_active', true)],
            'is_available' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'food_image' => ['nullable', 'array', 'list', 'max:'.FoodImageUploadPolicy::MAX_IMAGES],
            'food_image.*' => FoodImageUploadPolicy::fileRules(),
        ] + app(ServingOptionConfiguration::class)->rules(
            is_array($this->input('serving_options')) ? $this->input('serving_options') : [],
            (int) ($this->route('id') ?? 0)
        );
    }

    public function attributes(): array
    {
        return [
            'tax_percentage' => 'tax percentage',
            'category_id' => 'category',
            'dietary_label_ids' => 'dietary labels',
            'dietary_label_ids.*' => 'dietary label',
            'serving_options.*.serving_name' => 'serving name',
            'serving_options.*.serve_type' => 'serve type',
            'serving_options.*.min_guests' => 'minimum guests',
            'serving_options.*.max_guests' => 'maximum guests',
            'serving_options.*.quantity' => 'serving quantity',
            'serving_options.*.unit' => 'serving unit',
            'serving_options.*.serves' => 'number of people served',
            'serving_options.*.price' => 'serving price',
            'serving_options.*.discount_price' => 'discount price',
            'food_image.*' => 'item image',
        ];
    }

    public function messages(): array
    {
        return FoodImageUploadPolicy::messages('food_image');
    }
}
