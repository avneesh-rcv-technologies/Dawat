<?php

namespace Modules\FoodManagements\Services;

use Illuminate\Validation\Rule;

class ServingOptionConfiguration
{
    private array $configuration;

    public function __construct()
    {
        $this->configuration = json_decode(
            file_get_contents(__DIR__.'/../Config/serving-options.json'),
            true, 512, JSON_THROW_ON_ERROR
        );
    }

    public function all(): array
    {
        return $this->configuration;
    }

    public function rules(array $options, int $foodItemId = 0): array
    {
        $config = $this->configuration;
        $rules = [
            'serving_options' => $config['collection']['validation'],
            'serving_options.*' => $config['collection']['item_validation'],
            'serving_options.*.id' => [
                ...$config['collection']['id']['validation'],
                Rule::exists('food_item_serving_options', 'id')->where('food_item_id', $foodItemId),
            ],
        ];

        foreach ($options as $index => $option) {
            // Form row indices can have gaps after removing an option.
            if (! is_array($option) || ! ctype_digit((string) $index)) {
                $rules['serving_options'][] = function ($attribute, $value, $fail) {
                    $fail('Serving options must contain numbered option rows.');
                };
                continue;
            }
            $serveType = $option['serve_type'] ?? null;
            $type = is_string($serveType) ? ($config['serve_types'][$serveType] ?? []) : [];
            $prefix = 'serving_options.'.$index.'.';
            foreach ($config['fields'] as $field => $definition) {
                if ($field !== 'serve_type' && ! in_array($field, $type['enabled_fields'] ?? [], true)) {
                    $rules[$prefix.$field] = ['exclude'];
                    continue;
                }
                $validation = $type['validation_overrides'][$field] ?? $definition['validation'];
                if ($field === 'serve_type') {
                    $validation = ['required', 'string', Rule::in(array_keys($config['serve_types']))];
                } elseif ($field === 'quantity' && is_string($option['unit'] ?? null)) {
                    $validation = $config['unit_fields'][$option['unit']]['quantity_validation'] ?? $validation;
                }
                $required = $field === 'serve_type' || in_array($field, $type['required_fields'] ?? [], true);
                $validation = array_values(array_filter($validation, fn ($rule) => $rule !== 'required' && (! $required || $rule !== 'nullable')));
                $rules[$prefix.$field] = [
                    ...($required ? ['required'] : ['nullable']),
                    ...array_map(fn ($rule) => is_string($rule) ? str_replace('serving_options.*.', $prefix, $rule) : $rule, $validation),
                ];
            }
        }

        return $rules;
    }

    public function normalize(array $option): array
    {
        $type = $this->configuration['serve_types'][$option['serve_type']];
        foreach ($this->configuration['fields'] as $field => $definition) {
            if (! in_array($field, $type['enabled_fields'], true)) {
                $option[$field] = null;
            } elseif (! in_array($field, $type['required_fields'], true)) {
                $option[$field] = $option[$field] ?? null;
            }
        }

        return $option;
    }
}
